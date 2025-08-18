@extends('layouts.master')

@section('title', __('Purchase Invoice') .' '.__('Information'))

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
<x-page-title title="{{__('Purchase Invoice')}}" pagetitle="{{__('Purchase Invoice')}} {{__('Information')}}" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('Purchase Invoice')}} {{__('Information')}}</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.purchase_invoice.create') }}" >
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Purchase Invoice Number')}}</th>
                        <th>{{__('Purchase Invoice Date')}}</th>
                    <th>{{__('Supplier')}}</th>
                        <th>Total</th>
                        <th>Sisa Hutang</th>
                        <th>Action</th> <!-- New column for actions -->
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchaseInvoices as $generals) <!-- Changed to cash out records variable -->
                    <tr class="clickable-row">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $generals->purchase_invoice_number }}</td> <!-- Updated field to match cash out -->
                        <td>{{ \Carbon\Carbon::parse($generals->document_date)->format('d M Y') }}</td> <!-- Updated field to match cash out -->
                        <td>{{$generals->suppliers->supplier_name}}</td>
                        <td>Rp {{number_format($generals->total,0,'.',',')}}</td>
                        <td>Rp {{ number_format($generals->debts->debt_balance, 0, ',', '.') }}</td>
                        <td>
                            <a href="{{ route('transaction.purchase_invoice.edit', $generals->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>

                        </td> <!-- Edit and Print buttons for each row -->
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>{{__('Purchase Invoice Number')}}</th>
                        <th>{{__('Purchase Invoice Date')}}</th>
                        <th>{{__('Supplier')}}</th>
                        <th>Total</th>
                        <th>Sisa Hutang</th>
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
        $('#example').DataTable();
    });
</script>
@endsection
