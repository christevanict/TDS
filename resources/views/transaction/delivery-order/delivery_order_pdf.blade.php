<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Delivery Order - {{ $deliveryOrder->delivery_order_number }}</title>
    <style>
        .no-border {
            border: none;
        }

        body {
            font-family: Verdana, Geneva, Tahoma, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
        }

        .container {
            width: 100%;
            max-width: 900px;
            height: auto;
            /* margin: auto; */
            /* padding: 20px; */
            background: #fff;
            /* border-radius: 5px; */
            /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); */
        }

        .invoice-header {
            text-align: left;
            margin-bottom: 20px;
            padding-bottom: 10px;

        }

        .invoice-header h1 {
            margin: 0;
            font-size: 20 px;
            color: #333;
        }

        .invoice-header p {
            margin: 5px 0;
            font-size: 12px;
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
            /* Reduced margin */
            padding: 5px;
            /* Reduced padding */
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .section-title {
            font-size: 12px;
            /* Increased header size */
            margin-bottom: 5px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding: 3px;
            padding-bottom: 3px;
        }

        .details-text {
            font-size: 12px;
            /* Details text size */
            padding-left: 3px;
            margin-bottom: 2px;
            /* Further reduced bottom margin */
            line-height: 0.5;
            /* Adjust line height for tighter spacing */
        }

        .fixed-bottom {
            position: fixed;
            bottom: 350px;
            left: 0;
            width: 100%;
            background-color: white;
            /* Optional: Set a background color */
            z-index: 500;
            /* Optional: Ensure it stays above other content */
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;

            margin-bottom: 20px;
        }

        .items-table th,
        .items-table td {
            /* border: 1px solid #ccc; */
            padding: 7px;
            text-align: center;
        }

        .items-table th {
            background-color: #123a60;
            color: white;
        }

        .items-table tbody tr:nth-child(even) {
            background-color: #fff;
        }

        .items-table tbody tr:hover {
            background-color: #fff;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .summary-table th,
        .summary-table td {
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
            /* margin-top: 30px; */
            text-align: center;
            font-size: 10px;
            background-color: #fff;
        }

        .signature {
            text-align: center;
            /* Center the text within each signature */
            flex: 1;
            /* Each signature takes equal width */
            margin: 0 10px;
            /* Adjust margin for spacing */
            max-width: 200px;
            /* Set a maximum width for each signature */
        }

        .signature-title {
            margin-top: 50px;
            /* Adjust as needed */
            margin-bottom: 20px;
            /* Reduce bottom margin */
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div style="border-bottom: 2px solid #333;">
            <table>
                <tr>
                    <td>
                        <img src="{{ public_path('build/images/logo.jpg') }}"
                            style="width:124px;height:auto;">
                    </td>
                    <td>
                        <h1>TDS, CV</h1>
                        <p></p>
                    </td>
                </tr>
            </table>
        </div>

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
                            <td colspan="2"></td>
                            <td colspan="2" style="text-align: left" rowspan="2">
                                <h1><strong>Pengiriman Barang</strong></h1>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3"><strong>Kepada</strong></td>
                        </tr>
                        <tr>
                            <td colspan="2" style="vertical-align: left;">
                                {{ $deliveryOrder->customer->customer_name ?? 'N/A' }} </td>
                            <td style="vertical-align: left;">No Form # </td>
                            <td style="vertical-align: left;">: {{ $deliveryOrder->delivery_order_number }}</td>
                        </tr>
                        <tr>
                            <td colspan="2" style="vertical-align: left;">
                                {{ $deliveryOrder->customer->address ?? 'N/A' }}</td>
                            <td style="vertical-align: left;">No Faktur # </td>
                            <td style="vertical-align: left;">:
                                {{ implode(', ', $pos) }}
                            </td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong></strong></td>
                            <td style="vertical-align: left;"></td>
                            <td style="vertical-align: left;">Tanggal</td>
                            <td style="vertical-align: left;">:
                                {{ \Carbon\Carbon::parse($deliveryOrder->delivery_date)->format('d M Y') }}
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
                    <th>Kuantitas</th>
                    <th>Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($details as $item)
                    <tr>
                        <td>{{ $item->barcode }}</td>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ number_format($item->qty, 0) }}</td>
                        <td>{{ $item->unit }}</td>
                    </tr>
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
                    DIKIRIM KE : {{ $deliveryOrder->customer->address ?? 'N/A' }}
                </td>
                <td rowspan="1" style="border: none; text-align: center;"></td>
                <td rowspan="1" style="border: none; text-align: center;"></td>
            </tr>
            <tr>
                <td style="max-width: 30px;" colspan="2"
                    style="border: none; text-align: left; margin-bottom:0; padding-bottom:0;">
                    Note:<br>
                    - Pastikan semua item sesuai dengan detail {{__('Sales Order')}} yang terlampir.<br>
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
</body>

</html>
