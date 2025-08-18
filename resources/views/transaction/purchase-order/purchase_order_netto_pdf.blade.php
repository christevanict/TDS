
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{__('Purchase Order')}} - {{ $purchaseOrders->purchase_order_number }}</title>
    <style>
        html { margin: 0px}
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .container {
            width: 19.5cm;
            margin: 0 auto;
            max-width: 900px;
            height: auto;
            background: #fff;
        }
        .invoice-header {
            text-align: center;
            font-size: 16px;
            margin: 0;
        }
        .invoice-header h1 {
            color: #333;
        }
        .invoice-header p {
            /* margin: 5px 0; */
            font-size: 14px;
        }
        .details-container {
            display: flex;
            justify-content: space-between;
            /* margin-bottom: 20px; */
            /* margin-top: 20px; */
        }
        .company-details, .customer-details {
            flex: 1;
        }
        .section-title {
            font-size: 14px;
            /* margin-bottom: 5px; */
            color: #333;
            border-bottom: 1px solid #ccc;
            padding: 3px;
            padding-bottom: 3px;
        }
        .details-text {
            font-size: 14px;
            padding-left: 3px;
            line-height: 0.5;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            padding: 3px;
            text-align: center;
        }
        .items-table th {
            font-weight: bold;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #fff;
        }
        .items-table tbody tr td {
            font-size: 14px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            /* margin-top: 20px; */
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ccc;
            text-align: right;
        }
        .summary-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .total {
            font-weight: bold;
            font-size: 14px;
            text-align: right;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            background-color: #fff;
        }

        .signature {
            text-align: center;
            flex: 1;
            /* margin: 0 10px; */
            max-width: 200px;
        }

        .signature-title {
            /* margin-top: 50px; */
            /* margin-bottom: 20px; */
            font-weight: bold;
        }
        .btn-print{
            background-color: #4CAF50; /* Green background */
            border: none;              /* No border */
            color: white;             /* White text */
            padding: 15px 32px;      /* Some padding */
            text-align: center;       /* Centered text */
            text-decoration: none;     /* No underline */
            display: inline-block;     /* Inline block */
            font-size: 16px;          /* Increase font size */
            margin: 4px 2px;         /* Margins */
            cursor: pointer;          /* Pointer cursor on hover */
            border-radius: 12px;     /* Rounded corners */
            transition: background-color 0.3s;
        }

        @media print {
            @page {
                size: 21.5cm auto;
                margin: 0;
                margin-top: 20px;
            }
            body {
                margin: 0;
                padding: 0;
                margin-top: 20px;
            }
            .container {
                width: 19.5cm;
                margin: 0 auto;
                margin-top: 20px;
            }
            .invoice-header{
                margin-top:20px;
            }
            .btn-print{
                display:none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="details-container">
            <div class="company-details">
                <table class="table" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 10%;"></th>
                            <th scope="col" style="width: 40%;"></th>
                            <th scope="col" style="width: 10%;"></th>
                            <th scope="col" style="width: 40%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="2" class="invoice-header" style="text-align: right; font-size:16px;"><strong>TDS, CV</strong</td>
                            <td colspan="2"  style="text-align: center" rowspan="2"><h2><strong>PERMINTAAN PETDSLIAN NETTO</strong></h2></td>
                        </tr>
                        <tr>
                            <td colspan="3">Kepada Yth.</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="vertical-align: left; ">{{ $purchaseOrders->suppliers->supplier_name ?? 'N/A' }}</td>
                            <td style="vertical-align: left; min-width:100px;">No. Invoice</td>
                            <td style="vertical-align: left;">: {{ $purchaseOrders->purchase_order_number }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="vertical-align: left;">{{ $purchaseOrders->suppliers->address ?? 'N/A' }}</td>
                            <td style="vertical-align: left;">Tanggal</td>
                            <td style="vertical-align: left;">: {{ \Carbon\Carbon::parse($purchaseOrders->document_date)->format('d M Y') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="text-align: center; border:1px solid black; width:5px;">NO.</th>
                    <th style="text-align: center; border:1px solid black; min-width:350px;">NAMA BARANG</th>
                    <th style="text-align: center; border:1px solid black; width:20px;">COLY</th>
                    <th style="text-align: center; border:1px solid black;">QTY</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseOrders->details as $index => $detail)
                <tr>
                    <td style="text-align: right; border:1px solid black;width:5px;">{{ $loop->iteration }} </td>
                    <td style="text-align: left;  border:1px solid black; min-width:350px;">{{ $detail->items->item_name }} </td>
                    <td style="text-align: center;  border:1px solid black; max-width:20px;">{{ number_format($detail->qty,0) }}</td>
                    <td style="text-align: right;  border:1px solid black;">{{ number_format($detail->base_qty, 0,',','.') }} {{ $detail->baseUnit->unit_name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button class="btn btn-primary btn-print" onclick="window.print();" >
            Print
        </button>
        <a class="btn btn-primary btn-print" href="{{route('transaction.purchase_order')}}">Kembali</a>
    </div>
</body>
</html>
