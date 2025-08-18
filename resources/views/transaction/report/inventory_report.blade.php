@extends('layouts.master')

@section('title', 'Laporan Detail Persediaan')

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
<x-page-title title="Persediaan" pagetitle="Laporan Detail Persediaan" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan Detail Persediaan</h6>
        <div class="row mb-3 ">
            <div class="col-md-3">
                <label for="date_from">Dari :</label>
                <div class="input-group">
                    <input type="date" id="date_from" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_from"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3">
                <label for="date_to">Sampai :</label>
                <div class="input-group">
                    <input type="date" id="date_to" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger  clear-date" data-target="#date_to"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3 d-none">
                <label for="warehouse">Gudang :</label>
                <div class="input-group">
                    <select id="warehouse" class="form-control form-select">
                        @foreach ($warehouses as $wr)
                            <option value="{{ $wr->id }}">{{$wr->warehouse_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 pt-4">
                <button type="button" class="btn btn-primary" id="btn-search">Search</button>
            </div>
        </div>
        <div class="table-responsive ">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th rowspan="2">No</th>
                        <th colspan="2">{{__('Item')}}</th>
                        <th colspan="4">Transaksi</th>
                        <th>Lokasi</th>
                        <th colspan="1">Beginning Balance</th>
                        <th colspan="1">In</th>
                        <th colspan="1">Out</th>
                        <th colspan="1">Total</th>
                    </tr>
                    <tr>
                        <!-- Item -->
                        <th>Kode</th>
                        <th>Nama</th>
                        <!-- Transaction -->
                        <th>Jenis</th>
                        <th>Nomor</th>
                        <th>Tanggal</th>
                        <th>Pelanggan/Pemasok</th>
                        <!-- Location -->
                        <th>{{__('Warehouse')}}</th>
                        <!-- Beginning Balance -->
                        <th>Qty</th>
                        {{-- <th>{{__('Price')}}</th> --}}
                        {{-- <th>Nominal</th> --}}
                        <!-- In -->
                        <th>Qty</th>
                        {{-- <th>{{__('Price')}}</th> --}}
                        {{-- <th>Nominal</th> --}}
                        <!-- Out -->
                        <th>Qty</th>
                        {{-- <th>{{__('Price')}}</th> --}}
                        {{-- <th>Nominal</th> --}}
                        <!-- Total -->
                        <th>Qty</th>
                        {{-- <th>{{__('Price')}}</th> --}}
                        {{-- <th>Nominal</th> --}}
                    </tr>
                </thead>
                <tbody>

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
        x = {
            headers: {
                "X-CSRF-TOKEN": "{{csrf_token()}}"
            }
        }
        $.ajaxSetup(x);

        var table = $('#example').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print'],
                columns: [
                    { data: null, render: function(data, type, row, meta) {
                        return meta.row + 1;
                    }},
                    { data: 'item_id' },
                    { data: 'item_name' },
                    { data: 'transaction_type' },
                    { data: 'document_number' },
                    { data: 'document_date' },
                    { data: 'from_to' },
                    { data: 'warehouse_name' },
                    { data: 'beginning_qty' },
                    // { data: 'beginning_price' },
                    // { data: 'beginning_cogs' },
                    { data: 'qty_in' },
                    // { data: 'price_in' },
                    // { data: 'cogs_in' },
                    { data: 'qty_out' },
                    // { data: 'price_out' },
                    // { data: 'cogs_out' },
                    { data: 'total_qty' },
                    // { data: 'total_price' },
                    // { data: 'total_cogs' }
                ]
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );

                const todayDate = new Date().toISOString().split('T')[0];
                var warehouse = $('#warehouse').val();
                $.ajax({
                    url: '{{ route("report.inventory.search") }}',
                    method: 'POST',
                    data: {
                        date_from: todayDate,
                        date_to: todayDate,
                        warehouse: warehouse,
                    },
                    success: function(response) {
                        console.log(response);

                        // Assuming response contains the new journal data
                        table.clear().rows.add(response).draw();
                    },
                    error: function(xhr) {
                        console.log(xhr);

                        alert('An error occurred while fetching data.');
                    }
                });

                $('#btn-search').on('click', function() {
                    var dateFrom = $('#date_from').val();
                    var dateTo = $('#date_to').val();
                    var warehouse = $('#warehouse').val();
                    if(!dateTo || !dateFrom){
                        Swal.fire({
                            title: 'Error!',
                            text: "Date From and Date To must be filled",
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    $.ajax({
                        url: '{{ route("report.inventory.search") }}',
                        method: 'POST',
                        data: {
                            date_from: dateFrom,
                            date_to: dateTo,
                            warehouse: warehouse,
                        },
                        success: function(response) {
                            // Assuming response contains the new journal data
                            table.clear().rows.add(response).draw();
                        },
                        error: function(xhr) {
                            console.log(xhr);

                            alert('An error occurred while fetching data.');
                        }
                    });
                });
    });
</script>
@endsection
