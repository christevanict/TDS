@extends('layouts.master')

@section('title', 'Laporan Piutang')

@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }

    .btn-insert {
        margin-bottom: 20px;
    }

    .btn-print, .btn-edit {
        margin-right: 10px;
    }

    .date-filter {
        max-width: 200px;
    }

    .balance-row {
        font-weight: bold;
        background-color: #f8f9fa;
    }

    .filter-container {
        margin-bottom: 15px;
    }

    .column-search {
        width: 100%;
        padding: 3px;
        font-size: 12px;
    }
</style>
@endsection

@section('content')
<x-page-title title="Journal" pagetitle="Laporan Piutang" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan Piutang</h6>
        <form method="GET" action="{{ route('transaction.journal.receivableReport') }}" class="mb-3">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" class="form-control date-filter date-picker" id="date_from" name="date_from" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" class="form-control date-filter date-picker" id="date_to" name="date_to" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('transaction.journal.receivableReport') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div class="filter-container">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="status_filter" class="form-label">Status</label>
                    <select id="status_filter" class="form-control">
                        <option value="all" selected>Semua</option>
                        <option value="lunas">Lunas</option>
                        <option value="belum_lunas">Belum Lunas</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="">
                <a href="{{ route('transaction.journal.receivableReport.pdf', ['date_from' => request('date_from'), 'date_to' => request('date_to')]) }}" target="_blank">
                    <button type="submit" class="btn btn-secondary btn-square print-button" id="btn-print">View Detail</button>
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pelanggan</th>
                        <th>Nomor Faktur Penjualan</th>
                        <th>Tanggal Faktur Penjualan</th>
                        <th>Tanggal Tenggat</th>
                        <th>Total Piutang</th>
                        <th>Total Terbayar</th>
                        <th>Sisa Piutang</th>
                    </tr>
                    <tr>
                        <th><input type="text" class="column-search form-control" placeholder="Search No"></th>
                        <th><input type="text" class="column-search form-control" placeholder="Search Pelanggan"></th>
                        <th><input type="text" class="column-search form-control" placeholder="Search Nomor Faktur"></th>
                        <th><input type="text" class="column-search form-control" placeholder="Search Tanggal Faktur"></th>
                        <th><input type="text" class="column-search form-control" placeholder="Search Tanggal Tenggat"></th>
                        <th><input type="text" class="column-search form-control" placeholder="Search Total Piutang"></th>
                        <th><input type="text" class="column-search form-control" placeholder="Search Total Terbayar"></th>
                        <th><input type="text" class="column-search form-control" placeholder="Search Sisa Piutang"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach ($groupedCustomers as $baseCode => $group)
                        @php
                            $total_debt = collect($group['receivables'])->sum('total_debt');
                            $total_paid = collect($group['receivables'])->sum('Amount Paid');
                            $total_balance = collect($group['receivables'])->sum('debt_balance');
                            $ending_balance = $group['beginning_balance'] + $total_balance;
                        @endphp

                        <!-- Beginning Balance Row -->
                        <tr class="balance-row">
                            <td>{{ $no++ }}</td>
                            <td>{{ $group['customer_name'] }}</td>
                            <td>Saldo Awal</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end">{{ number_format($group['beginning_balance'], 2) }}</td>
                        </tr>

                        <!-- Transaction Rows -->
                        @foreach ($group['receivables'] as $debt)
                            <tr class="transaction-row" data-balance="{{ $debt['debt_balance'] }}">
                                <td>{{ $no++ }}</td>
                                <td>{{ $group['customer_name'] }}</td>
                                <td>{{ $debt['document_number'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($debt['document_date'])->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($debt['due_date'])->format('d M Y') }}</td>
                                <td class="text-end">{{ number_format($debt['total_debt'], 2) }}</td>
                                <td class="text-end">{{ number_format($debt['Amount Paid'], 2) }}</td>
                                <td class="text-end">{{ number_format($debt['debt_balance'], 2) }}</td>
                            </tr>
                        @endforeach

                        <!-- Ending Balance Row -->
                        <tr class="balance-row">
                            <td>{{ $no++ }}</td>
                            <td>{{ $group['customer_name'] }}</td>
                            <td>Saldo Akhir</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end">{{ number_format($ending_balance, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-end">Total:</th>
                        <th class="text-end">{{ number_format($groupedCustomers->sum(function($group) { return collect($group['receivables'])->sum('total_debt'); }), 2) }}</th>
                        <th class="text-end">{{ number_format($groupedCustomers->sum(function($group) { return collect($group['receivables'])->sum('Amount Paid'); }), 2) }}</th>
                        <th class="text-end">{{ number_format($groupedCustomers->sum(function($group) { return $group['beginning_balance'] + collect($group['receivables'])->sum('debt_balance'); }), 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

@if (session('success'))
<script>
    Swal.fire({
        title: 'Success!',
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonText: 'OK'
    });
</script>
@endif

@if (session('error'))
<script>
    Swal.fire({
        title: 'Error!',
        text: "{{ session('error') }}",
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
@endif

@endsection

@section('scripts')
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
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

        var table = $('#example').DataTable({
            lengthChange: false,
            paging: true,
            ordering: false,
            buttons: [
                {
                    extend: 'copy',
                },
                {
                    extend: 'print',
                },
                {
                    extend: 'excel',
                    filename: function() {
                        var dateFrom = formatDateToDDMMMYYYY($('#date_from').val());
                        var dateTo = formatDateToDDMMMYYYY($('#date_to').val());
                        return 'MajuBersama_LaporanPiutang_'+ dateFrom + '_to_' + dateTo;
                    }
                },
                {
                    extend: 'pdf',
                    title: '',
                    filename: function() {
                        var dateFrom = formatDateToDDMMMYYYY($('#date_from').val());
                        var dateTo = formatDateToDDMMMYYYY($('#date_to').val());
                        return 'MajuBersama_LaporanPiutang_'+ dateFrom + '_to_' + dateTo;
                    },
                    pageSize: 'A4',
                    orientation: 'portrait',
                    customize: function(doc) {
                        doc.pageMargins = [20, 60, 20, 40];

                        // Remove default table header
                        doc.content[0].table.headerRows = 1; // Keep one header row for PDF

                        // Right-align numeric columns (Total Piutang, Total Terbayar, Sisa Piutang)
                        doc.content[0].table.body.forEach(row => {
                            row[5].alignment = 'right';
                            row[6].alignment = 'right';
                            row[7].alignment = 'right';
                        });

                        // Get date_from and date_to
                        let dateFrom = $('#date_from').val();
                        let dateTo = $('#date_to').val();

                        // Format dates to Indonesian format
                        let formattedDateFrom = '';
                        let formattedDateTo = '';
                        if (dateFrom) {
                            const [yearFrom, monthFrom, dayFrom] = dateFrom.split('-');
                            formattedDateFrom = `${parseInt(dayFrom)} ${indonesianMonths[monthFrom]} ${yearFrom}`;
                        } else {
                            formattedDateFrom = 'Awal';
                        }
                        if (dateTo) {
                            const [yearTo, monthTo, dayTo] = dateTo.split('-');
                            formattedDateTo = `${parseInt(dayTo)} ${indonesianMonths[monthTo]} ${yearTo}`;
                        } else {
                            formattedDateTo = 'Akhir';
                        }

                        // Add custom header
                        doc.content = [{
                            text: [
                                { text: 'Laporan Piutang\n', fontSize: 14, bold: true },
                                { text: 'CV Maju Bersama\n', fontSize: 12 },
                                { text: `Untuk periode ${formattedDateFrom} sampai dengan ${formattedDateTo}`, fontSize: 12 }
                            ],
                            alignment: 'center',
                            margin: [0, 0, 0, 20]
                        }, {
                            table: doc.content[0].table,
                            layout: {
                                hLineWidth: function(i, node) { return 0.5; },
                                vLineWidth: function(i, node) { return 0.5; },
                                hLineColor: function(i, node) { return '#000000'; },
                                vLineColor: function(i, node) { return '#000000'; },
                                paddingLeft: function(i, node) { return 4; },
                                paddingRight: function(i, node) { return 4; },
                                paddingTop: function(i, node) { return 4; },
                                paddingBottom: function(i, node) { return 4; }
                            },
                            margin: [0, 0, 0, 0],
                            widths: ['5%', '15%', '15%', '10%', '10%', '15%', '15%', '15%']
                        }];

                        // Style the table header
                        doc.content[1].table.body[0].forEach(cell => {
                            cell.fillColor = '#d3d3d3';
                            cell.color = '#000000';
                            cell.fontSize = 10;
                            cell.bold = true;
                        });

                        // Style balance rows
                        doc.content[1].table.body.forEach(row => {
                            if (row[2].text === 'Saldo Awal' || row[2].text === 'Saldo Akhir') {
                                row.forEach(cell => {
                                    cell.fillColor = '#f8f9fa';
                                    cell.bold = true;
                                });
                            }
                        });
                    },
                },

            ],
            initComplete: function() {
                // Column search inputs
                this.api().columns().every(function(index) {
                    var column = this;
                    var input = $('input', column.header());
                    input.on('keyup change', function() {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });

                // Status filter
                $('#status_filter').on('change', function() {
                    var value = $(this).val();
                    table.draw();
                });

                $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                    var status = $('#status_filter').val();
                    var row = table.row(dataIndex);
                    var isTransactionRow = row.node().classList.contains('transaction-row');
                    var balance = parseFloat(row.data()[7].replace(/,/g, '')) || 0;

                    if (!isTransactionRow) {
                        return true; // Always show balance rows
                    }

                    if (status === 'all') {
                        return true;
                    } else if (status === 'lunas' && Math.floor(balance) === 0) {
                        return true;
                    } else if (status === 'belum_lunas' && balance > 0) {
                        return true;
                    }
                    return false;
                });
            }
        });

        function formatDateToDDMMMYYYY(dateString) {
            if (!dateString) return 'all'; // Fallback for empty dates
            var date = new Date(dateString);
            var day = String(date.getDate()).padStart(2, '0');
            var month = date.toLocaleString('en-US', { month: 'short' });
            var year = date.getFullYear();
            return `${day}${month}${year}`;
        }

        table.buttons().container()
            .appendTo('#example_wrapper .col-md-6:eq(0)');
    });
</script>
@endsection
