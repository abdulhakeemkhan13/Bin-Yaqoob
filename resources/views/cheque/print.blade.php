<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Detail - {{ $cheque->cheque_number }}</title>
    <style>
        @page {
            size: A4;
            margin: 20mm 15mm;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.4;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            margin: 0;
            font-size: 18pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 11pt;
        }

        .detail-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
        }

        .detail-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .detail-table td {
            padding: 10px 12px;
            vertical-align: top;
            border: 1px solid #000;
        }

        .detail-table .label {
            width: 40%;
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .detail-table .value {
            width: 60%;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            font-weight: bold;
            border-radius: 4px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #28a745;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #dc3545;
        }

        .amount-box {
            background-color: #e9ecef;
            padding: 15px;
            text-align: center;
            margin: 20px 0;
            border: 2px solid #000;
        }

        .amount-box .amount {
            font-size: 18pt;
            font-weight: bold;
        }

        .amount-box .amount-words {
            font-size: 11pt;
            font-style: italic;
            margin-top: 5px;
        }

        .reference-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #f8f9fa;
            border: 1px dashed #6c757d;
        }

        .reference-title {
            font-size: 12pt;
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
        }

        .reference-table {
            width: 100%;
        }

        .reference-table td {
            padding: 5px 10px;
            vertical-align: top;
        }

        .reference-table .ref-label {
            width: 35%;
            font-weight: bold;
            color: #6c757d;
        }

        .reference-table .ref-value {
            color: #495057;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }

        .signature-box {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }

        .signature-box .title {
            font-weight: bold;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="ti ti-printer"></i> Print
    </button>

    <div class="header">
        <h2>Check Detail</h2>
        <p>{{ $settings['company_name'] ?? 'TechJuice' }}</p>
        <p>{{ $settings['company_address'] ?? 'Khuda\'dad Plaza, G.T Road, Islamabad' }}</p>
    </div>

    <!-- Check Details Section -->
    <div class="detail-section">
        <div class="section-title">Check Information</div>
        <table class="detail-table">
            <tr>
                <td class="label">Check Number</td>
                <td class="value">{{ $cheque->cheque_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Date</td>
                <td class="value">{{ \Carbon\Carbon::parse($cheque->date)->format('d M Y') }}</td>
            </tr>
            <tr>
                <td class="label">Status</td>
                <td class="value">
                    @if (strtolower($cheque->status) === 'pending')
                        <span class="status-badge status-pending">Pending</span>
                    @elseif (strtolower($cheque->status) === 'rejected')
                        <span class="status-badge status-rejected">Rejected</span>
                    @else
                        <span class="status-badge status-approved">Approved</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Payee Name</td>
                <td class="value">{{ $cheque->payee_name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Account Title</td>
                <td class="value">{{ $cheque->account_title ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Account Number</td>
                <td class="value">{{ $cheque->account_number ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Bank Name</td>
                <td class="value">{{ $cheque->bankAccount->bank_name ?? '-' }}</td>
            </tr>
            @if ($cheque->notes)
                <tr>
                    <td class="label">Notes / Description</td>
                    <td class="value">{{ $cheque->notes }}</td>
                </tr>
            @endif
        </table>

        <!-- Amount Box -->
        <div class="amount-box">
            <div class="amount">{{ \Auth::user()->priceFormat($cheque->amount) }}</div>
            <div class="amount-words">{{ $amountInWords }}</div>
        </div>
    </div>

    <!-- Mint Sheet Reference Section -->
    @if ($cheque->minutesSheet)
        <div class="reference-section">
            <div class="reference-title">
                <i class="ti ti-file-text"></i> Mint Sheet Reference
            </div>
            <table class="reference-table">
                <tr>
                    <td class="ref-label">Reference No:</td>
                    <td class="ref-value">{{ $cheque->minutesSheet->reference_no ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="ref-label">Subject:</td>
                    <td class="ref-value">{{ $cheque->minutesSheet->subject ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="ref-label">Total Amount:</td>
                    <td class="ref-value">{{ \Auth::user()->priceFormat($cheque->minutesSheet->amount ?? 0) }}</td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line">
                        <div class="title">Prepared By</div>
                    </div>
                </td>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line">
                        <div class="title">Approved By</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <script>
        // Auto print on load (optional - uncomment if you want auto-print)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>
