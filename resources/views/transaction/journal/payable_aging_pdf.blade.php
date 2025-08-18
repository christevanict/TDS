<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payable Aging Report</title>
    <style>
        @page{
            /* size: A4 landscape; */
        }
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
            text-align: center;
            font-size: 12px;
        }
        .trial-balance-table td {
            text-align: center; /* Right align by default */
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
            width: 30%; /* Due Date column */
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
        .aging{
            text-align: center!important;
            min-width:60px!important;
        }
        /* Print styles */
        @media print {
            @page {
                margin:0;
                margin-top: 50px;
                /* size: A4 landscape; */

            }
            body { margin: 1.6cm; }
            .no-print {
                display: none; /* Hide elements with the class 'no-print' */
            }
            .print-header {
                margin-top: 500px; /* This won't work directly, but we can use padding */
                padding-top: 500px; /* Use padding to create space */
            }
            .supplier-group {
                margin-top: 20px; /* Adjust the margin as needed */
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
    $grandTotalDebt = 0;
    $grandTotalNotYetDue = 0;
    $grandTotal1to30Days = 0;
    $grandTotal31to60Days = 0;
    $grandTotal61to90Days = 0;
    $grandTotalOver90Days = 0;
    @endphp
    <div class="container">
        <div class="invoice-header">
            <h1>Payable Aging Report</h1>
            <p>{{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
        </div>

        @if (empty($agingReport ))
            <div class="no-data">
                <p>No trial balance data available for the selected period.</p>
            </div>
        @else
            <table class="trial-balance-table">
                <thead class="print-header">
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th colspan="5" class="aging">Days</th>
                    </tr>
                    <tr>
                        <th style="min-width: 230px;">Invoice Number</th>
                        <th>Invoice Date</th>
                        <th>{{__('Due Date')}}</th>
                        <th>Total Due</th>
                        <th class="aging">0</th>
                        <th class="aging">1-30</th>
                        <th class="aging">31-60</th>
                        <th class="aging">61-90</th>
                        <th class="aging"> > 90</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($agingReport as $supplierName => $report)
                    <tr class="supplier-group">
                        <td colspan="9" style="text-align: left;"><h2 style="font-size: 14px;">{{ $supplierName }}</h2></td>
                    </tr>
                    @foreach ($report['debts'] as $debt)
                        @php
                            // Use the 'umur' property set in the controller to determine the aging group
                            $agingGroup = '';
                            if ($debt->umur == 0) {
                                $agingGroup = '0';
                            } elseif ($debt->umur > 0 && $debt->umur <= 30) {
                                $agingGroup = '1-30';
                            } elseif ($debt->umur > 30 && $debt->umur <= 60) {
                                $agingGroup = '31-60';
                            } elseif ($debt->umur > 60 && $debt->umur <= 90) {
                                $agingGroup = '61-90';
                            } else {
                                $agingGroup = 'over_90';
                            }
                        @endphp
                        <tr>
                            <td>{{ $debt->document_number }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->document_date)->format('d M y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($debt->due_date)->format('d-m-y') }}</td>
                            <td class="aging">{{ number_format($debt->debt_balance, 0) }}</td>
                            <td class="aging">
                                @if($agingGroup == '0')
                                    {{ number_format($debt->debt_balance) }}
                                    @php $grandTotalNotYetDue += $debt->debt_balance; @endphp
                                @else
                                    0
                                @endif
                            </td>
                            <td class="aging">
                                @if($agingGroup == '1-30')
                                    {{ number_format($debt->debt_balance) }}
                                    @php $grandTotal1to30Days += $debt->debt_balance; @endphp
                                @else
                                    0
                                @endif
                            </td>
                            <td class="aging">
                                @if($agingGroup == '31-60')
                                    {{ number_format($debt->debt_balance) }}
                                    @php $grandTotal31to60Days += $debt->debt_balance; @endphp
                                @else
                                    0
                                @endif
                            </td>
                            <td class="aging">
                                @if($agingGroup == '61-90')
                                    {{ number_format($debt->debt_balance) }}
                                    @php $grandTotal61to90Days += $debt->debt_balance; @endphp
                                @else
                                    0
                                @endif
                            </td>
                            <td class="aging">
                                @if($agingGroup == 'over_90')
                                    {{ number_format($debt->debt_balance) }}
                                    @php $grandTotalOver90Days += $debt->debt_balance; @endphp
                                @else
                                    0
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tr>
                        <td colspan="3" style="text-align: right">Total for {{ $supplierName }}</td>
                        <td class="aging">{{ number_format($report['total_amount_due']) }}</td>
                        <td class="aging">{{ number_format($report['aging']['0'], 0) }}</td>
                        <td class="aging">{{ number_format($report['aging']['1-30']) }}</td>
                        <td class="aging">{{ number_format($report['aging']['31-60']) }}</td>
                        <td class="aging">{{ number_format($report['aging']['61-90']) }}</td>
                 <td>{{ number_format($report['aging']['over_90']) }}</td>
                    </tr>

                    @php
                        // Accumulate the grand totals
                        $grandTotalDebt += $report['total_amount_due'];
                    @endphp
                @endforeach

                @if ($grandTotalDebt > 0)

                    <tr class="total-row">
                        <td colspan="3" style="text-align: right"><strong>Grand Total</strong></td>
                        <td class="aging"><strong>{{ number_format($grandTotalDebt, 0) }}</strong></td>
                        <td class="aging"><strong>{{ number_format($grandTotalNotYetDue, 0) }}</strong></td>
                        <td class="aging"><strong>{{ number_format($grandTotal1to30Days, 0) }}</strong></td>
                        <td class="aging"><strong>{{ number_format($grandTotal31to60Days, 0) }}</strong></td>
                        <td class="aging"><strong>{{ number_format($grandTotal61to90Days, 0) }}</strong></td>
                        <td class="aging"><strong>{{ number_format($grandTotalOver90Days, 0) }}</strong></td>
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
