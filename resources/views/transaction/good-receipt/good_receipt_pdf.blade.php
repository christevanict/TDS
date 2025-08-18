
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{__('Good Receipt')}} - {{ $goodReceipt->good_receipt_number }}</title>
    <style>
        @import url('https://fonts.cdnfonts.com/css/dejavu-sans-mono');
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
            font-family: 'DejaVu Sans', monospace;
            font-size: 17.5px;
            font-weight: 600;
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
        .print-page {
            page-break-before: always; /* Start each section on a new page */
            page-break-after: always;  /* Ensure nothing spills onto the next page */
            min-height: 14.5cm; /* Approximate A4 height to fill one page, adjust as needed */
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
            .print-page {
                margin: 0;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <pre>




  TDS                      PENERIMAAN BARANG
  Kepada Yth.
  {{ str_pad($goodReceipt->supplier->supplier_name ?? 'N/A', 35) }}No. Penerimaan Barang : {{ $goodReceipt->good_receipt_number }}
  {{ str_pad($goodReceipt->supplier->address ?? 'N/A', 35) }}No. Dokumen Supplier  : {{$goodReceipt->vendor_number}}
  {{ str_pad('', 35) }}Tanggal               : {{ \Carbon\Carbon::parse($goodReceipt->document_date)->format('d M Y') }}


 ---------------------------------------------------------------------------------------
 |NO.|NAMA BARANG                                                  | COLY|          QTY|
 ---------------------------------------------------------------------------------------
@foreach($details as $index => $detail)
@php
    $maxLength = 61; // Max length for item name
    $itemName = $detail->item_name;
    $lines = explode("\n", wordwrap($itemName, $maxLength, "\n", false)); // Word-based wrapping
@endphp
@foreach($lines as $lineIndex => $line)
@if($lineIndex == 0)
 |{{ str_pad($loop->parent->iteration . '.', 3) }}|{{ str_pad($line, 61) }}|{{ str_pad(number_format($detail->qty, 0), 5, ' ', STR_PAD_LEFT) }}|{{ str_pad(number_format($detail->base_qty * $detail->qty, 2, ',', '.') . ' ' . $detail->baseUnit, 13, ' ', STR_PAD_LEFT) }}|
@else
 |{{ str_pad('', 3) }}|{{ str_pad($line, 61) }}|{{ str_pad('', 5) }}|{{ str_pad('', 13) }}|
@endif
@endforeach
@endforeach
 ---------------------------------------------------------------------------------------
        </pre>
        <button class="btn-print" onclick="window.print();">Print</button>
        <a class="btn-print" href="{{route('transaction.warehouse.good_receipt')}}">Kembali</a>
    </div>
</body>
</html>
