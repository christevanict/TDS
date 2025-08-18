<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receivable Report</title>
    <style>
        /* Your existing styles */
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
            position: relative;
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
        .trial-balance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .trial-balance-table th,
        .trial-balance-table td {
            padding: 10px;
            box-sizing: border-box;
            border-bottom: 1px solid #ccc;
        }
        .trial-balance-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: right;
            font-size: 12px;
        }
        .trial-balance-table td {
            text-align: right; /* Right align by default */
            font-size: 12px;
        }
        .trial-balance-table th:nth-child(1),
        .trial-balance-table td:nth-child(1) {
            width: 15%; /* Document Number column */
        }

        .trial-balance-table th:nth-child(2),
        .trial-balance-table td:nth-child(2) {
            width: 30%; /* {{__('Document Date')}} column */
        }

        .trial-balance-table th:nth-child(3),
        .trial-balance-table td:nth-child(3) {
            width: 30%; /* {{__('Due Date')}} column */
        }

        .trial-balance-table th:nth-child(4),
        .trial-balance-table td:nth-child(4),
        .trial-balance-table th:nth-child(5),
        .trial-balance-table td:nth-child(5),
        .trial-balance-table th:nth-child(6),
        .trial-balance-table td:nth-child(6) {
            width: 10%; /* Total Debt, Debt Balance, Age (Days) columns */
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
        /* Print styles */
        @media print {
            @page {margin:0; margin-top: 50px;}
            body { margin: 1.6cm; }
            .no-print {
                display: none; /* Hide elements with the class 'no-print' */
            }
            .page-break {
                margin-top: 20mm; /* Adjust the margin as needed */
            }
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
    @php
    function formatNumber($number) {
        // Check if the number has a decimal part
        if (floor($number) == $number) {
            // If it's a whole number, format without decimals
            return number_format($number, 0);
        } else {
            // If it has a decimal part, format with 2 decimal places
            return number_format($number, 2);
        }
    }
    @endphp
    <div class="container">
        <div class="invoice-header">
            <h1>Receivable Report</h1>
            <p>Date: {{ \Carbon\Carbon::now()->format('d-F-Y') }}</p>
        </div>

        @if (empty($customers ))
            <div class="no-data">
                <p>No trial balance data available for the selected period.</p>
            </div>
        @else
            @php
                $grandTotalDebt = 0;
                $grandTotalDebtBalance = 0;
            @endphp
            <table class="trial-balance-table">
                <thead>
                    <tr>
                        <th>Document Number</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Due Date')}}</th>
                        <th>Total Debt</th>
                        <th>Debt Balance</th>
                        <th>Age (Days)</th>
                    </tr>
                </thead>
                <tbody>

            @foreach ($customers  as $total)
                @if ($total['debt_balance'] > 0)
                <tr>
                    <td colspan="6" style="text-align: left;"><h2 style="font-size: 14px;">{{ $total['customer_name'] }}</h2></td>
                </tr>
                    @foreach ($total->receivables as $rei)
                        <tr>
                            <td>{{ $rei->document_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($rei->document_date)->format('d-M-Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($rei->due_date)->format('d-M-Y') }}</td>
                            <td>{{ $rei->total_debt == 0 ? formatNumber($rei->total_debt) : formatNumber($rei->total_debt) }}</td>
                            <td>{{ $rei->debt_balance == 0 ? formatNumber($rei->debt_balance) : formatNumber($rei->debt_balance) }}</td>
                            <td>{{ $rei->umur }}</td>
                        </tr>
                    @endforeach
                        </tbody>
                            <tr class="total-row">
                                <td colspan="3"><strong>Total for {{ $total['customer_name'] }}</strong></td>
                                <td><strong>{{ formatNumber($total['total_debt'],  2) }}</strong></td>
                                <td><strong>{{ formatNumber($total['debt_balance'], 2) }}</strong></td>
                                <td></td>
                            </tr>



                    @php
                        $grandTotalDebt += $total['total_debt'];
                        $grandTotalDebtBalance += $total['debt_balance'];
                    @endphp
                @endif
            @endforeach

            @if ($grandTotalDebtBalance > 0)

                        <tr class="total-row">
                            <td colspan="3"><strong>Grand Total</strong></td>
                            <td><strong>{{ formatNumber($grandTotalDebt, 2) }}</strong></td>
                            <td><strong>{{ formatNumber($grandTotalDebtBalance, 2) }}</strong></td>
                            <td></td>
                        </tr>

            @endif
            </table>
        @endif

        <div class="footer">
            <p>Generated by : {{ auth()->user()->fullname }}</p>
            <button style="font-size: 16px;" class="no-print" onclick="printReport()">Print Report</button>
        </div>
    </div>
</body>
</html>
