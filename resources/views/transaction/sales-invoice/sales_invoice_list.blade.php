@extends('layouts.master')

@section('title', __('Sales Invoice').' '.__('Information'))

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
<x-page-title title="{{__('Sales Invoice')}}" pagetitle="{{__('Sales Invoice')}} {{__('Information')}}" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('Sales Invoice')}} {{__('Information')}}</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.sales_invoice.create') }}" >
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Sales Invoice Number')}}</th>
                        <th>{{__('Sales Invoice')}} Date</th>
                        <th>{{__('Customer Code')}}</th>
                        <th>{{__('Customer')}}</th>
                        <th>Total</th>
                        <th>Sisa Piutang</th>
                        <th>Action</th>
                        <th style="max-width: 200px;">Note</th>
                    </tr>
                    <tr class="filter-row">
                        <th></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nomor SI" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Tanggal Dokumen" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Kode Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nama Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nominal" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Piutang" data-sort="false"></th>
                        <th></th>
                        <th><input type="text" class="form-control" placeholder="Filter Note" data-sort="false"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesInvoices as $generals) <!-- Changed to cash out records variable -->
                    <tr class="clickable-row">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $generals->sales_invoice_number }}</td> <!-- Updated field to match cash out -->
                        <td>{{ \Carbon\Carbon::parse($generals->document_date)->format('d M Y') }}</td> <!-- Updated field to match cash out -->
                        <td>{{$generals->customer_code}}</td>
                        <td>{{$generals->customers->customer_name}}</td>
                        <td>Rp {{number_format($generals->total,0,'.',',')}}</td>
                        <td>Rp {{number_format($generals->receivables->debt_balance,0,'.',',')}}</td>
                        <td>
                            <a href="{{ route('transaction.sales_invoice.edit', $generals->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            <a target="_blank" href="{{ route('sales_invoice.print', $generals->id) }}" class="btn btn-secondary btn-edit"><i class="material-icons-outlined">print</i></a>

                        </td> <!-- Edit and Print buttons for each row -->
                        <td>{{$generals->notes}}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>{{__('Sales Invoice Number')}}</th>
                        <th>{{__('Sales Invoice')}} Date</th>
                        <th>{{__('Customer Code')}}</th>
                        <th>{{__('Customer')}}</th>
                        <th>Total</th>
                        <th>Sisa Piutang</th>
                        <th>Action</th>
                        <th>Note</th>
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
        // Initialize DataTable with custom column definitions
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
