@extends('layouts.master')

@section('title', 'Laporan Umur Hutang')

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
        margin-right: 10px; /* Add space between buttons */
    }

    .form-container {
        margin-bottom: 20px;
    }

    .no-data {
        text-align: center;
        margin: 20px 0;
    }
</style>
@endsection

@section('content')
<x-page-title title="Journal" pagetitle="Laporan Umur Hutang" />
<hr>

<div class="card">
    <div class="card-body">
        <!-- Date Filter Form -->
        <div class="form-container">
            <form method="GET" action="{{ route('transaction.journal.payableAging') }}" class="form-inline justify-content-start">
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

        @if (empty($agingReport) || !isset($agingReport))
            <div class="no-data">
                <p>No trial balance data available. Please select a date range and submit to view the report.</p>
            </div>
        @else
            <div class="table-responsive">
                <h1>Laporan Umur Hutang</h1>
                <p>
                    Date:
                    @if(isset($startDate) && isset($endDate))
                        {{ \Carbon\Carbon::parse($startDate)->format('d F Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}
                    @else
                        {{ $today->format('d-M-Y') }}
                    @endif
                </p>
                <div class="row mb-3 d-none">
                    <div class="">
                        <a href="{{ route('transaction.journal.payableAgingPdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank">
                            <button type="button" class="btn btn-secondary btn-square print-button" id="btn-print">View Detail</button>
                        </a>
                    </div>
                </div>
                <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                    <thead>
                        <tr>
                            <th class="d-none">No</th>
                            <th>Invoice Number</th>
                            <th>Invoice Date</th>
                            <th>Total Amount Due</th>
                            <th>Not Yet Due (0 Day)</th>
                            <th>1-30 Days</th>
                            <th>31-60 Days</th>
                            <th>61-90 Days</th>
                            <th>Over 90 Days</th>
                        </tr>
                    </thead>
                    @php
                        $count = 0;
                    @endphp
                    <tbody>
                        @foreach($agingReport as $supplierName => $report)
                            @php
                                $count++;
                            @endphp
                            <tr>
                                <td class="d-none">{{$count}}</td>
                                <td style="font-weight: bold">{{ $supplierName }}</td>
                                <td></td>
                                <td style="font-weight: bold">{{ number_format($report['total_amount_due']) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['0']) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['1-30']) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['31-60']) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['61-90']) }}</td>
                                <td style="font-weight: bold">{{ number_format($report['aging']['over_90']) }}</td>
                            </tr>
                            @php
                                $count++;
                            @endphp
                            <tr>
                                <td class="d-none">{{$count}}</td>
                                <td>SAWAL</td>
                                <td></td>
                                <td>{{ number_format($report['sawal']['total']) }}</td>
                                <td>{{ number_format($report['sawal']['aging']['0']) }}</td>
                                <td>{{ number_format($report['sawal']['aging']['1-30']) }}</td>
                                <td>{{ number_format($report['sawal']['aging']['31-60']) }}</td>
                                <td>{{ number_format($report['sawal']['aging']['61-90']) }}</td>
                                <td>{{ number_format($report['sawal']['aging']['over_90']) }}</td>
                            </tr>
                            @foreach($report['debts'] as $debt)
                                @php
                                    // Use the 'umur' property set in the controller to determine the aging group
                                    $agingGroup = '';
                                    if ($debt->umur == 0) {
                                        $agingGroup = '0';
                                    } elseif ($debt->umur > 0 && $debt->umur <= 30) {
                                        $agingGroup = '1-30';
                                    } elseif ($debt->umur > 30 && $debt->umur <= 60) {
                                        $agingGroup = '31-60';
                                    } elseif ($debt->umur > 60 && $debt->umur <= 90) {
                                        $agingGroup = '61-90';
                                    } else {
                                        $agingGroup = 'over_90';
                                    }
                                    $count++;
                                @endphp
                                <tr>
                                    <td class="d-none">{{$count}}</td>
                                    <td>{{ $debt->document_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($debt->document_date)->format('d M y') }}</td>
                                    <td>{{ number_format($debt->debt_balance) }}</td>
                                    <td>
                                        @if($agingGroup == '0')
                                            {{ number_format($debt->debt_balance) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($agingGroup == '1-30')
                                            {{ number_format($debt->debt_balance) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($agingGroup == '31-60')
                                            {{ number_format($debt->debt_balance) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($agingGroup == '61-90')
                                            {{ number_format($debt->debt_balance) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td>
                                        @if($agingGroup == 'over_90')
                                            {{ number_format($debt->debt_balance) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
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

        var table = $('#example').DataTable( {
            lengthChange: false,
            sorting:false,
            buttons: [
                {
                    extend: 'copy',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8] // Exclude first column (index 0)
                    }
                },
                {
                    extend: 'print',
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'excel',
                    filename: function() {
                        var dateFrom = formatDateToDDMMMYYYY($('#start_date').val());
                        var dateTo = formatDateToDDMMMYYYY($('#end_date').val());
                        return 'TerraDataSolusi_LaporanUmurHutang_'+ dateFrom + '_to_' + dateTo;
                    },
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },
                {
                    extend: 'pdf',
                    title:'',
                    filename: function() {
                        var dateFrom = formatDateToDDMMMYYYY($('#start_date').val());
                        var dateTo = formatDateToDDMMMYYYY($('#end_date').val());
                        return 'TerraDataSolusi_LaporanUmurHutang_'+ dateFrom + '_to_' + dateTo;
                    },
                    pageSize: 'A4',
                    orientation: 'landscape',
                    customize: function(doc) {
                        doc.pageMargins = [20, 60, 20, 40];
                        // Remove default table header
                        doc.content[0].table.headerRows = 0;

                        // Right-align numeric columns (Total Hutang, Total Terbayar, Sisa Hutang)
                        doc.content[0].table.body.forEach(row => {
                            row[2].alignment = 'right'; // Total Hutang
                            row[3].alignment = 'right'; // Total Hutang
                            row[4].alignment = 'right'; // Total Hutang
                            row[5].alignment = 'right'; // Total Terbayar
                            row[6].alignment = 'right'; // Sisa Hutang
                            row[7].alignment = 'right'; // Sisa Hutang
                        });

                        // Get date_from and end_date
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
                                { text: 'Laporan Umur Hutang\n', fontSize: 14, bold: true },
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
                            widths: ['20%', '20%', '10%', '10%', '10%', '10%', '10%', '10%']
                        }];


                        doc.content[1].table.body[0].forEach(cell => {
                            cell.fillColor = '#ffffff';
                            cell.color = '#000000';
                            cell.fontSize = 10;
                            cell.bold = true;
                        });

                        // Set column widths
                        doc.content[1].table.widths = ['20%', '8%', '12%', '12%', '12%', '12%', '12%', '12%'];
                    },
                    exportOptions: {
                        columns: [1, 2, 3, 4, 5, 6, 7, 8]
                    }
                },

            ]
        } );

        function formatDateToDDMMMYYYY(dateString) {
            if (!dateString) return 'all'; // Fallback for empty dates
            var date = new Date(dateString);
            var day = String(date.getDate()).padStart(2, '0');
            var month = date.toLocaleString('en-US', { month: 'short' });
            var year = date.getFullYear();
            return `${day}${month}${year}`;
        }

        table.buttons().container()
            .appendTo( '#example_wrapper .col-md-6:eq(0)' );
    });
</script>
@endsection
