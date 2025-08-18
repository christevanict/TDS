@extends('layouts.master')

@section('title', 'Laporan Buku Besar Information')

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
    .filter-input {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
    }
</style>
@endsection

@section('content')
<x-page-title title="Laporan Buku Besar" pagetitle="Laporan Buku Besar" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan Buku Besar </h6>
        <div class="row mb-3">
            <!-- Date From Filter -->
            <div class="col-md-3">
                <label for="date_from">Date From:</label>
                <div class="input-group">
                    <input type="date" id="date_from" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_from"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>

            <!-- Date To Filter -->
            <div class="col-md-3">
                <label for="date_to">Date To:</label>
                <div class="input-group">
                    <input type="date" id="date_to" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_to"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>

            <!-- COA Filter -->
            <div class="col-md-3">
                <label for="coa_id">COA:</label>
                <select id="coa_id" class="form-control">
                    <option value="">Select COA</option>
                    @foreach($coas as $coa)
                        <option value="{{ $coa->account_number }}">{{ $coa->account_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Button -->
            <div class="col-md-3 pt-4">
                <button type="button" class="btn btn-primary" id="btn-search">Search</button>
            </div>
        </div>

        <!-- DataTable -->
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Account Number')}}</th>
                        <th>Nomor Dokumen</th>
                        <th>Tanggal Dokumen</th>
                        <th>Account Name</th>
                        <th>Catatan</th>
                        <th>Debet</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                    <tr>
                        <th></th> <!-- Empty for No column -->
                        <th><input type="text" class="filter-input form-control" placeholder="Search Account Number"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Document Number"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Journal Date"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Account Name"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Note"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Debet"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Credit"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Balance"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- The data will be injected by AJAX after searching --}}
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
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable({
            lengthChange: false,
            @if(Auth::user()->role=='RO01')
            buttons: ['copy', 'print',{
                extend: 'excel',
                filename: function() {
                    var dateFrom = formatDateToDDMMMYYYY($('#date_from').val());
                    var dateTo = formatDateToDDMMMYYYY($('#date_to').val());
                    var accountName = $('#coa_id').find('option:selected').text().replace(' ','');
                    return 'MajuBersama_LaporanBukuBesar_'+accountName+'_' + dateFrom + '_to_' + dateTo;
                }
            },
            {
                extend: 'pdf',
                filename: function() {
                    var dateFrom = formatDateToDDMMMYYYY($('#date_from').val());
                    var dateTo = formatDateToDDMMMYYYY($('#date_to').val());
                    var accountName = $('#coa_id').find('option:selected').text().replace(' ','');
                    return 'MajuBersama_LaporanBukuBesar_'+accountName+'_' + dateFrom + '_to_' + dateTo;
                }
            }],
            @endif
            columns: [
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'account_number' },
                { data: 'document_number' },
                { data: 'document_date' },
                { data: 'account_name' },
                { data: 'notes' },
                {
                    data: 'debet_nominal',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            // Format number with comma as thousand separator and period as decimal
                            return parseFloat(data.replaceAll('.','').replace(',','.')).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        }
                        return data; // Return raw data for sorting and other purposes
                    }
                },
                {
                    data: 'credit_nominal',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            // Format number with comma as thousand separator and period as decimal
                            return parseFloat(data.replaceAll('.','').replace(',','.')).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        }
                        return data; // Return raw data for sorting and other purposes
                    }
                },
                {
                    data: 'balance',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            // Format number with comma as thousand separator and period as decimal
                            return parseFloat(data.replaceAll('.','').replace(',','.')).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        }
                        return data; // Return raw data for sorting and other purposes
                    }
                },
            ]
        });

        table.buttons().container()
            .appendTo('#example_wrapper .col-md-6:eq(0)');

            // Add individual column search
        $('#example thead tr:eq(1) th').each(function(i) {
            var column = table.column(i);
            if (i !== 0) { // Skip the "No" column
                $(this).find('input').on('keyup change', function() {
                    if (column.search() !== this.value) {
                        column.search(this.value).draw();
                    }
                });
            }
        });

        // Prevent sorting when clicking on search inputs
        $('#example thead tr:eq(1) th input').on('click', function(e) {
            e.stopPropagation(); // Prevent sorting event
        });

        $('#btn-search').on('click', function() {
            var dateFrom = $('#date_from').val();
            var dateTo = $('#date_to').val();
            var coaId = $('#coa_id').val(); // Get selected COA ID

            $.ajax({
                url: '{{ route("transaction.journal.fetch_ledger_items") }}',
                method: 'GET',
                data: {
                    date_from: dateFrom,
                    date_to: dateTo,
                    coa_id: coaId // Include COA ID in the request
                },
                success: function(response) {
                    table.clear().rows.add(response).draw();
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('An error occurred while fetching data.');
                }
            });
        });

        function formatDateToDDMMMYYYY(dateString) {
            if (!dateString) return 'all'; // Fallback for empty dates
            var date = new Date(dateString);
            var day = String(date.getDate()).padStart(2, '0');
            var month = date.toLocaleString('en-US', { month: 'short' });
            var year = date.getFullYear();
            return `${day}${month}${year}`;
        }
    });
</script>
@endsection
