<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balance Sheet Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 14px;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 900px;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
            margin: auto;
            box-sizing: border-box;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .invoice-header p {
            font-size: 16px;
            color: #555;
        }
        .balance-sheet-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .balance-sheet-table th,
        .balance-sheet-table td {
            padding: 10px;
            box-sizing: border-box;
            border-bottom: 1px solid #ccc;
        }
        .balance-sheet-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
            font-size: 12px;
        }
        .balance-sheet-table td {
            font-size: 12px;
        }
        /* Align columns correctly */
        .balance-sheet-table th:nth-child(1),
        .balance-sheet-table td:nth-child(1) {
            width: 30%; /* Account Type and Sub Type */
            text-align: left;
        }

        .balance-sheet-table th:nth-child(2),
        .balance-sheet-table td:nth-child(2) {
            width: 40%; /* Account */
            text-align: left;
        }

        .balance-sheet-table th:nth-child(3),
        .balance-sheet-table td:nth-child(3) {
            width: 30%; /* Balance */
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            font-size: 16px;
            background-color: #f2f2f2;
        }

        .no-data {
            text-align: center;
            font-size: 16px;
            color: #999;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            font-size: 12px;
            margin-top: 30px;
        }
        .no-print {
            margin-top: 10px;
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }
        /* Print styles */
        @media print {
            @page { margin: 0; }
            body { margin: 1.6cm; }
            .no-print {
                display: none; /* Hide elements with the class 'no-print' */
            }
        }

        /* Responsive Styles */
        @media screen and (max-width: 768px) {
            .balance-sheet-table th, .balance-sheet-table td {
                font-size: 10px;
            }
            .balance-sheet-table th:nth-child(1),
            .balance-sheet-table td:nth-child(1) {
                width: 25%;
            }
            .balance-sheet-table th:nth-child(2),
            .balance-sheet-table td:nth-child(2) {
                width: 35%;
            }
            .balance-sheet-table th:nth-child(3),
            .balance-sheet-table td:nth-child(3) {
                width: 40%;
            }
        }

        /* Styling for indentation of subtypes and account names */
        .account-sub-type {
            margin-left: 10px;
        }

        .account-name {
            margin-left: 20px;
        }
    </style>
    <script>
        function printReport() {
            console.log('Print function called');
            setTimeout(() => {
                window.print();
                console.log('Print dialog should be open now.');
            }, 100); // Optional delay
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="invoice-header">
            <h1>Balance Sheet Report</h1>
            <p>Date: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</p>
            <p>Period: {{ \Carbon\Carbon::parse($dateFrom)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($dateTo)->format('d-m-Y') }}</p>
        </div>

        @if (empty($groupedData) || $groupedData->isEmpty())
            <div class="no-data">
                <p>No balance sheet data available for the selected period.</p>
            </div>
        @else
        <table class="balance-sheet-table">
            <thead>
                <tr>
                    <th>Account</th>
                    <th>Account Code</th>
                    <th>Balance</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $grandTotal = 0; // Initialize grand total variable
                @endphp
                @foreach ($groupedData as $accountTypeName => $subTypes)
                    @php
                        $accountTypeTotal = 0; // Initialize account type total for each type
                    @endphp
                    <tr>
                        <td class="account-type" colspan="3" style="font-weight: bold;">{{ $accountTypeName }}</td>
                    </tr>
                    @foreach ($subTypes as $subTypeName => $accounts)
                        <tr>
                            <td class="account-sub-type" colspan="3" style="font-weight: bold;">{{ $subTypeName }}</td>
                        </tr>
                        @foreach ($accounts as $account)
                            <tr>
                                <td class="account-name">{{ $account->account_name }}</td>
                                <td class="account-number">{{ $account->account_number }}</td>
                                <td class="balance">{{ number_format($account->balance, 2) }}</td>
                            </tr>
                            @php
                                $accountTypeTotal += $account->balance; // Add to account type total
                                $grandTotal += $account->balance; // Add to grand total
                            @endphp
                        @endforeach
                    @endforeach
                    <!-- Account Type Total -->
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right;"><strong>Total for {{ $accountTypeName }}:</strong></td>
                        <td class="balance" style="text-align: right;"><strong>{{ number_format($accountTypeTotal, 2) }}</strong></td>
                    </tr>
                @endforeach
                <!-- Grand Total -->
                <tr class="grand-total-row">
                    <td colspan="2" style="text-align: right;"><strong>Grand Total:</strong></td>
                    <td class="balance" style="text-align: right;"><strong>{{ number_format($grandTotal, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>
        @endif

        <div class="footer">
            <p>Generated by: {{ auth()->user()->fullname }}</p>
            <button class="no-print" onclick="printReport()">Print Report</button>
        </div>
    </div>
</body>
</html>
