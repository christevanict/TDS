@extends('layouts.master')

@section('title', 'Laporan Rekap Persediaan')

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
<x-page-title title="Persediaan" pagetitle="Laporan Rekap Persediaan" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan Rekap Persediaan</h6>
        <div class="row mb-3 ">
            <div class="col-md-3 d-none">
                <label for="date_from">Tanggal:</label>
                <div class="input-group">
                    <input type="date" id="date_from" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_from"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3 d-none">
                <label for="warehouse">Gudang:</label>
                <div class="input-group">
                    <select id="warehouse" class="form-control form-select">
                        @foreach ($warehouses as $wr)
                            <option value="{{ $wr->id }}">{{$wr->warehouse_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3 pt-4 d-none">
                <button type="button" class="btn btn-primary" id="btn-search">Search</button>
            </div>
        </div>
        <div class="table-responsive ">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Item</th>
                        <th>Nama Item</th>
                        <th>Lokasi</th>
                        <th>Total</th>
                        <th>Unit</th>
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
                    { data: 'item_code' },
                    { data: 'item_name' },
                    { data: 'warehouse_name' },
                    { data: 'total',className:'text-end' },
                    { data: 'unit' },
                ]
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
				const todayDate = new Date().toISOString().split('T')[0];
                var warehouse = $('#warehouse').val();
                Swal.fire({
                    title: 'Loading...',
                    text: 'Harap Tunggu.',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                $.ajax({
                    url: '{{ route("report.recap-inventory.search") }}',
                    method: 'POST',
                    data: {
                        date_from: todayDate,
                        warehouse: warehouse,
                    },
                    success: function(response) {
                        // Assuming response contains the new journal data
                        table.clear().rows.add(response).draw();
                        Swal.fire({
                            icon: 'success',
                            title: 'Sukses!',
                            text: 'Data berhasil ditampilkan!',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        console.log(xhr)
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Ada error',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                });

                $('#btn-search').on('click', function() {
                    var dateFrom = $('#date_from').val();
                    var warehouse = $('#warehouse').val();
                    if(!dateFrom){
                        Swal.fire({
                            title: 'Error!',
                            text: "Date From must be filled",
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    $.ajax({
                        url: '{{ route("report.recap-inventory.search") }}',
                        method: 'POST',
                        data: {
                            date_from: dateFrom,
                            warehouse: warehouse,
                        },
                        success: function(response) {
                            // Assuming response contains the new journal data
                            table.clear().rows.add(response).draw();
                        },
                        error: function(xhr) {
                            alert('An error occurred while fetching data.');
                        }
                    });
                });
    });
</script>
@endsection
