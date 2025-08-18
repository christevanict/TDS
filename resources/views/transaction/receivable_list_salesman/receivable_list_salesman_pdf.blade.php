<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daftar Tagihan - {{ $receivableList->receivable_list_salesman_number }}</title>
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
            <h1>Daftar Tagihan</h1>
            <p><b>{{ $receivableList->city_code }}</b></p>
            <p><b>Per Tgl. {{ \Carbon\Carbon::parse($receivableList->receivable_list_salesman_date)->format('d-m-Y') }}</b></p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Tgl Faktur</th>
                    <th>No. Faktur</th>
                    <th>Nilai Faktur</th>
                    <th>Pembayaran</th>
                    <th>Terutang</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $listGroupCode = array();
                @endphp
                @foreach($receivableListDetail as $index => $detail)
                    @if((array_search($detail->group_customer, $listGroupCode)) == NULL)
                        @php
                            array_push($listGroupCode,$detail->group_customer);
                            $tes = $customers->where("customer_code",$detail->group_customer)->first();
                        @endphp
                        <tr>
                            <td>{{ $tes->customer_name }}</td>
                            <td></td>
                            <td></td>
                            <td style="vertical-align: left;"></td>
                            <td style="vertical-align: left;"></td>
                            <td style="vertical-align: left;"></td>
                        </tr>
                        <tr>
                            <td>{{ $detail->customer_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($detail->document_date)->format('d/m/Y') }}</td>
                            <td>{{ $detail->document_number }}</td>
                            <td style="vertical-align: left;">RP. {{ formatNumber($detail->nominal, 0) }}</td>
                            <td style="vertical-align: left;">RP. {{ formatNumber($detail->paid, 0) }}</td>
                            <td style="vertical-align: left;">RP. {{ formatNumber($detail->nominal_left, 0) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>{{ $detail->customer_name }}</td>
                            <td>{{ \Carbon\Carbon::parse($detail->document_date)->format('d/m/Y') }}</td>
                            <td>{{ $detail->document_number }}</td>
                            <td style="vertical-align: left;">RP. {{ formatNumber($detail->nominal, 0) }}</td>
                            <td style="vertical-align: left;">RP. {{ formatNumber($detail->paid, 0) }}</td>
                            <td style="vertical-align: left;">RP. {{ formatNumber($detail->nominal_left, 0) }}</td>
                        </tr>
                    @endif
                @endforeach
            </tr>
            </tbody>
        </table>

        <table class="summary-table">
            <tr>
                <td colspan="2" style="border: none; text-align: left;"></td>
                <td colspan="2" style="border: none; text-align: left;">DISIAPKAN {{ date('d-m-Y') }} OLEH:</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;"></td>
                <td colspan="2" style="border: none; text-align: left;">DIPERIKSA OLEH:</td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;"></td>
                <td colspan="2" style="border: none; text-align: left;"></td>
            </tr>
            <tr>
                <td colspan="2" style="border: none; text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
            </tr>
        </table>
    </div>
    </div>
</body>
</html>
