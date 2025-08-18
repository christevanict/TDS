@extends('layouts.master')

@section('title', 'Debt/Credit Note Information')

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
<x-page-title title="Sales Credit Notes" pagetitle=" Sales Credit Note {{__('Information')}}" />
<hr>

<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Sales Credit Note {{__('Information')}}</h6>

        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Credit Note Number</th>
                        <th>Credit Note Date</th>
                        <th>Invoice Number</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($debtCreditNotes as $note)
                    <tr class="clickable-row">
                        <td>{{ $note->sales_credit_note_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($note->sales_credit_note_date)->format('d M Y') }}</td>
                        <td>{{ $note->invoice_number }}</td>
                        <td>
                            <span class="badge
                                {{ $note->status == 'debit' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($note->status) }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('transaction.sales_debt_credit_notes.edit', $note->id) }}" class="btn btn-warning btn-edit">
                                <i class="material-icons-outlined">edit</i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Credit Note Number</th>
                        <th>Credit Note Date</th>
                        <th>Invoice Number</th>
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
