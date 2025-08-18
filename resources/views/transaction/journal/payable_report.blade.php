@extends('layouts.master')

@section('title', 'Laporan Hutang')

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
</style>
@endsection

@section('content')
<x-page-title title="Journal" pagetitle="Laporan Hutang" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan Hutang</h6>
        <form method="GET" action="{{ route('transaction.journal.payableReport') }}" class="mb-3">
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
                    <a href="{{ route('transaction.journal.payableReport') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Supplier')}}</th>
                        <th>{{__('Purchase Invoice Number')}}</th>
                        <th>{{__('Purchase Invoice Date')}}</th>
                        <th>{{__('Due Date')}}</th>
                        <th>Total Hutang</th>
                        <th>Total Terbayar</th>
                        <th>Sisa Hutang</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach ($suppliers as $supplier)
                        @php
                            $total_debt = $supplier->debts->sum('total_debt');
                            $total_paid = $supplier->debts->sum('Amount Paid');
                            $total_balance = $supplier->debts->sum('debt_balance');
                            $ending_balance = $supplier->beginning_balance + $total_balance;
                        @endphp

                        <!-- Beginning Balance Row -->
                        <tr class="balance-row">
                            <td>{{ $no++ }}</td>
                            <td>{{ $supplier->supplier_name }}</td>
                            <td>Saldo Awal</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td class="text-end">{{ number_format($supplier->beginning_balance, 2) }}</td>
                        </tr>

                        <!-- Transaction Rows -->
                        @foreach ($supplier->debts as $debt)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $supplier->supplier_name }}</td>
                                <td>{{ $debt->document_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($debt->document_date)->format('d M Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($debt->due_date)->format('d M Y') }}</td>
                                <td class="text-end">{{ number_format($debt->total_debt, 2) }}</td>
                                <td class="text-end">{{ number_format($debt->{'Amount Paid'}, 2) }}</td>
                                <td class="text-end">{{ number_format($debt->debt_balance, 2) }}</td>
                            </tr>
                        @endforeach

                        <!-- Ending Balance Row -->
                        <tr class="balance-row">
                            <td>{{ $no++ }}</td>
                            <td>{{ $supplier->supplier_name }}</td>
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
                        <th class="text-end">{{ number_format($suppliers->sum(function($supplier) { return $supplier->debts->sum('total_debt'); }), 2) }}</th>
                        <th class="text-end">{{ number_format($suppliers->sum(function($supplier) { return $supplier->debts->sum('Amount Paid'); }), 2) }}</th>
                        <th class="text-end">{{ number_format($suppliers->sum(function($supplier) { return $supplier->beginning_balance + $supplier->debts->sum('debt_balance'); }), 2) }}</th>
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
            paging: false,
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
                        return 'MajuBersama_LaporanHutang_'+ dateFrom + '_to_' + dateTo;
                    }
                },
                {
                    extend: 'pdf',
                    title:'',
                    filename: function() {
                        var dateFrom = formatDateToDDMMMYYYY($('#date_from').val());
                        var dateTo = formatDateToDDMMMYYYY($('#date_to').val());
                        return 'MajuBersama_LaporanHutang_'+ dateFrom + '_to_' + dateTo;
                    },
                    pageSize: 'A4',
                    orientation: 'portrait',
                    customize: function(doc) {
                        doc.pageMargins = [20, 60, 20, 40];

                        // Remove default table header
                        doc.content[0].table.headerRows = 0;

                        // Right-align numeric columns (Total Hutang, Total Terbayar, Sisa Hutang)
                        doc.content[0].table.body.forEach(row => {
                            row[5].alignment = 'right'; // Total Hutang
                            row[6].alignment = 'right'; // Total Terbayar
                            row[7].alignment = 'right'; // Sisa Hutang
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
                                { text: 'Laporan Hutang\n', fontSize: 14, bold: true },
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

                        doc.content[1].table.body[0].forEach(cell => {
                            cell.fillColor = '#ffffff';
                            cell.color = '#000000';
                            cell.fontSize = 10;
                            cell.bold = true;
                        });

                        // Set column widths
                        doc.content[1].table.widths = ['5%', '15%', '15%', '10%', '10%', '15%', '15%', '15%'];
                    },
                },

            ]
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
