@extends('layouts.master')

@section('title', 'Sales Order '. __('Information'))

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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection

@section('content')
<x-page-title title="Sales Order" pagetitle="Invoice Summary" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Invoice Summary</h6>
        <form method="GET" action="{{ route('transaction.sales_order.summary') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control date-picker"
                            value="{{ request()->from_date }}">
                    </div>
                    <div class="col-md-4">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control date-picker"
                            value="{{ request()->to_date }}">
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Sales Order Number')}}</th>
                        <th>{{__('Customer Code')}}</th>
                        <th>Grup Pelanggan</th>
                        <th>{{__('Customer')}}</th>
                        <th>Tanggal Permintaan</th>
                        <th>Action</th> <!-- New column for actions -->
                    </tr>
                    <tr class="filter-row">
                        <th></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nomor SO" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Kode Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Grup Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Nama Pelanggan" data-sort="false"></th>
                        <th><input type="text" class="form-control" placeholder="Filter Tanggal Dokumen" data-sort="false"></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($salesOrders as $generals) <!-- Changed to cash out records variable -->
                    <tr class="clickable-row">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $generals->sales_order_number }}</td> <!-- Updated field to match cash out -->
                        <td>{{ $generals->customer_code }}</td>
                        <td>
                            {{ $customers->firstWhere(fn($customer) => $generals->customers->group_customer === $customer->customer_code)?->customer_name ?? '' }}
                        </td>
                        <td>{{ $generals->customers->customer_name }}</td>
                        <td>{{ \Carbon\Carbon::parse($generals->document_date)->format('d M Y') }}</td> <!-- Updated field to match cash out -->
                        <td>
                            <a href="{{ route('transaction.sales_order.print', $generals->id) }}" class="btn btn-secondary btn-edit @if(!in_array('print', $privileges)) disabled @endif"><i class="fa fa-print"></i></a>
                        </td> <!-- Edit and Print buttons for each row -->
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>{{__('Sales Order Number')}}</th>
                        <th>{{__('Customer Code')}}</th>
                        <th>Grup Pelanggan</th>
                        <th>{{__('Customer')}}</th>
                        <th>Tanggal Permintaan</th>
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
