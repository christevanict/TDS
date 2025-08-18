<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Receipt - {{ $pointOfSale->pos_number }}</title>
    <style>
        @page {
            size: 100mm auto;
            margin: 0;
        }

        body {
            font-family: Arial, sans-serif;
            width: 100mm;
            margin: 0;
            padding: 0;
            font-size: 25px;
            transform: scale(0.8);
            transform-origin: top left;
        }

        .container {
            width: 100%;
            padding: 10mm;
            background-color: #fff;
        }

        h1 {
            text-align: center;
            margin: 0;
            font-size: 25px;
        }

        .header-section {
            text-align: center;
            margin-bottom: 10px;
        }

        .header-section img {
            max-width: 80mm;
            height: auto;
            margin-bottom: 2px;
        }

        .header-section p {
            margin: 0;
            font-size: 16px;
        }

        .divider {
            border-top: 1px dotted #000;
            margin: 10px 0;
        }

        .section {
            margin: 12px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table th,
        table td {
            padding: 6px 0;
            font-size: 20px;
        }

        .items-table th,
        .items-table td {
            text-align: left;
        }

        .items-table .item-name {
            width: 100%;
            text-align: left;
        }

        .totals-section {
            margin-top: 15px;
            font-size: 18px;
        }

        .totals-section table {
            width: 100%;
            margin-top: 5px;
        }

        .totals-section td {
            padding: 3px 0;
            text-align: right;
        }

        .totals-label {
            text-align: left;
            width: 50%;
        }

        .totals-value {
            text-align: right;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 18px;
        }

        .footer p {
            margin: 0;
        }

        .small-font {
            font-size: 18px;
        }

        @media print {
            @page {
                size: 100mm auto;
                margin: 0;
            }

            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    @php
        function formatCurrency($number)
        {
            return number_format($number, 0, ',', '.');
        }

        $totalQuantity = 0;
        $totalItems = count($pointOfSale->details);
        foreach ($pointOfSale->details as $item) {
            $totalQuantity += $item->quantity;
        }

        // Simulating cash received and change for the example
        $cashReceived = $pointOfSale->cash_received ?? 0;
        $change = $cashReceived - $pointOfSale->final_amount;
    @endphp
    <div class="container">
        <!-- Header with Image and Address -->
        <div class="header-section">
            <img src="{{ asset('build/images/logo1.png') }}" alt="VINSMART Logo">
            <p>{{ $department->address }}</p>
            <p>{{ $department->phone }}</p>
        </div>
        <div class="divider"></div>

        <!-- Receipt Header -->
        <div class="section receipt-header">
            <table>
                <tr>
                    <td><span>Pelanggan:</span> {{ $pointOfSale->customer_name ?? 'Umum' }}</td>
                </tr>
                <tr>
                    <td><span>Transaksi:</span>
                        {{ \Carbon\Carbon::parse($pointOfSale->created_at)->format('d M Y H:i') }}</td>
                </tr>
                <tr>
                    <td><span>Karyawan/Kasir:</span> {{ $pointOfSale->created_by }}</td>
                </tr>
            </table>
        </div>
        <div class="divider"></div>

        <!-- Items Table -->
        <div class="section">
            <table class="items-table">
                <tbody>
                    @foreach ($pointOfSale->details as $item)
                        <tr>
                            <td class="item-name">
                                <span>{{ $item->item_name }}</span>
                                <br>
                                {{ $item->quantity }} x {{ formatCurrency($item->price) }}
                            </td>
                            <td class="item-subtotal">{{ formatCurrency($item->subtotal) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div class="divider"></div>
        <div class="totals-section">
            <table>
                <tr>
                    <td class="totals-label"><span>Jumlah Item:</span></td>
                    <td class="totals-value">{{ $totalItems }}</td>
                </tr>
            </table>
        </div>
        <div class="divider"></div>
        <div class="totals-section">
            <table>
                <tr>
                    <td class="totals-label"><span>Subtotal:</span></td>
                    <td class="totals-value">{{ formatCurrency($pointOfSale->total_amount) }}</td>
                </tr>
                <tr>
                    <td class="totals-label"><span>Pajak:</span></td>
                    <td class="totals-value">{{ formatCurrency($pointOfSale->tax) }}</td>
                </tr>
            </table>
        </div>
        <div class="divider"></div>
        <div class="totals-section">
            <table>
                <tr>
                    <td class="totals-label"><span>Total Keseluruhan:</span></td>
                    <td class="totals-value">{{ formatCurrency($pointOfSale->final_amount) }}</td>
                </tr>
                <tr>
                    <td class="totals-label"><span>Uang Diterima:</span></td>
                    <td class="totals-value">{{ formatCurrency($cashReceived) }}</td>
                </tr>
                <tr>
                    <td class="totals-label"><span>Kembalian:</span></td>
                    <td class="totals-value">{{ formatCurrency($change) }}</td>
                </tr>
                <tr>
                    <td class="totals-label" colspan="2" style="text-align: right; font-size: 14px;">
                        *Termasuk Pajak
                    </td>
                </tr>
            </table>
        </div>
        <div class="divider"></div>
        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer">
            <p>Terima Kasih</p>
        </div>
    </div>
    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>
</body>

</html>
