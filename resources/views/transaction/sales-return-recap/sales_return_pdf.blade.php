<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{__('Sales Return')}} - {{ $salesReturn->sales_invoice_number }}</title>
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




    TDS                      RETUR PENJUALAN
    Kepada Yth.
    {{ str_pad($salesReturn->customers->customer_name ?? 'N/A', 35) }}No. Retur : {{ $salesReturn->sales_return_number }}
    {{ str_pad($salesReturn->customers->city?? 'N/A', 35) }}Tanggal     : {{ \Carbon\Carbon::parse($salesReturn->document_date)->format('d M Y') }}

    ---------------------------------------------------------------------------------------
    |NO.|NAMA BARANG                        | COLY|     QTY|        HARGA |         JUMLAH|
    ---------------------------------------------------------------------------------------
@foreach($salesReturn->details as $index => $detail)
@php
    $maxLength = 35; // Max length for item name
    $itemName = $detail->items->item_name;
    $lines = explode("\n", wordwrap($itemName, $maxLength, "\n", false)); // Word-based wrapping
@endphp
@foreach($lines as $lineIndex => $line)
@if($lineIndex == 0)
    |{{ str_pad($loop->parent->iteration . '.', 3) }}|{{ str_pad($line, 35) }}|{{ str_pad(number_format($detail->qty, 0), 5, ' ', STR_PAD_LEFT) }}|{{ str_pad(number_format($detail->base_qty * $detail->qty, 2, ',', '.') . ' ' . $detail->baseUnit->unit_name, 12, ' ', STR_PAD_LEFT) }}|{{ str_pad(number_format($detail->price, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) }}|{{ str_pad(number_format(($detail->qty*$detail->price*$detail->base_qty), 0, ',', '.'), 13, ' ', STR_PAD_LEFT) }}|
@else
    |{{ str_pad('', 3) }}|{{ str_pad($line, 35) }}|{{ str_pad('', 5) }}|{{ str_pad('', 12) }}|{{ str_pad('', 12) }}|{{ str_pad('', 13) }}|
@endif
@endforeach
@endforeach
    ---------------------------------------------------------------------------------------
@php
    $maxLength = 35; // Max length for Terbilang
    $terbilang = "Terbilang: " . $totalHuruf;
    $terbilangLines = explode("\n", wordwrap($terbilang, $maxLength, "\n", false));
    // Ensure at least 3 lines for Terbilang, padding with empty strings if needed
    while (count($terbilangLines) < 3) {
        $terbilangLines[] = '';
    }
@endphp
    {{ str_pad($terbilangLines[0], 35) }}                     Sub Total    : {{ str_pad(number_format($salesReturn->subtotal+$totalDiscount, 0, ',', '.'), 15, ' ', STR_PAD_LEFT) }}
    {{ str_pad($terbilangLines[1], 35) }}                     Discount     : {{ str_pad(number_format($totalDiscount, 0, ',', '.'), 15, ' ', STR_PAD_LEFT) }}
    {{ str_pad($terbilangLines[2], 35) }}                     PPN          : {{ str_pad(number_format($salesReturn->add_tax, 0, ',', '.'), 15, ' ', STR_PAD_LEFT) }}
@if(isset($terbilangLines[3]))
@foreach(array_slice($terbilangLines, 3) as $extraLine)
    {{ str_pad($extraLine, 35) }}
@endforeach
@endif
    {{ str_pad('', 35) }}                     Total Invoice: {{ str_pad(number_format($salesReturn->total, 0, ',', '.'), 15, ' ', STR_PAD_LEFT) }}


        </pre>
        <button class="btn-print" onclick="window.print();">Print</button>
        <a class="btn-print" href="{{route('transaction.sales_invoice')}}">Kembali</a>
    </div>
</body>
</html>
