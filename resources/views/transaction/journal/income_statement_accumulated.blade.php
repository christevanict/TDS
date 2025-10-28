@extends('layouts.master')

@section('title', 'Laporan Laba Rugi Akumulasi')

@section('css')
    <style>
        .btn-square {
            width: 100px;
        }
        .table th, .table td {
            text-align: left;
            vertical-align: middle;
            padding: 12px;
        }
        .table thead {
            background-color: #f2f2f2;
        }
        .table-responsive {
            margin-top: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto;
        }
        .table td, .table th {
            word-wrap: break-word;
            overflow: hidden;
            max-width: 150px;
        }
        .table td {
            padding: 10px;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f8f8;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        .table td, .table th {
            border-left: none;
            border-right: none;
        }
        #incomeStatementTable_wrapper {
            margin-top: 20px;
        }
        .account-type-header {
            font-weight: bold;
            font-size: 16px;
        }
        .account-sub-type {
            padding-left: 30px; /* Indentation for subtypes */
            font-weight: bold;
        }
        .account-name {
            padding-left: 50px; /* Further indentation for account names */
        }
        .account-number {
            text-align: right; /* Align account number to the right */
        }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .text-end {
            text-align: right !important;
        }
    </style>
@endsection

@section('content')
    <x-page-title title="Laporan Laba Rugi Akumulasi" pagetitle="Laporan Laba Rugi Akumulasi" />
    <hr>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-4 text-uppercase">Laporan Laba Rugi Akumulasi</h6>

            <!-- Date Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="date_from">Dari Bulan:</label>
                    <select id="date_from" name="date_from" class="form-select filter-date" required>
                        @php
                            $currentMonth = now()->startOfMonth();
                            $startMonth = \Carbon\Carbon::create(2024, 10, 1);
                            while ($startMonth <= $currentMonth) {
                                $value = $startMonth->format('Y-m-d');
                                $display = $startMonth->format('F Y');
                                echo "<option value=\"$value\">$display</option>";
                                $startMonth->addMonth();
                            }
                        @endphp
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_to">Sampai Bulan:</label>
                    <select id="date_to" name="date_to" class="form-select filter-date" required>
                        @php
                            $currentMonth = now()->startOfMonth();
                            $startMonth = \Carbon\Carbon::create(2024, 10, 1);
                            while ($startMonth <= $currentMonth) {
                                $value = $startMonth->format('Y-m-d');
                                $display = $startMonth->format('F Y');
                                echo "<option value=\"$value\">$display</option>";
                                $startMonth->addMonth();
                            }
                        @endphp
                    </select>
                </div>
                <div class="col-md-2 pt-4">
                    <button type="button" class="btn btn-primary" id="btn-search">Search</button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
                <table id="incomeStatementTable" class="table table-hover mt-3">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Nama Akun</th>
                            <!-- Dynamic month columns will be inserted here -->
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Data will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
            <p class="text-center mt-4" id="noDataMessage">Silakan pilih rentang tanggal dan klik "Search" untuk melihat data Laporan Laba Rugi Akumulasi.</p>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        let translate = {
            'Sales': 'Penjualan',
            'Revenue': 'Pendapatan',
            'Expense': 'Beban',
            'Other Revenue': 'Pendapatan Lain-lain',
            'Other Expense': 'Beban Lain-lain',
            'COGS': 'Harga Pokok Penjualan',
        };

        // Initialize DataTable with dynamic columns
        let tableColumns = [
            { data: 'account_number', className: 'account-number' },
            { data: 'account', className: 'account' }
            // Dynamic month columns and total will be added later
        ];

        let table = $('#incomeStatementTable').DataTable({
            @if(Auth::user()->role=='RO01')
            buttons: [

                'copy',
                'print',
                {
                    extend: 'excel',
                    filename: function() {
                        var dateFrom = $('#date_from').find('option:selected').text().replace(' ','');
                        var dateTo = $('#date_to').find('option:selected').text().replace(' ','');
                        return 'TerraDataSolusi_LaporanLabaRugiAkumulasi_'+dateFrom+'_to_'+dateTo;
                    }
                },
                {
                    extend: 'pdf',
                    title: '',
                    filename: function() {
                        var dateFrom = $('#date_from').find('option:selected').text().replace(' ','');
                        var dateTo = $('#date_to').find('option:selected').text().replace(' ','');
                        return 'TerraDataSolusi_LaporanLabaRugiAkumulasi_'+dateFrom+'_to_'+dateTo;
                    },
                    customize: function(doc) {
                        // Remove default table header
                        doc.content[0].table.headerRows = 0;

                        // Right-align all balance columns
                        doc.content[0].table.body.forEach(row => {
                            for (let i = 2; i < row.length; i++) {
                                row[i].alignment = 'right';
                            }
                        });

                        // Calculate custom header
                        let dateFrom = $('#date_from').val();
                        let dateTo = $('#date_to').val();
                        let startMonth = 'Bulan Tidak Dipilih';
                        let endMonth = 'Bulan Tidak Dipilih';
                        let startYear, endYear;

                        if (dateFrom && dateTo) {
                            const startDate = new Date(dateFrom);
                            const endDate = new Date(dateTo);
                            startYear = startDate.getFullYear();
                            endYear = endDate.getFullYear();
                            startMonth = indonesianMonths[(startDate.getMonth() + 1).toString().padStart(2, '0')] || 'Bulan Tidak Valid';
                            endMonth = indonesianMonths[(endDate.getMonth() + 1).toString().padStart(2, '0')] || 'Bulan Tidak Valid';
                        }

                        // Add custom header at the top
                        doc.content = [{
                            text: [
                                { text: 'Laporan Laba Rugi Akumulasi\n', fontSize: 14, bold: true },
                                { text: 'PT Terra Data Solusi\n', fontSize: 12 },
                                { text: `Untuk periode ${startMonth} ${startYear} sampai dengan ${endMonth} ${endYear}`, fontSize: 12 }
                            ],
                            alignment: 'center',
                            margin: [0, 0, 0, 20]
                        }, ...doc.content];

                        var colCount = new Array();
                        $('#incomeStatementTable').find('tbody tr:first-child td').each(function() {
                            if ($(this).attr('colspan')) {
                                for (var i = 1; i <= $(this).attr('colspan'); i++) {
                                    colCount.push('*');
                                }
                            } else {
                                colCount.push('*');
                            }
                        });

                        doc.content[1].table.widths = colCount;
                    },
                },
            ],
            @endif
            paging: false,
            searching: true,
            ordering: false,
            info: true,
            autoWidth: false,
            responsive: true,
            columns: tableColumns,
            rowCallback: function(row, data) {
                if (data.type === 'account-type') {
                    $(row).addClass('account-type-header');
                    // SEARCH: Original rowCallback for account-type
                    // REPLACE: Ensure balance columns are empty for account-type
                    for (let i = 2; i < table.columns().count(); i++) {
                        $(row).find('td:eq(' + i + ')').text('');
                    }
                    // END REPLACE
                } else if (data.type === 'sub-type') {
                    $(row).addClass('account-sub-type');
                    // SEARCH: Original rowCallback for sub-type
                    // REPLACE: Ensure balance columns are empty for sub-type
                    for (let i = 2; i < table.columns().count(); i++) {
                        $(row).find('td:eq(' + i + ')').text('');
                    }
                    // END REPLACE
                } else if (data.type === 'account-name') {
                    $(row).addClass('account-name');
                } else if (data.type === 'total' || data.type === 'grand-total' || data.type === 'gross-profit') {
                    $(row).addClass('total-row');
                }
            }
        });

        const indonesianMonths = {
            '01': 'Januari',
            '02': 'Februari',
            '03': 'Maret',
            '04': 'April',
            '05': 'Mei',
            '06': 'Juni',
            '07': 'Juli',
            '08': 'Agustus',
            '09': 'September',
            '10': 'Oktober',
            '11': 'November',
            '12': 'Desember'
        };

        table.buttons().container()
            .appendTo('#incomeStatementTable_wrapper .col-md-6:eq(0)');

        function parseMonthRangeToDateRanges(startDate, endDate) {
            const start = new Date(startDate);
            const end = new Date(endDate);
            if (isNaN(start.getTime()) || isNaN(end.getTime())) {
                throw new Error('Invalid date format');
            }
            if (start > end) {
                throw new Error('Start date must be before end date');
            }

            let dateRanges = [];
            let current = new Date(start.getFullYear(), start.getMonth(), 1);
            const endMonth = new Date(end.getFullYear(), end.getMonth() + 1, 0);

            while (current <= endMonth) {
                const dateFrom = new Date(current.getFullYear(), current.getMonth(), 1, 0, 0, 0);
                const dateTo = new Date(current.getFullYear(), current.getMonth() + 1, 0, 23, 59, 59);

                const formatDate = (d) => {
                    const pad = (n) => n.toString().padStart(2, '0');
                    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
                };

                dateRanges.push({
                    date_from: formatDate(dateFrom),
                    date_to: formatDate(dateTo),
                    month_key: `${current.getFullYear()}-${(current.getMonth() + 1).toString().padStart(2, '0')}`
                });

                current.setMonth(current.getMonth() + 1);
            }

            return dateRanges;
        }

        // Initially hide table and show message
        $('#noDataMessage').show();
        $('#incomeStatementTable').parent().hide();

        // Handle the date "delete" button clicks (optional, if you want to add clear buttons)
        $('.clear-date').on('click', function() {
            var target = $(this).data('target');
            $(target).val('');
        });

        // When search button is clicked
        $('#btn-search').on('click', function() {
            let dateFrom = $('#date_from').val();
            let dateTo = $('#date_to').val();

            if (!dateFrom || !dateTo) {
                alert('Silakan pilih rentang tanggal.');
                return;
            }

            const dateRanges = parseMonthRangeToDateRanges(dateFrom, dateTo);

            // Update table columns dynamically
            let newColumns = [
                { data: 'account_number', className: 'account-number' },
                { data: 'account', className: 'account' }
            ];
            let headerRow = '<tr><th>Kode Akun</th><th>Nama Akun</th>';

            dateRanges.forEach(range => {
                const monthYear = range.month_key.split('-');
                const monthName = indonesianMonths[monthYear[1]] || 'Bulan Tidak Valid';
                newColumns.push({
                    data: `balance_${range.month_key}`,
                    className: 'balance text-end',
                    render: function(data, type, row) {
                        return data ? parseFloat(data).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '0';
                    }
                });
                headerRow += `<th class="text-end">${monthName} ${monthYear[0]}</th>`;
            });

            newColumns.push({
                data: 'total_balance',
                className: 'balance text-end',
                render: function(data, type, row) {
                    return data ? parseFloat(data).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '0';
                }
            });
            headerRow += '<th class="text-end">Total</th></tr>';

            // Update table columns
            table.clear();
            table.destroy();
            $('#incomeStatementTable thead').html(headerRow);
            table = $('#incomeStatementTable').DataTable({
                @if(Auth::user()->role=='RO01')
                buttons: [
                    'copy',
                    'print',
                    { extend: 'excel',
                        filename: function() {
                            var dateFrom = $('#date_from').find('option:selected').text().replace(' ','');
                            var dateTo = $('#date_to').find('option:selected').text().replace(' ','');
                            return 'TerraDataSolusi_LaporanLabaRugiAkumulasi_'+dateFrom+'_to_'+dateTo;
                        }
                     },
                    {
                        extend: 'pdf',
                        title: '',
                        filename: function() {
                            var dateFrom = $('#date_from').find('option:selected').text().replace(' ','');
                            var dateTo = $('#date_to').find('option:selected').text().replace(' ','');
                            return 'TerraDataSolusi_LaporanLabaRugiAkumulasi_'+dateFrom+'_to_'+dateTo;
                        },
                        customize: function(doc) {
                            // Remove default table header
                            doc.content[0].table.headerRows = 0;

                            // Add custom header
                            let dateFrom = $('#date_from').val();
                            let dateTo = $('#date_to').val();
                            let startMonth = 'Bulan Tidak Dipilih';
                            let endMonth = 'Bulan Tidak Dipilih';
                            let startYear, endYear;

                            if (dateFrom && dateTo) {
                                const startDate = new Date(dateFrom);
                                const endDate = new Date(dateTo);
                                startYear = startDate.getFullYear();
                                endYear = endDate.getFullYear();
                                startMonth = indonesianMonths[(startDate.getMonth() + 1).toString().padStart(2, '0')] || 'Bulan Tidak Valid';
                                endMonth = indonesianMonths[(endDate.getMonth() + 1).toString().padStart(2, '0')] || 'Bulan Tidak Valid';
                            }

                            doc.content = [{
                                text: [
                                    { text: 'Laporan Laba Rugi Akumulasi\n', fontSize: 14, bold: true },
                                    { text: 'PT Terra Data Solusi\n', fontSize: 12 },
                                    { text: `Untuk periode ${startMonth} ${startYear} sampai dengan ${endMonth} ${endYear}`, fontSize: 12 }
                                ],
                                alignment: 'center',
                                margin: [0, 0, 0, 20]
                            }, ...doc.content];

                            // Right-align balance columns
                            doc.content[1].table.body.forEach(row => {
                                for (let i = 2; i < row.length; i++) {
                                    row[i].alignment = 'right';
                                }
                            });

                            var colCount = new Array();
                            $('#incomeStatementTable').find('tbody tr:first-child td').each(function() {
                                if ($(this).attr('colspan')) {
                                    for (var i = 1; i <= $(this).attr('colspan'); i++) {
                                        colCount.push('*');
                                    }
                                } else {
                                    colCount.push('*');
                                }
                            });

                            doc.content[1].table.widths = colCount;
                        }
                    },

                ],
                @endif
                paging: false,
                searching: true,
                ordering: false,
                info: true,
                autoWidth: false,
                responsive: true,
                columns: newColumns,
                rowCallback: function(row, data) {
                    if (data.type === 'account-type') {
                        $(row).addClass('account-type-header');
                        // SEARCH: Original rowCallback for account-type
                        // REPLACE: Ensure balance columns are empty for account-type
                        for (let i = 2; i < table.columns().count(); i++) {
                            $(row).find('td:eq(' + i + ')').text('');
                        }
                        // END REPLACE
                    } else if (data.type === 'sub-type') {
                        $(row).addClass('account-sub-type');
                        // SEARCH: Original rowCallback for sub-type
                        // REPLACE: Ensure balance columns are empty for sub-type
                        for (let i = 2; i < table.columns().count(); i++) {
                            $(row).find('td:eq(' + i + ')').text('');
                        }
                        // END REPLACE
                    } else if (data.type === 'account-name') {
                        $(row).addClass('account-name');
                    } else if (data.type === 'total' || data.type === 'grand-total' || data.type === 'gross-profit') {
                        $(row).addClass('total-row');
                    }
                }
            });

            table.buttons().container()
                .appendTo('#incomeStatementTable_wrapper .col-md-6:eq(0)');

            // Fetch the data using AJAX
            $.ajax({
                url: '{{ route("transaction.journal.fetchIncomeStatementAccumulated") }}',
                type: 'GET',
                data: { date_from: dateFrom, date_to: dateTo },
                beforeSend: function() {
                    $('#table-body').empty();
                    $('#noDataMessage').hide();
                    $('#incomeStatementTable').parent().show();
                },
                success: function(response) {
                    console.log("Response Data:", response.data);
                    let dataRows = [];
                    let grandTotals = {};
                    let totalSales = {};
                    let totalHPP = {};

                    // Initialize totals for each month
                    dateRanges.forEach(range => {
                        grandTotals[range.month_key] = 0;
                        totalSales[range.month_key] = 0;
                        totalHPP[range.month_key] = 0;
                    });
                    grandTotals['total'] = 0;
                    totalSales['total'] = 0;
                    totalHPP['total'] = 0;

                    if (response.data && Object.keys(response.data).length > 0) {
                        $.each(response.data, function(accountType, accountSubTypes) {
                            let typeTotals = {};
                            dateRanges.forEach(range => {
                                typeTotals[range.month_key] = 0;
                            });
                            typeTotals['total'] = 0;

                            dataRows.push({
                                account_number: `<strong>${translate[accountType] || accountType}</strong>`,
                                account: '',
                                type: 'account-type'
                                // SEARCH: Original account-type data row
                                // REPLACE: Ensure balance fields are empty for account-type
                                // Note: No balance fields are included here as they will be handled by rowCallback
                                // END REPLACE
                            });

                            $.each(accountSubTypes, function(subType, accounts) {
                                dataRows.push({
                                    account_number: subType,
                                    account: '',
                                    type: 'sub-type'
                                    // SEARCH: Original sub-type data row
                                    // REPLACE: Ensure balance fields are empty for sub-type
                                    // Note: No balance fields are included here as they will be handled by rowCallback
                                    // END REPLACE
                                });

                                $.each(accounts, function(i, account) {
                                    let row = {
                                        account_number: account.account_number || 'N/A',
                                        account: account.account_name,
                                        type: 'account-name'
                                    };
                                    let totalBalance = 0;

                                    dateRanges.forEach(range => {
                                        const balance = parseFloat(account.balances[range.month_key] || 0);
                                        row[`balance_${range.month_key}`] = balance;
                                        totalBalance += balance;
                                        typeTotals[range.month_key] += balance;
                                        grandTotals[range.month_key] += balance;
                                        if (accountType === 'Sales') {
                                            totalSales[range.month_key] += balance;
                                        }
                                        if (accountType === 'COGS') {
                                            totalHPP[range.month_key] += balance;
                                        }
                                    });

                                    row['total_balance'] = totalBalance;
                                    typeTotals['total'] += totalBalance;
                                    grandTotals['total'] += totalBalance;
                                    if (accountType === 'Sales') {
                                        totalSales['total'] += totalBalance;
                                    }
                                    if (accountType === 'COGS') {
                                        totalHPP['total'] += totalBalance;
                                    }

                                    dataRows.push(row);
                                });
                            });

                            let totalRow = {
                                account_number: `<strong>Total ${translate[accountType] || accountType}</strong>`,
                                account: '',
                                type: 'total'
                            };
                            dateRanges.forEach(range => {
                                totalRow[`balance_${range.month_key}`] = typeTotals[range.month_key];
                            });
                            totalRow['total_balance'] = typeTotals['total'];
                            dataRows.push(totalRow);

                            if (accountType === 'COGS') {
                                let grossProfitRow = {
                                    account_number: '<strong>Laba / Rugi Kotor</strong>',
                                    account: '',
                                    type: 'gross-profit'
                                };
                                dateRanges.forEach(range => {
                                    grossProfitRow[`balance_${range.month_key}`] = totalSales[range.month_key] + totalHPP[range.month_key];
                                });
                                grossProfitRow['total_balance'] = totalSales['total'] + totalHPP['total'];
                                dataRows.push(grossProfitRow);
                            }
                        });

                        let grandTotalRow = {
                            account_number: '<strong>Laba / Rugi Bersih</strong>',
                            account: '',
                            type: 'grand-total'
                        };
                        dateRanges.forEach(range => {
                            grandTotalRow[`balance_${range.month_key}`] = grandTotals[range.month_key];
                        });
                        grandTotalRow['total_balance'] = grandTotals['total'];
                        dataRows.push(grandTotalRow);

                        console.log("Data Rows:", dataRows);
                        table.clear().rows.add(dataRows).draw();
                        $('#noDataMessage').hide();
                    } else {
                        $('#noDataMessage').show();
                        $('#incomeStatementTable').parent().hide();
                    }
                },
                error: function(error) {
                    console.error("Error:", error);
                    alert('Terjadi kesalahan. Silakan coba lagi.');
                }
            });
        });
    });
</script>
@endsection
