@extends('layouts.master')

@section('title', 'COGS Method')
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
<x-page-title title="Master" pagetitle="COGS Method" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">COGS Method</h6>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
            Tambah Baru
        </button>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>COGS Method</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cogsMethods as $method)
                        <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editMethod('{{ $method->id }}', '{{ addslashes($method->cogs_method) }}')">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $method->cogs_method }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editMethod('{{ $method->id }}', '{{ addslashes($method->cogs_method) }}')"><i class="material-icons-outlined">edit</i></button>

                                <form id="delete-form-{{ $method->id }}" action="{{ route('master.cogs-method.destroy', $method->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(event, '{{ $method->id }}')"><i class="material-icons-outlined">delete</i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>COGS Method</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInput" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="legendForm">COGS Method Insert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="cogs-method-form" id="cogs-method-form" method="post" action="{{ route('master.cogs-method.store') }}">
                    @csrf
                    <label for="cogs_method" class="form-label">COGS Method</label>
                    <div class="input-group mb-3">
                        <input type="text" id="cogs_method" name="cogs_method" class="form-control" placeholder="COGS Method" required>
                    </div>

                    <button id="btn-action" name="btn-action" type="submit" class="btn btn-primary btn-md">Insert</button>
                    <button type="button" class="btn btn-danger" id="cancelButton" style="display:none;" data-bs-dismiss="modal" onclick="cancelEdit()">Cancel</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelEdit()">Close</button>
            </div>
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
            lengthChange: false,
            buttons: ['copy', 'excel', 'pdf', 'print']
        });

        table.buttons().container()
            .appendTo('#example_wrapper .col-md-6:eq(0)');
    });

    function confirmDelete(event, id) {
        event.stopPropagation();
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    function cancelEdit() {
        document.getElementById('cogs_method').value = '';
        document.getElementById('legendForm').innerText = 'COGS Method Insert';
        document.getElementById('cancelButton').style.display = 'none';
        document.getElementById('btn-action').innerText = 'Insert';
        document.getElementById('cogs-method-form').action = `{{ route('master.cogs-method.store') }}`;
        document.getElementById('cogs-method-form').method = 'POST';
    }

    function editMethod(id, cogs_method) {
        document.getElementById('cogs_method').value = cogs_method;
        document.getElementById('legendForm').innerText = 'COGS Method Update';
        document.getElementById('cancelButton').style.display = 'inline-block';
        document.getElementById('btn-action').innerText = 'Edit';
        document.getElementById('cogs-method-form').action = `{{ route('master.cogs-method.update', '') }}/${id}`;
        document.getElementById('cogs-method-form').method = 'POST'; // Change method to POST for updating
        // Add hidden input to indicate that this is an update action
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', '_method');
        hiddenInput.setAttribute('value', 'POST'); // Use 'POST' because we're handling it as a form submission
        document.getElementById('cogs-method-form').appendChild(hiddenInput);
    }
</script>
@endsection
