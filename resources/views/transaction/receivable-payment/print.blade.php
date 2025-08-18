<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receivable Payment - {{ $generals->receivable_payment_number }}</title>
    <style>
        .no-border {
            border: none;
        }
        @page{
            margin: 0;
            margin-top: 20px!important;
            margin-bottom: 50px!important;
            margin-left: 35px!important;
            margin-right: 25px!important;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 14px;
            color: #333;
            margin-top: 10px;
        }
        .container {
            width: 100%;
            max-width: 900px;
            height: auto;
            background: #fff;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 5px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            page-break-inside: auto;
            min-height:  530px;
            margin: 5px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #fff;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .invoice-header p {
            margin: 5px 0;
            font-size: 12px;
        }

        .details-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            margin-top: 5px;
        }
        .company-details, .customer-details {
            flex: 1;
            margin: 0 5px;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .section-title {
            font-size: 12px;
            margin-bottom: 5px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding: 3px;
            padding-bottom: 3px;
        }
        .details-text {
            font-size: 12px;
            padding-left: 3px;
            margin-bottom: 2px;
            line-height: 0.5;
        }

        .summary-table th, .summary-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: right;
        }
        .summary-table th {
            background-color: #f9f9f9;
            font-weight: bold;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            background-color: #fff;
        }

        .signature {
            text-align: center;
            flex: 1;
            margin: 0 10px;
            max-width: 200px;
        }

        .signature-title {
            margin-top: 50px;
            margin-bottom: 20px;
            font-weight: bold;
        }



    </style>
</head>
<body>
    <div class="container">
        <div class="details-container">
            <div class="company-details">
                <h2 style="text-align: center">Receivable Payment</h2>
                <h3 style="text-align: center">{{$generals->general_journal_number}}</h3>
                <table class="table" style="width:100%">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: left" >Document Number</th>
                            <th colspan="2" style="text-align: center">Customer</th>
                            <th style="text-align: right">Document Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" style="text-align: left">{{$generals->receivable_payment_number}}</td>
                            <td colspan="2" style="text-align: center">{{$generals->customer->customer_name}}</td>
                            <td style="text-align: right">{{ \Carbon\Carbon::parse($generals->document_date)->format('d M Y') }}</td>

                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td colspan="2" style="text-align: right; font-size:16px;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Invoice Date</th>
                    <th>Total Debt</th>
                    <th>Payment Amount</th>
                    <th>Discount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalNominal = 0;
                    $totalDiscount = 0;
                @endphp
                @foreach($generals->details as $index => $detail)
                <tr>
                    @php
                        $totalNominal+=$detail->nominal;
                        $totalDiscount+=$detail->discount;
                    @endphp

                    <td style="text-align: center; max-width:200px;">{{ $detail->document_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($detail->document_date)->format('d M Y') }}</td>
                    <td style="text-align: right;">{{ number_format($detail->document_payment, 0,'.',',') }}</td>
                    <td style="text-align: right;">{{ number_format($detail->nominal, 0,'.',',') }}</td>
                    <td style="text-align: right; max-width: 70px;">{{ number_format($detail->discount,0,'.',',')}}</td>
                </tr>
                @endforeach
                <tr>
                    <td style="border: none;" colspan="5"></td>
                </tr>
                <tr>
                    <td style="border: none;"></td>
                    <th colspan="2" style="text-align: left;"> Total</th>
                    <td colspan="2"  style="text-align: right;">{{ number_format($totalNominal, 0,'.',',') }}</td>
                </tr>
                <tr>
                    <td style="border: none;"></td>
                    <th colspan="2" style="text-align: left;">Discount</th>
                    <td colspan="2"  style="text-align: right;">{{ number_format($totalDiscount, 0,'.',',') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td colspan="2" style="border: none; text-align: center;"><strong>Dibuat oleh: </strong></td>
            </tr>
            <tr>
                <td colspan="4" style="border: none; text-align: center;"></td>
            </tr>
            <tr>
                <td colspan="4" style="border: none; text-align: center;"></td>
            </tr>
            <tr>
                <td colspan="4" style="border: none; text-align: center;"></td>
            </tr>
            <tr>
                <td colspan="4" style="border: none; text-align: center;"></td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: center;margin:0; padding-top:0;padding-bottom:0;"></td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: center; margin:0; padding-top:0;">______________________</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: center; margin:0; padding-top:0;">Admin Finance</td>
            </tr>
        </table>

    </div>
</body>
</html>
