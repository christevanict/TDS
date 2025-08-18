<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{__('Sales Invoice')}} - {{ $salesInvoice->sales_invoice_number }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1;
        }
        .container {
            width: 19.5cm;
            margin: 0 auto;
        }
        pre {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            white-space: pre;
            margin: 0;
            padding: 0;
        }
        .btn-print {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            display: inline-block;
            font-size: 12px;
            margin: 4px 2px;
            cursor: pointer;
        }
        @media print {
            @page {
                size: 21.5cm auto;
                margin: 0;
            }
            body, .container {
                margin: 0;
                padding: 0;
            }
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a class="btn-print" href="{{route('transaction.sales_invoice')}}">Kembali</a>
    </div>
</body>
<script>window.open('http://127.0.0.1:8181/webservice/{{$salesInvoice->id}}/sales-invoice-netto/MB2', '_blank')</script>
</html>
