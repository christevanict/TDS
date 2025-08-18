<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Picking Order - {{ $salesOrder->sales_order_number }}</title>
    <style>
        .no-border {
            border: none;
        }

        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 900px;
            height: auto;
            background: #fff;
        }

        .invoice-header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }

        .invoice-header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }

        .details-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            margin-top: 20px;
        }

        .company-details,
        .customer-details {
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
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            padding: 7px;
            text-align: center;
        }

        .items-table th {
            background-color: #123a60;
            color: white;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .footer {
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>
    @php
        function formatNumber($number)
        {
            return floor($number) == $number ? number_format($number, 0) : number_format($number, 2);
        }
    @endphp
    <div class="container">
        <div style="border-bottom: 2px solid #333;">
            <table>
                <tr>
                    <td>
                        <img src="{{ public_path('build/images/logo.jpg') }}" alt=" " class="logo"
                            style="width:124px;height:auto;">
                    </td>
                    <td style="max-width: 300px;">
                        <h1 style="margin-bottom: 0;">TDS, CV</h1>
                        <p></p>
                    </td>
                </tr>
            </table>
        </div>

        <div class="details-container">
            <div class="company-details">
                <table class="table" style="width:100%; border-collapse: collapse;">
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
                            <td colspan="2"></td>
                            <td colspan="2" style="text-align: left;" rowspan="2">
                                <h1><strong>Picking Order</strong></h1>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Kepada</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: left;">
                                {{ $salesOrder->customers->customer_name ?? 'N/A' }}
                            </td>
                            <td style="text-align: left;">No Order #</td>
                            <td style="text-align: left;">: {{ $salesOrder->sales_order_number }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="text-align: left;">
                                {{ $salesOrder->customers->address ?? 'N/A' }}
                            </td>
                            <td style="text-align: left;">Tanggal Order</td>
                            <td style="text-align: left;">:
                                {{ \Carbon\Carbon::parse($salesOrder->order_date)->format('d M Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"></td>
                            <td style="text-align: left;">Tanggal Pengiriman</td>
                            <td style="text-align: left;">:
                                {{ \Carbon\Carbon::parse($salesOrder->delivery_date)->format('d M Y') ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>


        <table class="items-table">
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Kts.</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedDetails as $itemId => $details)
                    @foreach ($details as $detail)
                        <tr>
                            <!-- Akses barcode dari itemDetails -->
                            <td style="text-align: left;">
                                {{ $detail->items?->itemDetails?->first()?->barcode ?? 'N/A' }}
                            </td>

                            <!-- Akses item_code dan item_name dari items -->
                            <td style="text-align: left;">
                                {{ $detail->items?->item_code ?? 'N/A' }}
                            </td>
                            <td style="text-align: left;">
                                {{ $detail->items?->item_name ?? 'N/A' }}
                            </td>

                            <!-- Akses quantity dari detail -->
                            <td style="text-align: right;">
                                {{ formatNumber($detail->qty ?? 0) }}
                            </td>

                            <!-- Akses unit_name dari unitConversion -->
                            <td style="text-align: center;">
                                {{ $detail->items?->itemDetails?->first()?->unitConversion?->unit_name ?? 'N/A' }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>

        <table class="summary-table fixed-bottom" style="border-top: 2px solid #333;">
            <tr>
                <td style="max-width: 30px;" colspan="2"
                    style="border: none; text-align: left; margin-bottom:0; padding-bottom:0;">Keterangan</td>
                <td style="border: none; text-align: center;">Diterima Oleh:</td>
                <td style="border: none; text-align: center;">Disetujui oleh: </td>
            </tr>
            <tr>
                <td style="max-width: 30px;" colspan="2"
                    style="border: none; text-align: left; margin-top:0; padding-top:0;">
                    DIKIRIM KE : {{ $salesOrder->customers->address ?? 'N/A' }}
                </td>
                <td rowspan="1" style="border: none; text-align: center;"></td>
                <td rowspan="1" style="border: none; text-align: center;"></td>
            </tr>
            <tr>
                <td style="max-width: 30px;" colspan="2"
                    style="border: none; text-align: left; margin-bottom:0; padding-bottom:0;">
                    Note:<br>
                    - Pastikan semua item sesuai dengan detail Sales Order yang terlampir.<br>
                    - Harap konfirmasi penerimaan barang dalam waktu 2x24 jam.
                </td>
                <td style="border: none; text-align: center; margin:0; padding-top:0; padding-bottom:0;"></td>
                <td style="border: none; text-align: center; margin:0; padding-top:0; padding-bottom:0;"></td>
            </tr>
            <tr>
                <td style="max-width: 30px;" colspan="2"
                    style="border: none; text-align: left; margin-top:0; padding-top:0;"></td>
                <td style="border: none; text-align: center; margin:0; padding-top:0;">____________________________</td>
                <td style="border: none; text-align: center; margin:0; padding-top:0;">____________________________</td>
            </tr>
            <tr>
                <td style="max-width: 30px;" colspan="2"
                    style="border: none; text-align: left; margin-top:0; padding-top:0;">
                    - Apabila terjadi kesalahan pengiriman, mohon segera hubungi kami.
                </td>
                <td style="border: none; text-align: left; margin:0; padding-top:0;">Tgl.</td>
                <td style="border: none; text-align: left; margin:0; padding-top:0;">Tgl.</td>
            </tr>
            <tr style="height: 2px;">
                <td colspan="2" style="border: none; text-align: left;">Terima Kasih</td>
                <td style="border: none; text-align: left; margin:0; padding-top:0;"></td>
                <td style="border: none; text-align: left; margin:0; padding-top:0;"></td>
            </tr>
            <tr style="height: 2px;">
                <td colspan="2" style="border: none; text-align: left;">
                    ---------------------------------------------------------------
                </td>
                <td style="border: none; text-align: left; margin:0; padding-top:0;"></td>
                <td style="border: none; text-align: left; margin:0; padding-top:0;"></td>
            </tr>
        </table>
    </div>
    <script type="text/php">

        if (isset($pdf)) {
            $x = 450;
            $y = 820;
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $font =  $fontMetrics->get_font("helvetica", "italic");
            $size = 8;
            $color = array(0,0,0);
            $word_space = 0.0;  //  default
            $char_space = 0.0;  //  default
            $angle = 0.0;   //  default
            $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
        }

    </script>
    </div>
</body>

</html>
