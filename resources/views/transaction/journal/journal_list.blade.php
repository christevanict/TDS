@extends('layouts.master')

@section('title', 'Laporan Jurnal Umum')

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

    /* Styling for search inputs in table header */
    .filter-input {
        width: 100%;
        padding: 5px;
        box-sizing: border-box;
    }
</style>
@endsection

@section('content')
<x-page-title title="Journal" pagetitle="Laporan Jurnal Umum" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan Jurnal Umum</h6>
        <div class="row mb-3">
            <div class="col-md-3">
                <label for="date_from">Date From:</label>
                <div class="input-group">
                    <input type="date" id="date_from" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_from"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3">
                <label for="date_to">Date To:</label>
                <div class="input-group">
                    <input type="date" id="date_to" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_to"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3 pt-4">
                <button type="button" class="btn btn-primary" id="btn-search">Search</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Account Number')}}</th>
                        <th>Account Name</th>
                        <th>Document Number</th>
                        <th>Journal Date</th>
                        <th>Note</th>
                        <th>Debet</th>
                        <th>Credit</th>
                    </tr>
                    <tr>
                        <th></th> <!-- Empty for No column -->
                        <th><input type="text" class="filter-input form-control" placeholder="Search Account Number"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Account Name"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Document Number"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Journal Date"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Note"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Debet"></th>
                        <th><input type="text" class="filter-input form-control" placeholder="Search Credit"></th>
                    </tr>
                </thead>
                <tbody>
                    {{-- @foreach ($journals as $generals)
                    <tr class="clickable-row">
                        <td>{{ $generals->account_number }}</td>
                        <td>{{ $generals->coas->account_name }}</td>
                        <td>{{ $generals->document_number }}</td>
                        <td data-order="{{ \Carbon\Carbon::parse($generals->document_date)->format('Y-m-d') }}">{{ \Carbon\Carbon::parse($generals->document_date)->format('d M Y') }}</td>
                        <td>{{ $generals->notes }}</td>
                        <td>{{ $generals->debet_nominal }}</td>
                        <td>{{ $generals->credit_nominal }}</td>
                    </tr>
                    @endforeach --}}
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>{{__('Account Number')}}</th>
                        <th>Account Name</th>
                        <th>Document Number</th>
                        <th>Journal Date</th>
                        <th>Note</th>
                        <th>Debet</th>
                        <th>Credit</th>
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
        var table = $('#example').DataTable({
            lengthChange: false,
            @if(Auth::user()->role=='RO01')
            buttons: ['copy', 'print',{
                extend: 'excel',
                filename: function() {
                    var dateFrom = formatDateToDDMMMYYYY($('#date_from').val());
                    var dateTo = formatDateToDDMMMYYYY($('#date_to').val());
                    return 'MajuBersama_LaporanJurnalUmum_' + dateFrom + '_to_' + dateTo;
                }
            },
            {
                extend: 'pdf',
                filename: function() {
                    var dateFrom = formatDateToDDMMMYYYY($('#date_from').val());
                    var dateTo = formatDateToDDMMMYYYY($('#date_to').val());
                    return 'MajuBersama_LaporanJurnalUmum_' + dateFrom + '_to_' + dateTo;
                }
            }],
            @endif
            columns: [
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1; // Auto-incrementing row number
                    },
                    orderable: false // Disable sorting for "No" column
                },
                { data: 'account_number' },
                { data: 'account_name' },
                { data: 'document_number' },
                { data: 'document_date' },
                { data: 'notes' },
                {
                    data: 'debet_nominal',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            // Format number with comma as thousand separator
                            return parseFloat(data.replaceAll('.', '')).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        }
                        return data; // Return raw data for sorting
                    }
                },
                {
                    data: 'credit_nominal',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter') {
                            // Format number with comma as thousand separator
                            return parseFloat(data.replaceAll('.', '')).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                        }
                        return data; // Return raw data for sorting
                    }
                }
            ],
        });

        // Append DataTables buttons
        table.buttons().container().appendTo('#example_wrapper .col-md-6:eq(0)');

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

        // Date range search
        $('#btn-search').on('click', function() {
            var dateFrom = $('#date_from').val();
            var dateTo = $('#date_to').val();

            $.ajax({
                url: '{{ route("transaction.journal.fetch_items") }}',
                method: 'GET',
                data: {
                    date_from: dateFrom,
                    date_to: dateTo
                },
                success: function(response) {
                    console.log(response);
                    table.clear().rows.add(response).draw();
                },
                error: function(xhr) {
                    console.error(xhr);
                    alert('An error occurred while fetching data.');
                }
            });
        });

        // Clear date inputs
        $('.clear-date').on('click', function() {
            $($(this).data('target')).val('');
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
