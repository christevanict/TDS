
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Order - {{ $salesInvoice->sales_invoice_number }}</title>
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
    @php
        $qtys = 0;
    @endphp
    <div class="container">
<pre>



  TDS                      SURAT JALAN
  Kepada Yth.
  {{ str_pad($customerOrigin->customer_name ?? 'N/A', 35) }}No. SJ      :{{ $salesInvoice->sales_invoice_number }}
  {{ str_pad($customerOrigin->city ?? 'N/A', 35) }}Tanggal     : {{ \Carbon\Carbon::parse($salesInvoice->document_date)->format('d M Y') }}

 ---------------------------------------------------------------------------------------
@php
    $count=0;
@endphp
 |NO.|NAMA BARANG                                                |    COLY|         QTY|
 ---------------------------------------------------------------------------------------
@foreach($salesInvoice->details as $index => $detail)
@php
$maxLength = 59; // Max length for item name
$itemName = $detail->items->item_name;
$lines = explode("\n", wordwrap($itemName, $maxLength, "\n", false)); // Word-based wrapping
$qtys+=$detail->qty;
@endphp
@foreach($lines as $lineIndex => $line)
@if($lineIndex == 0)
 |{{ str_pad($loop->parent->iteration . '.', 3) }}|{{ str_pad($line, 59) }}|{{ str_pad(number_format($detail->qty, 2), 8, ' ', STR_PAD_LEFT) }}|{{ str_pad(number_format($detail->base_qty * $detail->qty, 2, ',', '.') . ' ' . $detail->baseUnit->unit_name, 12, ' ', STR_PAD_LEFT) }}|
@else
 |{{ str_pad('', 3) }}|{{ str_pad($line, 59) }}|{{ str_pad('', 8) }}|{{ str_pad('', 12) }}|
@endif
@php
    $count++;
@endphp
@endforeach
@endforeach
@if(14 - $count-1>0)
 |---|-----------------------------------------------------------|--------|------------|
@endif
@for($i = 0; $i < (14 - $count-1); $i++)
 |{{ str_pad('', 3) }}|{{ str_pad('', 59) }}|{{ str_pad('', 8) }}|{{ str_pad('', 12) }}|
@endfor
 ---------------------------------------------------------------------------------------
  Penerima,                                                  Total: {{$qtys}} COLY  Hormat Kami,
              --------------------------------------------------------
              |Apabila Saat Bongkar Dibanting/Kasar Harap Divideo Dan|
              |Dikirim Ke Sales Yg Bersangkutan                      |
              |Hotline Pengaduan: 081938670189 (TLP dan WA)          |
              --------------------------------------------------------
</pre>
        <button class="btn-print" onclick="window.print();">Print</button>
        <a class="btn-print" href="{{route('transaction.sales_invoice')}}">Kembali</a>
    </div>
</body>
</html>
