@extends('layouts.master')

@section('title', 'Delete Log')
@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }
    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }
</style>
@endsection

@section('content')
<x-page-title title="Log" pagetitle="Delete Log" />
<hr>
<div class="card">
    <div class="card-body">
        <h3 class="mb-2 text-uppercase">Delete Log</h3>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered mt-3" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Type</th>
                        <th>Document Number</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Notes')}}</th>
                        <th>Department</th>
                        <th>Deleted By</th>
                        <th>Deleted Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($deleteLogs as $log)
                        <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $log->type }}</td>
                            <td>{{ $log->document_number ?? ''}}</td>
                            <td>{{ \Carbon\Carbon::parse($log->document_date)->format('d M Y')}}</td>
                            <td>{{ $log->delete_notes ?? ''}}</td>
                            <td>{{ $log->department->department_name ?? ''}}</td>
                            <td>{{ $log->deleted_by ?? ''}}</td>
                            <td>{{ $log->created_at ?? ''}}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Type</th>
                        <th>Document Number</th>
                        <th>{{__('Document Date')}}</th>
                        <th>{{__('Notes')}}</th>
                        <th>Department</th>
                        <th>Deleted By</th>
                        <th>Deleted Date</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')

    <script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            var table = $('#example').DataTable( {
                    lengthChange: false,
                    buttons: [ 'copy', 'excel', 'pdf', 'print']
                } );

                table.buttons().container()
                    .appendTo( '#example_wrapper .col-md-6:eq(0)' );
        } );
    </script>

@endsection
