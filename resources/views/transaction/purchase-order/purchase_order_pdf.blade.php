<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{__('Purchase Order')}} - {{ $purchaseOrders->purchase_order_number }}</title>
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
        <pre>




    TDS                      PERMINTAAN PETDSLIAN
    Kepada Yth.
    {{ str_pad($purchaseOrders->suppliers->supplier_name ?? 'N/A', 35) }}No. Invoice : {{ $purchaseOrders->purchase_order_number }}
    {{ str_pad($purchaseOrders->suppliers->address ?? 'N/A', 35) }}Tanggal     : {{ \Carbon\Carbon::parse($purchaseOrders->document_date)->format('d M Y') }}

    ---------------------------------------------------------------------------------------
    |NO.|NAMA BARANG                                                       | COLY|     QTY|
    ---------------------------------------------------------------------------------------
@foreach($purchaseOrders->details as $index => $detail)
@php
    $maxLength = 66; // Max length for item name
    $itemName = $detail->items->item_name;
    $lines = explode("\n", wordwrap($itemName, $maxLength, "\n", false)); // Word-based wrapping
@endphp
@foreach($lines as $lineIndex => $line)
@if($lineIndex == 0)
    |{{ str_pad($loop->parent->iteration . '.', 3) }}|{{ str_pad($line, 66) }}|{{ str_pad(number_format($detail->qty, 0), 5, ' ', STR_PAD_LEFT) }}|{{ str_pad(number_format($detail->base_qty * $detail->qty, 0, ',', '.') . ' ' . $detail->baseUnit->unit_name, 8, ' ', STR_PAD_LEFT) }}|
@else
    |{{ str_pad('', 3) }}|{{ str_pad($line, 35) }}|{{ str_pad('', 5) }}|{{ str_pad('', 8) }}|{{ str_pad('', 14) }}|{{ str_pad('', 15) }}|
@endif
@endforeach
@endforeach
    ---------------------------------------------------------------------------------------

        </pre>
        <button class="btn-print" onclick="window.print();">Print</button>
        <a class="btn-print" href="{{route('transaction.purchase_order')}}">Kembali</a>
    </div>
</body>
</html>
