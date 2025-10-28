@extends('layouts.master')

@section('title', 'Ringkasan Umur Piutang')

@section('css')
<style>
    .form-container {
        margin-bottom: 20px;
    }

    .no-data {
        text-align: center;
        margin: 20px 0;
    }

    .table th, .table td {
        text-align: right;
    }

    .table th:first-child, .table td:first-child {
        text-align: left;
    }
</style>
@endsection

@section('content')
<x-page-title title="Ringkasan Umur Piutang" pagetitle="Ringkasan Umur Piutang" />
<hr>

<div class="card">
    <div class="card-body">
        <!-- Date Filter Form -->
        <div class="form-container">
            <form method="GET" action="{{ route('transaction.receivableAgingSummary') }}" class="form-inline justify-content-start">
                <div class="form-group mb-2 mr-2">
                    <label for="start_date" class="mr-2">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" class="form-control date-picker" value="{{ old('start_date', $startDate ? \Carbon\Carbon::parse($startDate)->format('Y-m-d') : '') }}" required>
                </div>
                <div class="form-group mb-2 mr-2">
                    <label for="end_date" class="mr-2">End Date:</label>
                    <input type="date" name="end_date" id="end_date" class="form-control date-picker" value="{{ old('end_date', $endDate ? \Carbon\Carbon::parse($endDate)->format('Y-m-d') : '') }}" required>
                </div>
                <button type="submit" class="btn btn-primary mb-2">Generate Report</button>
            </form>
        </div>

        @if (empty($agingReport))
            <div class="no-data">
                <p>No receivable aging data available. Please select a date range and submit to view the report.</p>
            </div>
        @else
            <div class="table-responsive">
                <h1>Ringkasan Umur Piutang</h1>
                <p>
                    Date:
                    @if(isset($startDate) && isset($endDate))
                        {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
                    @else
                        {{ $today->format('d-M-Y') }}
                    @endif
                </p>
                <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                    <thead>
                        <tr>
                            <th>Head Customer</th>
                            <th>Total Amount Due</th>
                            <th>Not Yet Due (0 Day)</th>
                            <th>1-30 Days</th>
                            <th>31-60 Days</th>
                            <th>61-90 Days</th>
                            <th>Over 90 Days</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agingReport as $headCustomerName => $report)
                            <tr>
                                <td style="font-weight: bold">{{ $headCustomerName ?? 'Unknown Customer' }}</td>
                                <td style="font-weight: bold">{{ number_format($report['total_amount_due'] ?? 0) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['0'] ?? 0) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['1-30'] ?? 0) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['31-60'] ?? 0) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['61-90'] ?? 0) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['over_90'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
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
            ordering: false,
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
                },
                {
                    extend: 'excel',
                    filename: function() {
                        var dateFrom = formatDateToDDMMMYYYY($('#start_date').val());
                        var dateTo = formatDateToDDMMMYYYY($('#end_date').val());
                        return 'TerraDataSolusi_RingkasanUmurPiutang_'+ dateFrom + '_to_' + dateTo;
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
                },
                {
                    extend: 'pdf',
                    title:'',
                    filename: function() {
                        var dateFrom = formatDateToDDMMMYYYY($('#start_date').val());
                        var dateTo = formatDateToDDMMMYYYY($('#end_date').val());
                        return 'TerraDataSolusi_RingkasanUmurPiutang_'+ dateFrom + '_to_' + dateTo;
                    },
                    pageSize: 'A4',
                    orientation: 'potrait',
                    customize: function(doc) {
                        doc.pageMargins = [20, 60, 20, 40];
                        // Remove default table header
                        doc.content[0].table.headerRows = 0;

                        // Right-align numeric columns (Total Hutang, Total Terbayar, Sisa Hutang)
                        doc.content[0].table.body.forEach(row => {
                            row[1].alignment = 'right'; // Total Hutang
                            row[2].alignment = 'right'; // Total Hutang
                            row[3].alignment = 'right'; // Total Hutang
                            row[4].alignment = 'right'; // Total Terbayar
                            row[5].alignment = 'right'; // Sisa Hutang
                            row[6].alignment = 'right'; // Sisa Hutang
                        });

                        // Get date_from and date_to
                        let dateFrom = $('#start_date').val();
                        let dateTo = $('#end_date').val();

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
                                { text: 'Laporan Ringkasan Umur Piutang\n', fontSize: 14, bold: true },
                                { text: 'PT Terra Data Solusi\n', fontSize: 12 },
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
                            widths: ['10%', '15%', '15%', '15%', '15%', '15%', '15%']
                        }];


                        doc.content[1].table.body[0].forEach(cell => {
                            cell.fillColor = '#ffffff';
                            cell.color = '#000000';
                            cell.fontSize = 10;
                            cell.bold = true;
                        });

                        // Set column widths
                        doc.content[1].table.widths = ['10%', '15%', '15%', '15%', '15%', '15%', '15%'];
                    },
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6]
                    }
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
