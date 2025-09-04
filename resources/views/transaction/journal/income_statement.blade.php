@extends('layouts.master')

@section('title', 'Laporan Laba Rugi')

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
            table-layout: fixed;
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
    </style>
@endsection

@section('content')
    <x-page-title title="Laporan Laba Rugi" pagetitle="Laporan Laba Rugi " />
    <hr>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-4 text-uppercase">Laporan Laba Rugi </h6>

            <!-- Date Filters -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <label for="date_to">Date To:</label>
                    <select id="date" name="date" class="form-select filter-date" required>
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
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Data will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
            <p class="text-center mt-4" id="noDataMessage">Please select a date range and click "Search" to view the Laporan Laba Rugi data.</p>
        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {

        let translate = {
            'Sales':'Penjualan',
            'Revenue':'Pendapatan',
            'Expense':'Beban',
            'Other Revenue':'Pendapatan Lain-lain',
            'Other Expense':'Beban Lain-lain',
            'COGS':'Harga Pokok Penjualan',
        };
        // Initialize DataTable
        const table = $('#incomeStatementTable').DataTable({
            @if(Auth::user()->role=='RO01')
            buttons: [
                'copy',
                'print',
                {
                    extend: 'excel',
                    filename: function() {
                        var accountName = $('#date').find('option:selected').text().replace(' ','');
                        return 'MajuBersama_LaporanLabaRugi_'+accountName;
                    }
                },
                {
                    extend: 'pdf',
                    title: '',
                    filename: function() {
                        var accountName = $('#date').find('option:selected').text().replace(' ','');
                        return 'MajuBersama_LaporanLabaRugi_'+accountName;
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


                        let monthName = 'Bulan Tidak Dipilih';
                        let lastDayOfMonth = '31';

                        if (selectedMonth) {
                            monthName = indonesianMonths[bulan] || 'Bulan Tidak Valid';
                            const date = new Date(year, parseInt(bulan, 10), 0);
                            lastDayOfMonth = date.getDate();
                        }

                        // Add custom header at the top
                        doc.content = [{
                            text: [
                                { text: 'Laporan Laba Rugi\n', fontSize: 14, bold: true },
                                { text: 'CV Maju Bersama\n', fontSize: 12 },
                                { text: `Untuk periode 1 ${monthName} 2025 sampai dengan ${lastDayOfMonth} ${monthName} 2025`, fontSize: 12 }
                            ],
                            alignment: 'center',
                            margin: [0, 0, 0, 20]
                        }, ...doc.content];

                        var colCount = new Array();
                        $(incomeStatementTable).find('tbody tr:first-child td').each(function(){
                            if($(this).attr('colspan')){
                                for(var i=1;i<=$(this).attr('colspan');$i++){
                                    colCount.push('*');
                                }
                            }else{ colCount.push('*'); }
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

        function parseMonthToDateRange(selectedDate) {
            // Parse the input date string (e.g., "2025-01-01")
            const date = new Date(selectedDate);

            // Validate the date
            if (isNaN(date.getTime())) {
                throw new Error('Invalid date format');
            }

            // Get start of the month (date_from)
            const dateFrom = new Date(date.getFullYear(), date.getMonth(), 1, 0, 0, 0);

            // Get end of the month (date_to)
            const dateTo = new Date(date.getFullYear(), date.getMonth() + 1, 0, 23, 59, 59);

            // Format dates to "YYYY-MM-DD HH:mm:ss"
            const formatDate = (d) => {
                const pad = (n) => n.toString().padStart(2, '0');
                return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
            };

            return {
                date_from: formatDate(dateFrom), // e.g., "2025-01-01 00:00:00"
                date_to: formatDate(dateTo)      // e.g., "2025-01-31 23:59:59"
            };
        }


        // Initially hide table and show message
        $('#noDataMessage').show();
        $('#incomeStatementTable').parent().hide(); // Hide table initially

        // Function to clear the date input fields
        function clearDateInput(inputId) {
            $(inputId).val(''); // Reset the value of the input field
        }

        // Handle the date "delete" button clicks
        $('.clear-date').on('click', function() {
            var target = $(this).data('target');
            clearDateInput(target);
        });

        // When search button is clicked
        $('#btn-search').on('click', function() {
            let selectedDate = $('#date').val();
            const { date_from, date_to } = parseMonthToDateRange(selectedDate);

            // Fetch the data using AJAX based on selected date range
            $.ajax({
                url: '{{ route("transaction.journal.fetchIncomeStatement") }}',
                type: 'GET',
                data: { date_from: date_from, date_to: date_to },
                beforeSend: function() {
                    $('#table-body').empty(); // Clear existing rows
                    $('#noDataMessage').hide(); // Hide no data message
                    $('#incomeStatementTable').parent().show(); // Show table when data is being loaded
                },
                success: function(response) {
                    console.log("Response Data:", response.data);
                    let grandTotal = 0;
                    let totalSales = 0;
                    let totalHPP = 0;
                    let dataRows = [];

                    if (response.data && Object.keys(response.data).length > 0) {
                        $.each(response.data, function(accountType, accountSubTypes) {
                            let typeTotal = 0;

                            dataRows.push({
                                account_number: `<strong>${translate[accountType] || accountType}</strong>`,
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
                                        balance: parseFloat(account.balance).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',').split('.')[0],
                                        type: 'account-name'
                                    });

                                    typeTotal += parseFloat(account.balance) || 0;
                                    if (accountType === 'Sales') {
                                        totalSales += parseFloat(account.balance) || 0;
                                    }
                                    if (accountType === 'COGS') {
                                        totalHPP += parseFloat(account.balance) || 0;
                                    }
                                    grandTotal += parseFloat(account.balance) || 0;
                                });
                            });

                            dataRows.push({
                                account_number: `<strong>Total ${translate[accountType] || accountType}</strong>`,
                                account: '',
                                balance: `<strong>${typeTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',').split('.')[0]}</strong>`,
                                type: 'total'
                            });

                            if (accountType === 'COGS') {
                                dataRows.push({
                                    account_number: '<strong>Laba / Rugi Kotor</strong>',
                                    account: '',
                                    balance: `<strong>${(totalSales + totalHPP).toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong>`,
                                    type: 'gross-profit'
                                });
                            }
                        });

                        dataRows.push({
                            account_number: '<strong>Laba / Rugi Bersih</strong>',
                            account: '',
                            balance: `<strong>${grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',').split('.')[0]}</strong>`,
                            type: 'grand-total'
                        });

                        console.log("Data Rows:", dataRows);
                        table.clear().rows.add(dataRows).draw();
                        $('#noDataMessage').hide();
                    } else {
                        $('#noDataMessage').show();
                        $('#balanceSheetTable').parent().hide();
                    }
                },
                error: function(error) {
                    console.error("Error:", error); // Log any errors
                    alert('An error occurred. Please try again.');
                }
            });
        });

    });
</script>
@endsection
