@extends('layouts.master')

@section('title', 'Daftar Pelunasan Hutang')

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
<x-page-title title="{{__('Payable Payment')}}" pagetitle="{{__('Payable Payment')}}" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('Payable Payment')}}</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.payable_payment.create') }}">
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Pelunasan Hutang</th>
                        <th>Tanggal Pelunasan Hutang</th>
                        <th>Total Pembayaran</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pays as $pay) <!-- Changed to cash out records variable -->
                    <tr class="clickable-row">
                        <td>{{ $loop->iteration }}</td> <!-- Updated field to match cash out -->
                        <td>{{ $pay->payable_payment_number }}</td> <!-- Updated field to match cash out -->
                        <td>{{ \Carbon\Carbon::parse($pay->payable_payment_date)->format('d M Y') }}</td> <!-- Updated field to match cash out -->
                        <td>Rp {{ number_format($pay->total_debt,0,'.',',')}}</td> <!-- Updated field to match cash out -->
                        <td>
                            <a href="{{ route('transaction.payable_payment.edit', $pay->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">visibility</i></a>
                            <a href="{{ route('transaction.payable_payment.print', $pay->id) }}" class="btn btn-secondary btn-edit"><i class="material-icons-outlined">print</i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Nomor Pelunasan Hutang</th>
                        <th>Tanggal Pelunasan Hutang</th>
                        <th>Total Pembayaran</th>
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
