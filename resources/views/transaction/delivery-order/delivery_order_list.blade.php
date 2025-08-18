@extends('layouts.master')

@section('title', 'Delivery Order List')

@section('css')
<style>
    .btn-insert {
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<x-page-title title="Inbound" pagetitle="Delivery Order List" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Delivery Order List</h6>
        <a class="btn btn-primary mb-3" href="{{route('transaction.warehouse.delivery_order.create')}}">Tambah Baru</a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Delivery Order Number</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Supplier')}}</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deliveryOrderRecords as $deliveryOrder)
                    <tr>
                        <td>{{ $deliveryOrder->delivery_order_number }}</td>
                        <td>{{ $deliveryOrder->document_date }}</td>
                        <td>{{ $deliveryOrder->customer->customer_name ?? '' }}</td>
                        <td>
                            <span class="badge
                                {{ $deliveryOrder->status == 'Open' ? 'bg-secondary' : ($deliveryOrder->status =='Partial' ? 'bg-info' : ($deliveryOrder->status =='Closed' ? 'bg-success' : 'bg-danger')) }}">
                                {{ ucfirst($deliveryOrder->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('transaction.warehouse.delivery_order.edit', $deliveryOrder->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            {{-- <a href="{{ route('inbound.print', $deliveryOrder->id) }}" class="btn btn-primary btn-print" target="_blank">Print Nota</a> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Delivery Order Number</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Supplier')}}</th>
                        <th>Status</th>
                        <th>Action</th>
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
        $('#example').DataTable();
    });
</script>
@endsection
