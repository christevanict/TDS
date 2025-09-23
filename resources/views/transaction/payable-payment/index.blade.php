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

    .date-filter {
        margin-bottom: 20px;
    }
</style>
@endsection

@section('content')
<x-page-title title="{{__('Payable Payment')}}" pagetitle="{{__('Payable Payment')}}" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('Payable Payment')}}</h6>

        <!-- Date Filter Form -->
        <div class="date-filter">
            <form id="dateFilterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="dateFrom" class="form-label">Date From</label>
                    <input type="date" id="dateFrom" class="form-control date-picker">
                </div>
                <div class="col-md-3">
                    <label for="dateTo" class="form-label">Date To</label>
                    <input type="date" id="dateTo" class="form-control date-picker">
                </div>
                <div class="col-md-3 align-self-end">
                    <button type="button" class="btn btn-primary" onclick="filterTable()">Filter</button>
                    <button type="button" class="btn btn-secondary" onclick="resetFilter()">Reset</button>
                </div>
            </form>
        </div>

        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.payable_payment.create') }}">
            Tambah Baru
        </a>
        @if(Auth::user()->username=='superadminICT')
        <button type="button" class="mb-3 btn btn-success d-none" data-bs-toggle="modal" data-bs-target="#modalImport">
            Import Data
        </button>
        @endif
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Pelunasan Hutang</th>
                        <th>Tanggal Pelunasan Hutang</th>
                        <th>Kode Supplier</th>
                        <th>Nama Supplier</th>
                        <th>Total Pembayaran</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pays as $pay)
                    <tr class="clickable-row">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $pay->payable_payment_number }}</td>
                        <td data-order="{{ \Carbon\Carbon::parse($pay->payable_payment_date)->format('Y-m-d') }}">{{ \Carbon\Carbon::parse($pay->payable_payment_date)->format('d M Y') }}</td>
                        <td>{{$pay->supplier_code}}</td>
                        <td>{{$pay->supplier->supplier_name}}</td>
                        <td>Rp {{ number_format($pay->total_debt,0,'.',',') }}</td>
                        <td>
                            <a href="{{ route('transaction.payable_payment.edit', $pay->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">visibility</i></a>
                            <a target="_BLANK" href="{{ route('transaction.payable_payment.print', $pay->id) }}" class="btn btn-secondary btn-edit"><i class="material-icons-outlined">print</i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Nomor Pelunasan Hutang</th>
                        <th>Tanggal Pelunasan Hutang</th>
                        <th>Kode Supplier</th>
                        <th>Nama Supplier</th>
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
        var table = $('#example').DataTable({
            // Optional: Configure DataTables options here
            // "order": [[2, "desc"]] // Default sort by date column
        });

        // Custom filter function for date range
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var dateFrom = $('#dateFrom').val();
                var dateTo = $('#dateTo').val();
                var date = data[2]; // Date column (index 2, Tanggal Pelunasan Hutang)

                if (!dateFrom && !dateTo) {
                    return true; // No filter applied
                }

                var dateValue = new Date(date); // Assuming date is in Y-m-d format from data-order
                var from = dateFrom ? new Date(dateFrom) : null;
                var to = dateTo ? new Date(dateTo) : null;

                if (from) {
                    from.setHours(0, 0, 0, 0); // Set to end of day
                }
                if (to) {
                    to.setHours(23, 59, 59, 999); // Set to end of day
                }

                if (from && to) {
                    return dateValue >= from && dateValue <= to;
                } else if (from) {
                    return dateValue >= from;
                } else if (to) {
                    return dateValue <= to;
                }
                return true;
            }
        );
    });

    // Filter table on button click
    function filterTable() {
        $('#example').DataTable().draw();
    }

    // Reset filter and clear inputs
    function resetFilter() {
        $('#dateFrom').val('');
        $('#dateTo').val('');
        $('#example').DataTable().draw();
    }
</script>
@endsection
