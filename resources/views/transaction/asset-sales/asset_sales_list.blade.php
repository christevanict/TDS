
@extends('layouts.master')

@section('title', 'Daftar Penjualan Aset')

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
        margin-right: 10px;
    }
</style>
@endsection

@section('content')
<x-page-title title="Penjualan Aset" pagetitle="Informasi Penjualan Aset" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Informasi Penjualan Aset</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('asset-sales.create') }}">
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="assetSaleTable" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Penjualan</th>
                        <th>Tanggal Dokumen</th>
                        <th>Nomor Aset</th>
                        <th>Subtotal</th>
                        <th>Pajak</th>
                        <th>Nominal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assetSales as $sale)
                    <tr class="clickable-row">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sale->asset_sales_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale->document_date)->format('d M Y') }}</td>
                        <td>{{ $sale->assetDetail->asset_name ?? '' }} ({{ $sale->asset_number }})</td>
                        <td>Rp {{ number_format($sale->subtotal, 0, '.', ',') }}</td>
                        <td>Rp {{ number_format($sale->add_tax, 0, '.', ',') }}</td>
                        <td>Rp {{ number_format($sale->nominal, 0, '.', ',') }}</td>
                        <td>
                            <a href="{{ route('asset-sales.edit', $sale->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            {{-- <a href="{{ route('asset-sales.print', $sale->id) }}" class="btn btn-primary btn-print @if(!in_array('print', $privileges)) disabled @endif" target="_blank"><i class="material-icons-outlined">print</i></a> --}}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Nomor Penjualan</th>
                        <th>Tanggal Dokumen</th>
                        <th>Nomor Aset</th>
                        <th>Subtotal</th>
                        <th>Pajak</th>
                        <th>Nominal</th>
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
        cancelButtonText: 'OK'
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
        $('#assetSaleTable').DataTable();
    });
</script>
@endsection
