@extends('layouts.master')

@section('title', 'Daftar Kas Masuk')

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
<x-page-title title="{{__('Bank Cash In')}}" pagetitle="{{__('Bank Cash In')}} Information" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('Bank Cash In')}} Information</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.bank_cash_in.create') }}">
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>{{__('Bank Cash In Number')}}</th>
                        <th>Tanggal {{__('Bank Cash In')}}</th>
                        <th>Total</th>
                        <th>Action</th> <!-- New column for actions -->
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bankCashInRecords as $cashIn) <!-- Change to the correct variable name -->
                    <tr class="clickable-row">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $cashIn->bank_cash_in_number }}</td> <!-- Update to match the correct field -->
                        <td>{{ \Carbon\Carbon::parse($cashIn->bank_cash_in_date)->format('d M Y') }}</td> <!-- Update to match the correct field -->
                        <td>Rp {{number_format($cashIn->nominal,0,'.',',')}}</td>
                        <td>
                            <a href="{{ route('transaction.bank_cash_in.edit', $cashIn->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            <a href="{{ route('transaction.bank_cash_in.print', $cashIn->id) }}" class="btn btn-primary btn-print @if(!in_array('print', $privileges)) disabled @endif" target="_blank"><i class="material-icons-outlined">print</i></a>
                        </td> <!-- Edit and Print buttons for each row -->
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>{{__('Bank Cash In Number')}}</th>
                        <th>Tanggal {{__('Bank Cash In')}}</th>
                        <th>Total</th>
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
