@extends('layouts.master')

@section('title', 'Daftar Jurnal Umum')

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
<x-page-title title="{{__('General Journal')}}" pagetitle="{{__('General Journal')}} Information" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">{{__('General Journal')}} Information</h6>
        <a class="btn btn-primary mb-3 @if(!in_array('create', $privileges)) disabled @endif" href="{{ route('transaction.general_journal.create') }}">
            Tambah Baru
        </a>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor {{__('General Journal')}}</th>
                        <th>Tanggal {{__('General Journal')}}</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($generalJournals as $generals) <!-- Changed to cash out records variable -->
                    <tr class="clickable-row">
                        <td>{{$loop->iteration}}</td>
                        <td>{{ $generals->general_journal_number }}</td> <!-- Updated field to match cash out -->
                        <td>{{ \Carbon\Carbon::parse($generals->general_journal_date)->format('d M Y') }}</td> <!-- Updated field to match cash out -->
                        <td>Rp {{number_format($generals->nominal_debet,0,'.',',')}}</td>
                        <td>
                            <a href="{{ route('transaction.general_journal.edit', $generals->id) }}" class="btn btn-warning btn-edit"><i class="material-icons-outlined">edit</i></a>
                            <a href="{{ route('transaction.general_journal.print', $generals->id) }}" class="btn btn-primary btn-print @if(!in_array('print', $privileges)) disabled @endif" target="_blank"><i class="material-icons-outlined">print</i></a>

                        </td> <!-- Edit and Print buttons for each row -->
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Nomor {{__('General Journal')}}</th>
                        <th>Tanggal {{__('General Journal')}}</th>
                        <th>Total</th>
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
