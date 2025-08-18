@extends('layouts.master')

@section('title', 'Neraca Keuangan Information')

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
            overflow-x: auto;
            width: 100%;
        }
        .table {
            width: 100% !important;
            border-collapse: collapse;
            table-layout: auto;
        }
        .table td, .table th {
            white-space: nowrap;
            max-width: none !important;
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
        #balanceSheetTable_wrapper {
            margin-top: 20px;
        }
        .account-type-header {
            font-weight: bold;
            font-size: 16px;
        }
        .account-sub-type {
            padding-left: 30px;
            font-weight: bold;
        }
        .account-name {
            padding-left: 50px;
        }
        .account-number {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
@endsection

@section('content')
    <x-page-title title="Neraca Keuangan" pagetitle="Neraca Keuangan" />
    <hr>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-4 text-uppercase">Neraca Keuangan</h6>

            <!-- Date Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="month">Bulan:</label>
                    <div class="input-group">
                        <select id="date" name="date" class="form-select filter-date" required>
                            @php
                                $currentMonth = now()->startOfMonth();
                                $startMonth = \Carbon\Carbon::create(2025, 1, 1);
                                while ($startMonth <= $currentMonth) {
                                    $value = $startMonth->format('Y-m-d');
                                    $display = $startMonth->format('F Y');
                                    echo "<option value=\"$value\">$display</option>";
                                    $startMonth->addMonth();
                                }
                            @endphp
                        </select>
                        <button type="button" class="btn btn-danger clear-date" data-target="#month"><i class="material-icons-outlined">close</i></button>
                    </div>
                </div>

                <div class="col-md-2 pt-4">
                    <button type="button" class="btn btn-primary" id="btn-search">Search</button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="table-responsive">
                <table id="balanceSheetTable" class="table table-hover mt-3">
                    <thead>
                        <tr>
                            <th>Kode Akun</th>
                            <th>Akun</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Data will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
            <p class="text-center mt-4" id="noDataMessage">Silakan pilih bulan dan klik "Search" untuk melihat data neraca keuangan.</p>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
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

    $(document).ready(function() {
        const table = $('#balanceSheetTable').DataTable({
            @if(Auth::user()->role=='RO01')
            buttons: [
                'copy',
                'print',
                {
                    extend: 'excel',
                    filename: function() {
                        var accountName = $('#date').find('option:selected').text().replace(' ','');
                        return 'MajuBersama_NeracaKeuangan_'+accountName;
                    }
                },
                {
                    extend: 'pdf',
                    title: '',
                    filename: function() {
                        var accountName = $('#date').find('option:selected').text().replace(' ','');
                        return 'MajuBersama_NeracaKeuangan_'+accountName;
                    },
                    customize: function(doc) {
                        // Remove default table header
                        doc.content[0].table.headerRows = 0;

                        // Right-align Nominal column (index 2)
                        doc.content[0].table.body.forEach(row => {
                            row[2].alignment = 'right';
                        });

                        // Calculate custom header
                        let selectedMonth = $('#date').val();
                        const year = selectedMonth.substr(0,4);
                        let bulan = selectedMonth.substr(5,2);

                        if (selectedMonth) {
                            monthName = indonesianMonths[bulan] || 'Bulan Tidak Valid';
                            const date = new Date(year, parseInt(bulan, 10), 0);
                            lastDayOfMonth = date.getDate();
                        }

                        // Add custom header at the top
                        doc.content = [{
                            text: [
                                { text: 'Neraca\n', fontSize: 14, bold: true },
                                { text: 'CV Maju Bersama\n', fontSize: 12 },
                                { text: `Per ${lastDayOfMonth} ${monthName} ${year}`, fontSize: 12 }
                            ],
                            alignment: 'center',
                            margin: [0, 0, 0, 20]
                        }, ...doc.content];

                        var colCount = new Array();
                        $(balanceSheetTable).find('tbody tr:first-child td').each(function(){
                            if($(this).attr('colspan')){
                                for(var i=1;i<=$(this).attr('colspan');$i++){
                                    colCount.push('*');
                                }
                            }else{ colCount.push('*'); }
                        });
                        console.log(doc);

                        doc.content[1].table.widths = colCount;
                    },
                    exportOptions: {
                        rows: function(idx, data, node) {
                            return data.type !== 'total' && data.type !== 'grand-total';
                        }
                    }
                }
            ],
            @endif
            paging: false,
            searching: true,
            ordering: false,
            info: true,
            autoWidth: true,
            responsive: true,
            columns: [
                { data: 'account_number', className: 'account-number' },
                { data: 'account', className: 'account' },
                { data: 'balance', className: 'balance text-end' }
            ],
            rowCallback: function(row, data) {
                if (data.type === 'account-type') {
                    $(row).addClass('account-type-header');
                } else if (data.type === 'sub-type') {
                    $(row).addClass('account-sub-type');
                } else if (data.type === 'account-name') {
                    $(row).addClass('account-name');
                } else if (data.type === 'total' || data.type === 'grand-total') {
                    $(row).addClass('total-row');
                }
            }
        });

        table.buttons().container()
            .appendTo('#balanceSheetTable_wrapper .col-md-6:eq(0)');

        $('#noDataMessage').show();
        $('#balanceSheetTable').parent().hide();

        $('.clear-date').on('click', function() {
            var target = $(this).data('target');
            $(target).val('');
        });

        function parseMonthToDateRange(selectedDate) {
            // Parse the input date string (e.g., "2025-01-01")
            const date = new Date(selectedDate);

            if (isNaN(date.getTime())) {
                throw new Error('Invalid date format');
            }

            // Get start of the month (date_from)
            const dateFrom = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0);

            // Get end of the month (date_to)
            const dateTo = new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59);

            const formatDate = (d) => {
                const pad = (n) => n.toString().padStart(2, '0');
                return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
            };

            return {
                date_from: formatDate(dateFrom),
                date_to: formatDate(dateTo)
            };
        }

        $('#btn-search').on('click', function() {
            let selectedDate = $('#date').val();
            const { date_from, date_to } = parseMonthToDateRange(selectedDate);

            $.ajax({
                url: '{{ route("transaction.journal.balanceSheetData") }}',
                type: 'GET',
                data: { date_from: date_from, date_to: date_to },
                beforeSend: function() {
                    table.clear();
                    $('#noDataMessage').hide();
                    $('#balanceSheetTable').parent().show();
                },
                success: function(response) {
                    console.log("Response Data:", response.data);
                    let grandTotal = 0;
                    let dataRows = [];

                    if (response.data && Object.keys(response.data).length > 0) {
                        $.each(response.data, function(accountType, accountSubTypes) {
                            let typeTotal = 0;

                            dataRows.push({
                                account_number: `<strong>${accountType}</strong>`,
                                account: '',
                                balance: '',
                                type: 'account-type'
                            });

                            $.each(accountSubTypes, function(subType, accounts) {
                                dataRows.push({
                                    account_number: subType,
                                    account: '',
                                    balance: '',
                                    type: 'sub-type'
                                });

                                $.each(accounts, function(i, account) {
                                    dataRows.push({
                                        account_number: account.account_number || 'N/A',
                                        account: account.account_name,
                                        balance: parseFloat(account.balance).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','),
                                        type: 'account-name'
                                    });

                                    typeTotal += parseFloat(account.balance) || 0;
                                    grandTotal += parseFloat(account.balance) || 0;
                                });
                            });

                            dataRows.push({
                                account_number: `<strong>Total for ${accountType}</strong>`,
                                account: '',
                                balance: `<strong>${typeTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong>`,
                                type: 'total'
                            });
                        });

                        dataRows.push({
                            account_number: '<strong>Grand Total</strong>',
                            account: '',
                            balance: `<strong>${grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong>`,
                            type: 'grand-total'
                        });
                        table.clear().rows.add(dataRows).draw();
                        $('#noDataMessage').hide();
                    } else {
                        $('#noDataMessage').show();
                        $('#balanceSheetTable').parent().hide();
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
