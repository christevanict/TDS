<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trial Balance</title>
    <style>
        /* General styles */
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
            text-align: left;
        }

        .trial-balance-table td {
            text-align: right; /* Right align by default */
        }

        .trial-balance-table td.debit,
        .trial-balance-table td.credit {
            text-align: left; /* Align numbers to the left */
            padding-left: 10px; /* Small padding for clarity */
        }

        .trial-balance-table td:first-child {
            text-align: left;
            padding-left: 30px; /* Align account names to the left with more padding */
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

        /* Responsive styles */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .trial-balance-table th,
            .trial-balance-table td {
                padding: 8px;
            }

            .invoice-header h1 {
                font-size: 20px;
            }

            .invoice-header p {
                font-size: 14px;
            }
        }

        @media (max-width: 480px) {
            .invoice-header h1 {
                font-size: 18px;
            }

            .trial-balance-table th,
            .trial-balance-table td {
                padding: 6px;
                font-size: 12px;
            }

            .trial-balance-table td:first-child {
                padding-left: 20px;
            }
        }

        /* Print styles */
        @media print {
            @page {
                size: A4;
                margin: 20mm 15mm;
            }

            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 12px;
            }

            .container {
                width: 100%;
                max-width: none;
                padding: 10px;
                border: none;
            }

            .invoice-header h1 {
                font-size: 20px;
            }

            .invoice-header p {
                font-size: 14px;
            }

            .footer {
                font-size: 10px;
            }

            .trial-balance-table th,
            .trial-balance-table td {
                padding: 8px;
                font-size: 12px;
            }

            .trial-balance-table td:first-child {
                padding-left: 30px;
            }

            .total-row {
                background-color: #e6e6e6;
                font-size: 14px;
            }
        }
    </style>
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
            <h1>Trial Balance</h1>
            <p>Date: {{ \Carbon\Carbon::now()->format('d-m-Y') }}</p>
        </div>

        @if ($trialBalanceData->isEmpty())
            <div class="no-data">
                <p>No trial balance data available for the selected period.</p>
            </div>
        @else
            <table class="trial-balance-table">
                <thead>
                    <tr>
                        <th>Account</th>
                        <th>Debit</th>
                        <th>Credit</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $currentAccountType = '';
                        $totalDebit = 0;
                        $totalCredit = 0;
                    @endphp

                    @foreach ($trialBalanceData as $data)
                        @if ($data->account_sub_type !== $currentAccountType)
                            @php
                                $currentAccountType = $data->account_sub_type;
                            @endphp

                            <!-- Account Type Header Row -->
                            <tr>
                                <td colspan="3" style="font-weight: bold; background-color: #f2f2f2; text-align: left; padding-left: 30px;">
                                    {{ $currentAccountType }}
                                </td>
                            </tr>
                        @endif


                        <!-- Account Row -->
                        <tr>
                            <td>{{ $data->account }}</td>
                            <td class="debit">{{ formatNumber($data->debit, 2) }}</td>
                            <td class="credit">{{ formatNumber($data->credit, 2) }}</td>
                        </tr>

                        @php
                            $totalDebit += $data->debit;
                            $totalCredit += $data->credit;
                        @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="debit">{{ formatNumber($totalDebit, 2) }}</td>
                        <td class="credit">{{ formatNumber($totalCredit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        <div class="footer">
            <p>Generated by: {{ auth()->user()->fullname }}</p>
        </div>
    </div>

</body>
</html>
