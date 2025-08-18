@extends('layouts.master')

@section('title', 'Reimburse List')

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
<x-page-title title="Reimburse" pagetitle="Reimburse List" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Reimburse {{__('Information')}}</h6>
        <a class="btn btn-primary mb-3" href="{{ route('transaction.reimburse.create') }}">
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="reimburse" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Reimburse Number</th>
                        <th>{{__('Sales Order Number')}}</th>
                        <th>{{__('Document Date')}}</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($reimburses as $order)
                    <tr class="clickable-row">
                        <td>{{ $order->reimburse_number }}</td>
                        <td>{{ $order->contract_document_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->document_date)->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('transaction.reimburse.edit', $order->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            {{-- <a href="{{ route('transaction.reimburse.print', $order->id) }}" class="btn btn-secondary btn-print"><i class="material-icons-outlined">print</i></a> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Reimburse Number</th>
                        <th>{{__('Sales Order Number')}}</th>
                        <th>{{__('Document Date')}}</th>
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
        $('#reimburse').DataTable();
    });
</script>
@endsection
