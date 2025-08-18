<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reimburse - {{ $reimburse->reimburse_number }}</title>
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
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
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
        .company-details, .customer-details {
            flex: 1;
            margin: 0 5px; /* Reduced margin */
            padding: 5px; /* Reduced padding */
            border: 1px solid #ccc;
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
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
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
        .summary-table th, .summary-table td {
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


        <div>
            <img src="{{ public_path('build/images/logo.jpg') }}" alt=" " class="logo" style="width:124px;height:63px;">
        </div>
        <div class="invoice-header">
            <h1>Reimburse</h1>
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
                            <td style="vertical-align: left;"><strong>Consignee</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->customers->customer_name ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"><strong>Invoice No</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->reimburse_number }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"></td>
                            <td style="vertical-align: left;">{{$reimburse->sos->customers->address ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"><strong>Issue Date</strong></td>
                            <td style="vertical-align: left;">: {{ \Carbon\Carbon::parse($reimburse->sos->document_date)->format('d-M-Y') }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong>Shipper</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->shipper ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"><strong>ETA Date</strong></td>
                            <td style="vertical-align: left;">: {{ \Carbon\Carbon::parse($reimburse->sos->eta_date)->format('d-M-Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong>MBL</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->mbl ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"><strong>ETD Date</strong></td>
                            <td style="vertical-align: left;">: {{ \Carbon\Carbon::parse($reimburse->sos->etd_date)->format('d-M-Y') ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong>Loading</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->loading ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"><strong>Destination</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->destination ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong>Shipment</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->shipment ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"><strong>HBL</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->hbl ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="vertical-align: left;"><strong>PO</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->manual_number ?? 'N/A' }}</td>
                            <td style="vertical-align: left;"><strong>Vessel</strong></td>
                            <td style="vertical-align: left;">: {{ $reimburse->sos->vessel ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th colspan="2">Description</th>
                    <th>Quantity</th>
                    <th colspan="2"> Unit Price</th>
                    <th colspan="2"> Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reimburse->details as $index => $detail)
                <tr>

                    <td colspan="2" style="text-align: left;">{{ $detail->item_description ?? '' }}</td>
                    <td  style="text-align: center;">1</td>
                    <td colspan="2" style="text-align: right;">{{ formatNumber($detail->price, 2) }}</td>
                    <td colspan="2" style="text-align: right;">{{ formatNumber($detail->price, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                </tr>
                <tr>
                    <td  colspan="3" style="border: none; text-align: left;"> Terbilang :</td>

                    <th colspan="2" style="text-align: left;"> Subtotal</th>
                    <td colspan="2" style="text-align: right;">{{ formatNumber($reimburse->total, 2) }}</td>
                </tr>
                <tr>
                    <td  colspan="3" style="border: none;  text-align: left;"> {{$totalHuruf}}</td>

                    <th colspan="2" style="text-align: left; ">Total Amount</th>
                    <td colspan="2" style="text-align: right; ">{{ formatNumber($reimburse->total, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="6" style="border: none;  text-align: left;"></td>
                    <td style="border: none; border-top: 1px solid black;"></td>

                </tr>
                <tr>
                    <td  colspan="4" style="border: none;"></td>
                    <td style="border: none;"></td>


                </tr>
                <tr>
                    <td  colspan="4" style="border: none;"></td>
                    <td style="border: none;"></td>

                </tr>
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td colspan="2" style="border: none; text-align: left;">Payment should be made to: </td>
                <td style="border: none; text-align: center;"><strong>Dibuat oleh: </strong></td>
                <td style="border: none; text-align: center;"><strong>Disetujui oleh: </strong></td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;">
                </td>
                <td rowspan="3" style="border: none; text-align: center;">
                </td>
                <td rowspan="3" style="border: none; text-align: center;">
                    <img src="data:image/png;base64,{{ base64_encode($imageData) }}" alt="TTD" style="width: 150px; height: auto;">
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;">BANK BCA</td>
                {{-- <td style="border: none; text-align: center;"></td>
                <td style="border: none; text-align: center;"></td> --}}
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;">A/C No. 088-2091111 (IDR)</td>
                {{-- <td style="border: none; text-align: center; "></td>
                <td style="border: none; text-align: center;"></td> --}}
            </tr>
            <tr style="height: 2px;">
                <td colspan="2" style="border: none; text-align: left;"></td>
                <td style="border: none; text-align: center;margin:0; padding:0;"></td>
                <td style="border: none; text-align: center;"></td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;margin:0; padding-top:0;padding-bottom:0;"><strong>BUPOT :</strong> finance@satulogistics.com</td>
                <td style="border: none; text-align: center;margin:0; padding-top:0;padding-bottom:0;"> {{ $reimburse->users->fullname }}</td>
                <td style="border: none; text-align: center;margin:0; padding-top:0;padding-bottom:0;"> Budi Setiawan </td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;margin:0; padding-top:0;"><strong><span style="color: white;">BUPOT :</span></strong> import@satulogistics.com (CC)</td>
                <td style="border: none; text-align: center; margin:0; padding-top:0;">______________________</td>
                <td style="border: none; text-align: center;margin:0; padding-top:0;">______________________</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;margin:0; padding-top:0;"><strong><span style="color: white;"></td>
                <td style="border: none; text-align: center; margin:0; padding-top:0;">AR Admin</td>
                <td style="border: none; text-align: center;margin:0; padding-top:0;">Direktur</td>
            </tr>
        </table>

    </div>
</body>
</html>
