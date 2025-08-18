@extends('layouts.master')

@section('title', 'Laporan PPN Masukan')

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
<x-page-title title="Pajak" pagetitle="Laporan PPN Masukan" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Laporan PPN Masukan</h6>
        <div class="row mb-3 ">
            <div class="col-md-3 ">
                <label for="date_from">Dari:</label>
                <div class="input-group">
                    <input type="date" id="date_from" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_from"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3 ">
                <label for="date_to">Sampai:</label>
                <div class="input-group">
                    <input type="date" id="date_to" class="form-control date-picker filter-date me-0">
                    <button type="button" class="btn btn-danger clear-date" data-target="#date_to"><i class="material-icons-outlined">close</i></button>
                </div>
            </div>
            <div class="col-md-3 pt-4 ">
                <button type="button" class="btn btn-primary" id="btn-search">Cari</button>
            </div>
        </div>
        <div class="table-responsive ">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>No Faktur</th>
                        <th>Tanggal</th>
                        <th>Kode Pemasok</th>
                        <th>Pemasok</th>
                        <th>DPP</th>
                        <th>PPn</th>
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
                    { data: 'invoice_number' },
                    { data: 'date' },
                    { data: 'supplier_code' },
                    { data: 'supplier_name' },
                    { data: 'dpp',className:'text-end' },
                    { data: 'ppn',className:'text-end' },
                ]
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
				const todayDate = new Date().toISOString().split('T')[0];
                const firstDay = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
                $.ajax({
                    url: '{{ route("report.ppn_in.search") }}',
                    method: 'POST',
                    data: {
                        date_from: firstDay,
                        date_to: todayDate,
                    },
                    success: function(response) {
                        // Assuming response contains the new journal data
                        table.clear().rows.add(response).draw();
                    },
                    error: function(xhr) {
                        console.log(xhr)
                        alert('An error occurred while fetching data.');
                    }
                });

                $('#btn-search').on('click', function() {
                    var dateFrom = $('#date_from').val();
                    var dateTo = $('#date_to').val();
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
                        url: '{{ route("report.ppn_in.search") }}',
                        method: 'POST',
                        data: {
                            date_from: dateFrom,
                            date_to: dateTo,
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
