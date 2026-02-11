<?php

namespace App\Imports;

use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Models\ChartOfAccountSubType;
use App\Models\ChartOfAccountType;
use App\Models\JournalEntry;
use App\Models\JournalItem;
use App\Models\TransactionLines;
use App\Models\Customer;
use App\Models\Vender;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FullJournalImport implements ToCollection, WithHeadingRow
{
    public function headingRow(): int
    {
        return 1; // Standard heading row
    }

    public function collection(Collection $rows)
    {
        $groupedEntries = $this->processJournalRows($rows);
        
        $totalImported = 0;
        $skippedEntries = [];

        foreach ($groupedEntries as $entryData) {
            $result = $this->createJournalEntry($entryData);

            if ($result['status'] == 'created') {
                $totalImported++;
            } elseif ($result['status'] == 'skipped') {
                $skippedEntries[] = $result['data'];
            }
        }

        if (!empty($skippedEntries)) {
            session()->put('skipped_entries', $skippedEntries);
        }
    }

    private function processJournalRows(Collection $rows): array
    {
        $groupedEntries = [];
        $entryBuffer = [];
        $currentDate = null;
        $currentRef = null;

        foreach ($rows as $row) {
            $date = $row['date'] ?? $row[0] ?? null;
            $type = $row['transaction_type'] ?? $row[1] ?? null;
            $num = $row['num'] ?? $row[2] ?? null;
            
            // If we have a new date or ref, it might be a new transaction
            // QuickBooks reports often have blank dates for lines after the first
            if (!empty($date)) {
                if (!empty($entryBuffer)) {
                    $groupedEntries[] = $entryBuffer;
                    $entryBuffer = [];
                }
                $currentDate = $date;
                $currentRef = $num;
            }

            // Skip empty rows or header-like rows
            if (empty($row['account']) && empty($row[5])) continue;

            $entryBuffer[] = $row;
        }

        if (!empty($entryBuffer)) {
            $groupedEntries[] = $entryBuffer;
        }

        return $groupedEntries;
    }

    private function createJournalEntry($entryData)
    {
        try {
            $firstRow = $entryData[0];
            $date = $firstRow['date'] ?? $firstRow[0] ?? now()->toDateString();
            $num = $firstRow['num'] ?? $firstRow[2] ?? '';
            $description = $firstRow['memo'] ?? $firstRow[4] ?? '';

            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($entryData as $row) {
                $totalDebit += floatval($this->cleanAmount($row['debit'] ?? $row[6] ?? 0));
                $totalCredit += floatval($this->cleanAmount($row['credit'] ?? $row[7] ?? 0));
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                return [
                    'status' => 'skipped',
                    'data' => ['date' => $date, 'ref' => $num, 'reason' => 'Unbalanced: D('.$totalDebit.') C('.$totalCredit.')']
                ];
            }

            $journal = new JournalEntry();
            $journal->journal_id = $this->journalNumber();
            $journal->date = date('Y-m-d', strtotime($date));
            $journal->reference = $num;
            $journal->description = $description;
            $journal->created_by = Auth::user()->creatorId();
            $journal->owned_by = Auth::user()->ownedId();
            $journal->save();

            foreach ($entryData as $row) {
                $accountName = $row['account'] ?? $row[5] ?? '';
                $debit = floatval($this->cleanAmount($row['debit'] ?? $row[6] ?? 0));
                $credit = floatval($this->cleanAmount($row['credit'] ?? $row[7] ?? 0));
                $memo = $row['memo'] ?? $row[4] ?? '';
                $name = $row['name'] ?? $row[3] ?? '';

                $account = $this->ensureCOA($accountName);
                if (!$account) continue;

                $journalItem = new JournalItem();
                $journalItem->journal = $journal->id;
                $journalItem->account = $account->id;
                $journalItem->description = $memo;
                $journalItem->debit = $debit;
                $journalItem->credit = $credit;
                
                if (!empty($name)) {
                    [$entityType, $entityId] = $this->mapQuickBooksEntity($name);
                    $journalItem->type = $entityType;
                    $journalItem->name = $name;
                    if ($entityType == 'customer') $journalItem->customer_id = $entityId;
                    elseif ($entityType == 'vendor') $journalItem->vendor_id = $entityId;
                    elseif ($entityType == 'employee') $journalItem->employee_id = $entityId;
                }

                $journalItem->save();

                // Add Transaction Lines
                if ($debit > 0 || $credit > 0) {
                    $this->addTransactionLines([
                        'account_id' => $account->id,
                        'transaction_type' => $debit > 0 ? 'Debit' : 'Credit',
                        'transaction_amount' => $debit > 0 ? $debit : $credit,
                        'reference' => 'Journal',
                        'reference_id' => $journal->id,
                        'reference_sub_id' => $journalItem->id,
                        'date' => $journal->date,
                    ]);
                }
            }

            return ['status' => 'created', 'data' => $journal];

        } catch (\Exception $e) {
            \Log::error('FullJournalImport Error: ' . $e->getMessage());
            return ['status' => 'skipped', 'data' => ['reason' => $e->getMessage()]];
        }
    }

    private function mapQuickBooksEntity($name)
    {
        $customer = Customer::where('name', 'LIKE', $name)->first();
        if ($customer) return ['customer', $customer->id];

        $vendor = Vender::where('name', 'LIKE', $name)->first();
        if ($vendor) return ['vendor', $vendor->id];

        $employee = Employee::where('name', 'LIKE', $name)->first();
        if ($employee) return ['employee', $employee->id];

        return ['other', null];
    }

    private function ensureCOA($fullName)
    {
        $account = ChartOfAccount::where('name', $fullName)->where('created_by', Auth::user()->creatorId())->first();
        if ($account) return $account;

        $typeMapping = [
            'accounts payable (a/p)' => 'Liabilities',
            'accounts payable' => 'Liabilities',
            'credit card' => 'Liabilities',
            'long term liabilities' => 'Liabilities',
            'other current liabilities' => 'Liabilities',
            'loan payable' => 'Liabilities',
            'notes payable' => 'Liabilities',
            'accounts receivable (a/r)' => 'Assets',
            'accounts receivable' => 'Assets',
            'bank' => 'Assets',
            'checking' => 'Assets',
            'savings' => 'Assets',
            'inventory asset' => 'Assets',
            'other current assets' => 'Assets',
            'fixed assets' => 'Assets',
            'equity' => 'Equity',
            'income' => 'Income',
            'other income' => 'Income',
            'sales' => 'Income',
            'cost of goods sold' => 'Costs of Goods Sold',
            'cogs' => 'Costs of Goods Sold',
            'expenses' => 'Expenses',
            'expense' => 'Expenses',
            'other expense' => 'Expenses',
        ];

        $systemTypeName = 'Expenses'; // Default
        $detailType = 'Other';
        $lowName = strtolower($fullName);

        foreach ($typeMapping as $key => $type) {
            if (str_contains($lowName, $key)) {
                $systemTypeName = $type;
                $detailType = $key;
                break;
            }
        }

        $type = ChartOfAccountType::firstOrCreate(
            ['name' => $systemTypeName, 'created_by' => Auth::user()->creatorId()]
        );

        $subType = ChartOfAccountSubType::firstOrCreate([
            'type' => $type->id,
            'name' => ucwords($detailType),
            'created_by' => Auth::user()->creatorId(),
        ]);

        return ChartOfAccount::create([
            'name' => $fullName,
            'type' => $type->id,
            'sub_type' => $subType->id,
            'created_by' => Auth::user()->creatorId(),
        ]);
    }

    private function cleanAmount($value)
    {
        if (!$value) return 0;
        return str_replace([',', '$', '(', ')'], '', $value);
    }

    private function journalNumber()
    {
        $latest = JournalEntry::where('created_by', Auth::user()->creatorId())->latest()->first();
        return $latest ? $latest->journal_id + 1 : 1;
    }

    private function addTransactionLines($data)
    {
        $transactionLines = new TransactionLines();
        $transactionLines->account_id = $data['account_id'];
        $transactionLines->reference = $data['reference'];
        $transactionLines->reference_id = $data['reference_id'];
        $transactionLines->reference_sub_id = $data['reference_sub_id'];
        $transactionLines->date = $data['date'];
        
        if ($data['transaction_type'] == "Credit") {
            $transactionLines->credit = $data['transaction_amount'];
            $transactionLines->debit = 0;
        } else {
            $transactionLines->credit = 0;
            $transactionLines->debit = $data['transaction_amount'];
        }
        $transactionLines->created_by = Auth::user()->creatorId();
        $transactionLines->save();
    }
}
