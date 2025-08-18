@extends('layouts.master')

@section('title', __('Sales Order') .' '. __('Information'))

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
<x-page-title title="{{__('Sales Order')}}" pagetitle="{{__('Sales Order')}} {{__('Information')}}" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('Sales Order')}} {{__('Information')}}</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.sales_order.create') }}" >
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Sales Order Number')}}</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Customer Code')}}</th>
                        <th>{{__('Customer')}}</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th> <!-- New column for actions -->
                    </tr>
                    <tr class="filter-row">
                        <th></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nomor SO" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Tanggal Dokumen" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Kode Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nama Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nominal" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Status" data-sort="false"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrders as $generals) <!-- Changed to cash out records variable -->
                    <tr class="clickable-row">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $generals->sales_order_number }}</td> <!-- Updated field to match cash out -->
                        <td>{{ \Carbon\Carbon::parse($generals->document_date)->format('d M Y') }}</td> <!-- Updated field to match cash out -->
                        <td>{{$generals->customer_code}}</td>
                        <td>{{$generals->customers->customer_name}}</td>
                        <td>Rp {{number_format($generals->total,0,'.',',')}}</td>
                        <td>
                            <span class="badge
                                {{ $generals->status == 'Open' ? 'bg-secondary' : ($generals->status =='Partial' ? 'bg-info' : ($generals->status =='Closed' ? 'bg-success' : 'bg-danger')) }}">
                                {{ ucfirst($generals->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('transaction.sales_order.edit', $generals->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>

                        </td> <!-- Edit and Print buttons for each row -->
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>{{__('Sales Order Number')}}</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Customer Code')}}</th>
                        <th>{{__('Customer')}}</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Action</th> <!-- New column for actions -->
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
