<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minute Sheet - {{ $minutesSheet->reference_no }}</title>
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
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
        }

        .header p {
            margin: 5px 0 0 0;
            font-size: 11pt;
            text-decoration: underline;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            border: 1px solid #000;
            padding: 8px 10px;
            vertical-align: top;
        }

        .info-table .label {
            width: 70px;
            font-weight: normal;
        }

        .info-table .value {
            font-weight: normal;
        }

        .info-table .date-label,
        .info-table .ref-label {
            width: 60px;
            text-align: left;
        }

        .info-table .date-value,
        .info-table .ref-value {
            width: 120px;
        }

        .content {
            margin: 25px 0;
        }

        .content p {
            margin: 10px 0;
            text-align: justify;
        }

        .content ol {
            margin: 15px 0;
            padding-left: 25px;
        }

        .content ol li {
            margin-bottom: 15px;
        }

        .total-section {
            margin: 30px 0;
            padding-left: 40px;
        }

        .total-section strong {
            font-weight: bold;
        }

        .signature-section {
            margin-top: 40px;
        }

        .signature-row {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 60px;
        }

        .signature-box {
            text-align: right;
        }

        .signature-box .title {
            font-weight: bold;
        }

        .signature-box .date {
            margin-top: 5px;
        }

        .approval-signatures {
            margin-top: 30px;
        }

        .approval-row {
            margin-bottom: 50px;
        }

        .approval-row .title {
            font-weight: bold;
            text-decoration: underline;
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
        <h2>Minute Sheet</h2>
        <p>{{ $settings['company_address'] ?? 'Khuda\'dad Plaza, G.T Road, Islamabad' }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">From:</td>
            {{-- <td class="value" colspan="2"> 
                {{ $minutesSheet->designation }},
                {{ $settings['company_name'] ?? 'TechJuice' }}</td> --}}
            <td class="value" colspan="2">
                {{ !empty($approvedStages) ? implode(', ', $approvedStages) : $minutesSheet->designation }}
                <br>
                <small>{{ $settings['company_name'] ?? 'TechJuice' }}</small>
            </td>
            <td class="date-label">Date</td>
            <td class="date-value">{{ \Carbon\Carbon::parse($minutesSheet->date)->format('d M Y') }}</td>
        </tr>
        
        <tr>
            <td class="label">To:</td>
            <td class="value" colspan="2">{{ $toDesignation }}, {{ $settings['company_name'] ?? 'TechJuice' }}</td>
            <td class="ref-label">Ref No.</td>
            <td class="ref-value">{{ $minutesSheet->reference_no }}</td>
        </tr>
        <tr>
            <td class="label">Subject</td>
            <td colspan="4" class="value">{{ $minutesSheet->subject }}</td>
        </tr>
    </table>

    <div class="content">
        <ol>
            <li>An amount of <strong>{{ \Auth::user()->priceFormat($minutesSheet->amount) }}</strong>
                ({{ $amountInWords }}) is required against the details as follows:
                <br><br>
                @if ($minutesSheet->description)
                    <p style="margin-left: 20px;">{{ $minutesSheet->description }}</p>
                @endif
                <table style="width: 100%; margin-left: 20px; margin-top: 15px;">
                    <tr>
                        <td style="width: 150px;"><strong>Bank Account:</strong></td>
                        <td>{{ $minutesSheet->bankAccount->holder_name ?? '-' }}
                            ({{ $minutesSheet->bankAccount->bank_name ?? '-' }})</td>
                    </tr>
                    <tr>
                        <td><strong>Account Number:</strong></td>
                        <td>{{ $minutesSheet->bankAccount->account_number ?? '-' }}</td>
                    </tr>
                </table>
            </li>
        </ol>

        <div class="total-section">
            <strong>Total</strong>
        </div>

        <p style="margin-left: 20px;">Above in view, it is requested that a sum of
            <strong>{{ \Auth::user()->priceFormat($minutesSheet->amount) }}</strong> ({{ $amountInWords }}) may please
            be sanctioned.</p>

        <ol start="2">
            <li>Submitted for perusal/approval, Please.</li>
        </ol>
    </div>

    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-box">
                <div class="title">{{ $minutesSheet->designation }}</div>
                <div class="date">----{{ \Carbon\Carbon::parse($minutesSheet->date)->format('M Y') }}</div>
            </div>
        </div>

        <div class="approval-signatures">
            @php
                $chain = \App\Models\MinutesSheet::APPROVAL_CHAIN;
                $creatorIndex = array_search($minutesSheet->designation, $chain);
            @endphp

            @foreach ($chain as $index => $stage)
                @if ($index > $creatorIndex)
                    <div class="approval-row">
                        <div class="title">{{ $stage }}</div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <script>
        // Auto print on load (optional - uncomment if you want auto-print)
        // window.onload = function() { window.print(); }
    </script>
</body>

</html>
