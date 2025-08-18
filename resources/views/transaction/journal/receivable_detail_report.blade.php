@extends('layouts.master')

@section('title', 'Journal Information')

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
</style>
@endsection

@section('content')
<x-page-title title="Journal" pagetitle="Receivable Report" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Receivable Report</h6>
        <div class="row mb-3">
            <div class="col-md-4">
                <select class="selectpicker form-control" id="salesmanSearch" data-live-search="true" title="Select Salesman">
                    <option value="">All Salesmen</option>
                    @php
                        $salesmen = $customers->pluck('sales')->unique()->filter()->values();
                    @endphp
                    @foreach($salesmen as $salesman)
                        <option value="{{ $salesman }}">{{ $salesman }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <select class="selectpicker form-control" id="zoneSearch" data-live-search="true" title="Select Zone">
                    <option value="">All Zones</option>
                    @php
                        $zones = $customers->pluck('zone')->unique()->filter()->values();
                    @endphp
                    @foreach($zones as $zone)
                        <option value="{{ $zone }}">{{ $zone }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <button type="button" class="btn btn-primary" id="searchBtn">Filter</button>
            <button type="button" class="btn btn-secondary btn-square print-button" id="btn-print">Lihat Laporan</button>
        </div>
        <div class="table-responsive ">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pelanggan</th>
                        <th>Salesman</th>
                        <th>Zone</th>
                        <th>Nomor Faktur Penjualan</th>
                        <th>Tanggal Faktur Penjualan</th>
                        <th>Waktu Tenggat</th>
                        <th>Total Piutang</th>
                        <th>Total Terbayar</th>
                        <th>Sisa Piutang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                        @foreach ($customers as $cust)
                            @foreach ($cust->receivables as $rei)
                                <tr>
                                    <td>{{ $no++ }}</td>
                                    <td>{{ $cust->customer_name }}</td>
                                    <td>{{ $cust->sales??'' }}</td>
                                    <td>{{ $cust->zone??'' }}</td>
                                    <td>{{ $rei->document_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($rei->document_date)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($rei->due_date)->format('d M Y') }}</td>
                                    <td class="text-end">{{ number_format($rei->total_debt,2) }}</td>
                                    <td class="text-end">{{ number_format($rei->{'Amount Paid'},2) }}</td>
                                    <td class="text-end">{{ number_format($rei->debt_balance,2) }}</td>
                                    <td class="text-end text-light {{ $rei->debt_balance == 0 ? 'bg-success' : ($rei->debt_balance == $rei->total_debt ? 'bg-danger' : 'bg-warning') }} font-weight-bold">
                                        {{ $rei->debt_balance == 0 ? 'Terbayar' : ($rei->debt_balance == $rei->total_debt ? 'Belum Terbayar' : 'Terbayar Sebagian') }}
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                </tbody>
                <tfoot>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.14.0-beta2/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            paging: false,
        });

        $('.selectpicker').selectpicker();

        // Function to build the URL with parameters
        function buildUrl() {
            var salesman = $('#salesmanSearch').val() || '';
            var zone = $('#zoneSearch').val() || '';
            var baseUrl = "{{ route('transaction.journal.receivableDetailReport.pdf') }}";

            var params = new URLSearchParams();
            if (salesman) params.append('salesman', salesman);
            if (zone) params.append('zone', zone);

            var url = baseUrl + (params.toString() ? '?' + params.toString() : '');
            console.log('Built URL:', url);
            return url;
        }

        // Local table search
        $('#searchBtn').on('click', function() {
            var salesmanValue = $('#salesmanSearch').val() || '';
            var zoneValue = $('#zoneSearch').val() || '';

            table.columns(2).search(salesmanValue)
                .columns(3).search(zoneValue)
                .draw();

            // Update the link href
            $('#viewDetailLink').attr('href', buildUrl());
        });

        // Search on select change
        $('#salesmanSearch, #zoneSearch').on('changed.bs.select', function() {
            $('#searchBtn').click();
        });

        // Search on Enter key
        $('#salesmanSearch, #zoneSearch').on('keyup', function(e) {
            if (e.key === 'Enter') {
                $('#searchBtn').click();
            }
        });

        // Handle View Detail click
        $('#btn-print').on('click', function(e) {
            e.preventDefault(); // Prevent default button behavior

            var url = buildUrl();
            console.log('Navigating to:', url);

            // Open in new tab
            window.open(url, '_blank');
        });

        // Initial URL setup
        $('#viewDetailLink').attr('href', buildUrl());
    });
</script>
@endsection
