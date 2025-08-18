@extends('layouts.master')

@section('title', 'Daftar '. __('Good Receipt'))

@section('css')
<style>
    .btn-insert {
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<x-page-title title="Inbound" pagetitle="{{__('Good Receipt')}} List" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('Good Receipt')}} List</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{route('transaction.warehouse.good_receipt.create')}}">Tambah Baru</a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Good Receipt Number')}}</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Supplier')}}</th>
                        <th>{{__('Warehouse')}}</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($goodReceiptRecords as $goodReceipt)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $goodReceipt->good_receipt_number }}</td>
                        <td>{{ $goodReceipt->document_date }}</td>
                        <td>{{ $goodReceipt->supplier->supplier_name ?? '' }}</td>
                        <td>{{$goodReceipt->warehouse->warehouse_name??''}}</td>
                        <td>
                            <span class="badge
                                {{ $goodReceipt->status == 'Open' ? 'bg-secondary' : ($goodReceipt->status =='Partial' ? 'bg-info' : ($goodReceipt->status =='Closed' ? 'bg-success' : 'bg-danger')) }}">
                                {{ ucfirst($goodReceipt->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('transaction.warehouse.good_receipt.edit', $goodReceipt->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            {{-- <a href="{{ route('inbound.print', $goodReceipt->id) }}" class="btn btn-primary btn-print" target="_blank">Print Nota</a> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>{{__('Good Receipt Number')}}</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Supplier')}}</th>
                        <th>{{__('Warehouse')}}</th>
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
