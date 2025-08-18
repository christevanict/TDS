
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{__('Bank Cash Out')}} - {{ $bankCashOut->bank_cash_out_number }}</title>
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




    TDS                      OTHER PAYMENT

    Paid For : {{ str_pad($bankCashOut->account_number.'-'.$bankCashOut->coa->account_name ?? 'N/A', 35) }}Voucher No. {{$bankCashOut->bank_cash_out_number}}
    Tanggal  : {{ \Carbon\Carbon::parse($bankCashOut->bank_cash_out_date)->format('d M Y') }}

    --------------------------------------------------------------------------------------
    |Account No.|         Account Name           |     Amount     |         Memo         |
    --------------------------------------------------------------------------------------
@php
    if (!function_exists('splitAtWordBoundary')) {
        function splitAtWordBoundary($text, $maxLength) {
            if (strlen($text) <= $maxLength) return [$text];
            $pos = strrpos(substr($text, 0, $maxLength + 1), ' ');
            if ($pos === false) $pos = $maxLength;
            return [substr($text, 0, $pos), substr($text, $pos + 1)];
        }
    }
@endphp
@foreach($bankCashOut->details as $index => $detail)
@php
    $cols = [
        'account_number' => $detail->account_number . ' ',
        'account_name' => $detail->coa->account_name,
        'amount' => number_format($detail->nominal, 0),
        'memo' => $detail->note
    ];
    $maxLengths = [
        'account_number' => 11,
        'account_name' => 32,
        'amount' => 16,
        'memo' => 22
    ];
    $lines = [[]];
    $remaining = array_map(fn($col) => $col, $cols);

    // Split columns into lines
    while (array_filter($remaining)) {
        $currentLine = [];
        foreach ($cols as $key => $value) {
            $max = $maxLengths[$key];
            $text = $remaining[$key] ?? '';
            if (strlen($text) > 0) {
                $split = splitAtWordBoundary($text, $max);
                $currentLine[$key] = $split[0];
                $remaining[$key] = $split[1] ?? '';
            } else {
                $currentLine[$key] = '';
            }
        }
        $lines[] = $currentLine;
    }
    array_shift($lines); // Remove empty first line
@endphp
@foreach($lines as $line)
    |{{ str_pad($line['account_number'], 11) }}|{{ str_pad($line['account_name'], 32) }}|{{ str_pad($line['amount'], 16, ' ', STR_PAD_LEFT) }}|{{ str_pad($line['memo'], 22, ' ') }}|
@endforeach
@endforeach
    --------------------------------------------------------------------------------------
@php

    $totalPayment = 'Total Payment: '.number_format($bankCashOut->nominal, 0);
    $maxLineLength = 84; // Total line length
    $maxHurufLength = $maxLineLength - strlen($totalPayment); // Max for $totalHuruf per line
    $lines = [];
    $remaining = $totalHuruf;

    // First line: Fit as much of $totalHuruf as possible before Total Payment
    $split = splitAtWordBoundary($remaining, $maxHurufLength);
    $lines[] = $split[0] . str_repeat(' ', $maxHurufLength - strlen($split[0])).' '.  $totalPayment;
    $remaining = $split[1] ?? '';

    // Additional lines: Use same maxHurufLength for consistency
    while (!empty($remaining) && count($lines) < 3) {
        $split = splitAtWordBoundary($remaining, $maxHurufLength);
        $lines[] = $split[0];
        $remaining = $split[1] ?? '';
    }
@endphp
@foreach($lines as $line)
    {{ $line }}
@endforeach

    Memo
    --------------------------------------------------------------------------------------
@php

        $memoLines = [];
        $remainingMemo = $bankCashOut->note ?? '';
        $maxMemoLength = 86;

        while (!empty($remainingMemo) && count($memoLines) < 3) {
            $split = splitAtWordBoundary($remainingMemo, $maxMemoLength);
            $memoLines[] = $split[0];
            $remainingMemo = $split[1] ?? '';
        }
@endphp
@foreach($memoLines as $memoLine)
    {{ $memoLine }}
@endforeach

    Disiapkan                   Dibayar oleh                Diterima oleh


    --------                    ------------                -------------
    Tgl.                        Tgl.                        Tgl.
</pre>
        <button class="btn-print" onclick="window.print();">Print</button>
        <a class="btn-print" href="{{route('transaction.bank_cash_out')}}">Kembali</a>
    </div>
</body>
</html>
