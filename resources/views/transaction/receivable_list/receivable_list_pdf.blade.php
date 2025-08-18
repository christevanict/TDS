<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima - {{ $receivableList->receivable_list_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 12px;
            color: #333;
        }

        .parent {
            position: relative;
        }

        .img-bg {
            opacity: 0.1;
            position: absolute;
            width: 75%;
            height: 75%;
            padding-top: 75px;
            padding-left: 110px;
        }

        .container {
            width: 100%;
            max-width: 900px;
            height: auto;
            /* margin: auto; */
            /* padding: 20px; */

            /* border-radius: 5px; */
            /* box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); */
        }
        .invoice-header {
            text-align: center;
        }
        .invoice-header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .invoice-header p {
            margin: 5px 0;
            font-size: 12px;
        }
        .details-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            margin-top: 5px;
        }
        .company-details, .supplier-details {
            flex: 1;
            margin: 0 5px; /* Reduced margin */
            padding: 5px; /* Reduced padding */
            border-radius: 5px;
        }
        .section-title {
            font-size: 12px; /* Increased header size */
            margin-bottom: 5px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding: 3px;
            padding-bottom: 3px;
        }
        .details-text {
            font-size: 12px; /* Details text size */
            padding-left: 3px;
            margin-bottom: 2px; /* Further reduced bottom margin */
            line-height: 0.5; /* Adjust line height for tighter spacing */
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;

        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            text-align: center;
        }
        .items-table th {
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: right;
        }
        .summary-table th {
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
        }

        .signature {
            text-align: center; /* Center the text within each signature */
            flex: 1; /* Each signature takes equal width */
            margin: 0 10px; /* Adjust margin for spacing */
            max-width: 200px; /* Set a maximum width for each signature */
        }

        .signature-title {
            margin-top: 50px; /* Adjust as needed */
            margin-bottom: 20px; /* Reduce bottom margin */
            font-weight: bold;
        }

    </style>
</head>
<body>
    <div class="parent">
        <img class="img-bg" src="data:image/png;base64,{{ base64_encode($imageData) }}">
    @php
    function formatNumber($number) {
        // Check if the number has a decimal part
        if (floor($number) == $number) {
            // If it's a whole number, format without decimals
            return number_format($number, 0);
        } else {
            // If it has a decimal part, format with 2 decimal places
            return number_format($number, 2);
        }
    }
    @endphp
    <div class="container">
        <div class="invoice-header">
            <h1>Tanda Terima</h1>
            <p><b>{{ $namacv }}</b></p>
        </div>

        <div class="details-container">
            <div class="company-details">
                <table class="table" style="width:100%">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 10%;"></th>
                            <th scope="col" style="width: 40%;"></th>
                            <th scope="col" style="width: 50%;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="vertical-align: left;"><strong>Customer</strong></td>
                            <td style="vertical-align: left;">: {{ $receivableList->customers->customer_name ?? 'N/A' }}</td>
                            <td style="vertical-align: left;">[Halaman pertama untuk CV TDS]</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong>Kota</strong></td>
                            <td style="vertical-align: left;">: {{$receivableList->customers->city ?? 'N/A' }}</td>
                            <td style="vertical-align: left;">[Halaman kedua untuk Customer]</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong>Periode</strong></td>
                            <td style="vertical-align: left;">: {{ \Carbon\Carbon::parse($receivableList->periode)->format('F Y') ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tanggal</th>
                    <th>No. Faktur</th>
                    <th>Nilai Faktur</th>
                </tr>
            </thead>
            <tbody>
                @foreach($receivableList->details as $index => $detail)
                <tr>
                    <td>{{ $detail->customers->customer_name }}</td>
                    <td>{{ \Carbon\Carbon::parse($detail->document_date)->format('d/m/Y') }}</td>
                    <td>{{ $detail->document_number }}</td>
                    <td style="vertical-align: left;">RP. {{ formatNumber($detail->nominal, 0) }}</td>
                </tr>
                @endforeach
                <tr>
                    <th colspan="3">Subtotal Tanda Terima</th>
                    <td><strong>Rp. {{ formatNumber($receivableList->total, 2) }}</strong></td>
                </tr>
                <tr>
                <td style="text-align: left;">Tambahan: </td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
                <td style="text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                <td style="text-align: center;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
            <tr>
                <th colspan="3">Total Tanda Terima</th>
                <td><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
            </tr>
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td style="border: none; text-align: left;">Pembayaran mohon dilakukan ke: </td>
            </tr>
            <tr>
                <td style="border: none; text-align: left;"><strong><u>{{ $norek }}</u></strong></td>
            </tr>
        </table>

        <table class="summary-table">
            <tr>
                <td colspan="2" style="border: none; text-align: left;">Tanggal:</td>
                <td colspan="2" style="border: none; text-align: left;">Disiapkan oleh:</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;"></td>
                <td colspan="2" style="border: none; text-align: left;">Supervisi oleh Sales:</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;"></td>
                <td colspan="2" style="border: none; text-align: left;"></td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;">Stampel dan Tanda Tangan Customer</td>
            </tr>
        </table>
    </div>
    </div>
</body>
</html>
