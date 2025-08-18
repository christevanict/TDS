@extends('layouts.master')

@section('title', 'SALES PBR')

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
<x-page-title title="SALES PBR" pagetitle="SALES PBR" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">SALES PBR</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.pbr.create') }}" >
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Sales PBR</th>
                        <th>Tanggal Dokumen</th>
                        <th>Kode Pelanggan</th>
                        <th>Grup Pelanggan</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Action</th>
                        <th style="max-width: 200px;">Note</th>
                    </tr>
                    <tr class="filter-row">
                        <th></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nomor PBR" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Tanggal" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Kode Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Grup Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Total" data-sort="false"></th>
                        <th></th>
                        <th><input type="text" class="form-control" placeholder="Filter Note" data-sort="false"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pbrs as $pbr) <!-- Changed to cash out records variable -->
                    <tr class="clickable-row">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $pbr->pbr_number }}</td> <!-- Updated field to match cash out -->
                        <td>{{ \Carbon\Carbon::parse($pbr->document_date)->format('d M Y') }}</td> <!-- Updated field to match cash out -->
                        <td>{{$pbr->customer_code}}</td>
                        <td>{{ $customers->firstWhere(fn($customer) => $pbr->customers->group_customer === $customer->customer_code)?->customer_name ?? '' }}</td>
                        <td>{{$pbr->customers->customer_name}}</td>
                        <td>Rp {{number_format($pbr->total,0,'.',',')}}</td>
                        <td>
                            <a href="{{ route('transaction.pbr.edit', $pbr->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            <a target="_blank" href="{{ route('pbr.print', $pbr->id) }}" class="btn btn-secondary btn-edit"><i class="material-icons-outlined">print</i></a>

                        </td> <!-- Edit and Print buttons for each row -->
                        <td>{{$pbr->notes??''}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Nomor Sales PBR</th>
                        <th>Tanggal Dokumen</th>
                        <th>Kode Pelanggan</th>
                        <th>Grup Pelanggan</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Action</th>
                        <th style="max-width: 200px;">Note</th>
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
            orderCellsTop: true,
        });

        // Prevent clicks on filter inputs from triggering sorting
        $('#example thead tr.filter-row input').on('click', function(e) {
            e.stopPropagation();
        });

        // Add filtering functionality to input fields
        $('#example thead tr.filter-row input').on('keyup change', function() {
            var columnIndex = $(this).parent().index();
            table.column(columnIndex).search(this.value).draw();
        });

        // Restore filter inputs if page is reloaded with search params
        $('#example thead tr.filter-row input').each(function() {
            var columnIndex = $(this).parent().index();
            $(this).val(table.column(columnIndex).search());
        });
    });
</script>
@endsection
