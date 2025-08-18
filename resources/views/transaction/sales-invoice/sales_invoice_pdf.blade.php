@php
function formatNumber($number) {
    if (floor($number) == $number) {
        return number_format($number, 0, '.', ',');
    } else {
        return number_format($number, 2, '.', ',');
    }
}

$totalAmount = $salesInvoice->details->sum(function ($detail) {
    return $detail->qty * $detail->price - (($detail->disc_percent / 100) * ($detail->qty * $detail->price) + $detail->disc_nominal);
});
@endphp

<style>
    @page {
        size: A4;
        margin: 0;
    }
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        margin: 0;
        padding: 0;
        line-height: 1.2;
    }
    .container {
        width: 190mm;
        min-height: 260mm;
        margin: 0 auto;
        padding: 0;
        box-sizing: border-box;
        text-align: center;
        position: relative;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    td, th {
        padding: 1.5mm;
        vertical-align: top;
    }
    .header td:first-child img {
        height: 30mm;
        margin-right: 5mm;
    }
    .header td:last-child {
        text-align: right;
    }
    .separator {
        border-top: 2px solid #ddd;
        margin: 3mm 0;
    }
    .address td {
        border: none;
        padding-bottom: 0.5mm;
    }
    .bill-to-table {
        width: 100%;
        text-align: left;
    }
    .item-table {
        height: 500px;
        border: 1px solid #fff;
        margin-top: 0mm;
    }
    .item-table th {
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        text-align: center;
        font-weight: bold;
    }
    .item-table th:last-child {
    }
    .item-table td {
        border: 1px solid #ddd;
        height: 10px;;
    }
    .item-table td:last-child {
    }
    .summary {
        margin-top: 0;
        border-top: none;
    }
    .summary td:first-child {
        width: 50%;
    }
    .summary td:last-child table {
        border: 1px solid #ddd;
        border-top: none;
    }
    .summary td:last-child table td {
        padding: 1.5mm;
        border-bottom: 1px solid #ddd;
    }
    .summary td:last-child table td:first-child {
        text-align: left;
        width: 60%;
    }
    .summary td:last-child table td:last-child {
        text-align: right;
        width: 40%;
    }
    .summary td:last-child table tr:last-child td {
        font-weight: bold;
    }
    .footer {
        position: fixed;
        bottom: 0mm;
        left: 10mm;
        right: 10mm;
        height: 20mm;
    }
    .footer p {
        margin: 0;
        text-align: left;
    }
    .top-right-date {
        position: absolute;
        top: 10mm;
        right: 10mm;
        font-size: 10pt;
    }
    strong {
        font-weight: bold;
    }
</style>

<div class="container">
    <div class="top-right-date">
        {{ \Carbon\Carbon::parse($salesInvoice->created_at ?? now())->format('n/j/Y') }}
    </div>

    <table class="header">
        <tr>
            <td style="width: 35%; text-align: left;">
                <img src="data:image/png;base64,{{ base64_encode($imageData) }}" alt="TDS Logo" style="height: 25mm;margin-top:70px;"><br>
                <b>Spazio Tower Unit 511<br>
                <b>Jl. Mayjend. Jonosewojo No.Kav.3,</b><br>
                <b>Pradahkalikendal, Kec. Dukuhpakis,</b><br>
                <b>Surabaya, Jawa Timur 60225</b><br><br>
                <table class="bill-to-table">
                    <tr>
                        <td style="margin: 0.5mm; text-align:left;"><strong>Bill To</strong></td>
                    </tr>
                    <tr>
                        <td style="margin: 0.5mm;text-align:left; font-weight:normal;">{{ $salesInvoice->customers->customer_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="margin: 0.5mm;text-align:left;font-weight:normal;">{{ $salesInvoice->customers->address ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="margin: 0.5mm;text-align:left;font-weight:normal;">{{ $salesInvoice->customers->city ?? '' }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 30%; text-align: right;">
                <table style="width: 100%; text-align: right;">
                    <tr>
                        <td colspan="2">
                            <h1 style="margin-top: 130px; margin-bottom:50px; text-align:right; font-size:40px;"><strong>INVOICE</strong></h1>
                        </td>
                    </tr>
                    <tr>
                        <td>Invoice #</td>
                        <td style=" text-align:left;">{{ $salesInvoice->sales_invoice_number ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Invoice Date</td>
                        <td style=" text-align:left;">{{ \Carbon\Carbon::parse($salesInvoice->document_date ?? now())->format('F j, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Contract Number #</td>
                        <td style=" text-align:left;" {{ $salesInvoice->contract_number ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Due Date</td>
                        <td style=" text-align:left;">{{ \Carbon\Carbon::parse($salesInvoice->due_date ?? now())->format('F j, Y') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center; border-right: 1px solid #ddd;">Qty</th>
                <th style="width: 50%; text-align: center; border-right: 1px solid #ddd;">Description</th>
                <th style="width: 20%; text-align: center; border-right: 1px solid #ddd;">Unit Price</th>
                <th style="width: 20%; text-align: center;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesInvoice->details as $index => $detail)
                <tr>
                    <td style="text-align: center; border-right: 1px solid #ddd;">{{ formatNumber($detail->qty ?? 0) }}</td>
                    <td style="text-align: left; border-right: 1px solid #ddd;">
                        <div style=" overflow: hidden;">
                            {{ $detail->items->item_name ?? 'N/A' }} {{ $detail->description }}
                        </div>
                    </td>
                    <td style="text-align: right; border-right: 1px solid #ddd;">{{ formatNumber($detail->price ?? 0) }}</td>
                    <td style="text-align: right;">{{ formatNumber($detail->qty * $detail->price ?? 0) }}</td>
                </tr>
            @endforeach
            <tr style="border: none;">
                <td colspan="2" style="vertical-align: top; border: none;"></td>
                <td colspan="1" style="text-align: left; height:10px;;">Subtotal</td>
                <td colspan="1" style="text-align: right; height:10px;;">{{ formatNumber($salesInvoice->subtotal + $discTotal ?? 0) }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;" colspan="2"><strong><i> Terms & Conditions</i></strong><br></td>
                <td style="text-align: left; height:10px;;">@if($salesInvoice->tax_revenue>0)PPh 25 @endif</td>
                <td style="text-align: right; height:10px;;">@if($salesInvoice->tax_revenue>0){{ formatNumber($salesInvoice->tax_revenue ?? 0) }}@endif</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;" colspan="2"><i>Payment is due within 7 days</i></td>
                <td style="text-align: left;  background-color: #f0f0f0;height:10px;;"><strong>Total</strong></td>
                <td style="text-align: right;  background-color: #f0f0f0;height:10px;;"><strong>{{ formatNumber($salesInvoice->total ?? 0) }}</strong></td>
            </tr>
        </tbody>
    </table>
</div>

<div class="footer">
    <p>Pembayaran: {{ $salesInvoice->company->npwp ?? 'BANK BCA 0888 676 867 An. Terra Data Solusi' }}</p>
</div>
