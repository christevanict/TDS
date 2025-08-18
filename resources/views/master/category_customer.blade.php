@extends('layouts.master')

@section('title', 'Category Customer')
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
<x-page-title title="Master" pagetitle="Category Customer" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Category Customer</h6>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
            Tambah Baru
        </button>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Category Code</th>
                        <th>Category Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($categories as $category)
                        <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory('{{ $category->category_code }}', '{{ addslashes($category->category_name) }}', '{{ addslashes($category->company_code) }}')">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $category->category_code }}</td>
                            <td>{{ $category->category_name }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editCategory('{{ $category->category_code }}', '{{ addslashes($category->category_name) }}', '{{ addslashes($category->company_code) }}')"><i class="material-icons-outlined">edit</i></button>

                                <form id="delete-form-{{ $category->category_code }}" action="{{ route('master.category-customer.destroy', $category->category_code) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(event, '{{ $category->category_code }}')"><i class="material-icons-outlined">delete</i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Category Code</th>
                        <th>Category Name</th>
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
                <h5 class="modal-title" id="legendForm">Category Customer Insert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="category-customer-form" id="category-customer-form" method="post" action="{{ route('master.category-customer.store') }}">
                    @csrf
                    <label for="category_code" class="form-label">Category Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="category_code" name="category_code" class="form-control" placeholder="Category Code" required>
                    </div>

                    <label for="category_name" class="form-label">Category Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="category_name" name="category_name" class="form-control" placeholder="Category Name" required>
                    </div>

                    <label hidden for="company_code" class="form-label">Company Code</label>
                    <div class="input-group mb-3">
                        <select hidden id="company_code" name="company_code" class="form-control" required>
                            @foreach($companies as $company)
                                <option value="{{ $company->company_code }}">{{$company->company_name.' ('.$company->company_code.')'}}</option>
                            @endforeach
                        </select>
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

    function confirmDelete(event, category_code) {
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
                document.getElementById('delete-form-' + category_code).submit();
            }
        });
    }

    function cancelEdit() {
        document.getElementById('category_code').value = '';
        document.getElementById('category_name').value = '';
        document.getElementById('legendForm').innerText = 'Category Customer Insert';
        document.getElementById('cancelButton').style.display = 'none';
        document.getElementById('btn-action').innerText = 'Insert';
        document.getElementById('category-customer-form').action = `{{ route('master.category-customer.store') }}`;
        document.getElementById('category-customer-form').method = 'POST';
        // Remove any hidden input for update
        var hiddenInput = document.querySelector('input[name="_method"]');
        if (hiddenInput) {
            hiddenInput.remove();
        }
    }

    function editCategory(category_code, category_name, company_code) {
        document.getElementById('category_code').value = category_code;
        document.getElementById('category_name').value = category_name;
        document.getElementById('company_code').value = company_code; // Set selected company code
        document.getElementById('legendForm').innerText = 'Category Customer Update';
        document.getElementById('cancelButton').style.display = 'inline-block';
        document.getElementById('btn-action').innerText = 'Edit';
        document.getElementById('category-customer-form').action = `{{ route('master.category-customer.update', '') }}/${category_code}`;
        document.getElementById('category-customer-form').method = 'POST'; // Change method to POST for updating

        // Add hidden input to indicate that this is an update action
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', '_method');
        hiddenInput.setAttribute('value', 'POST'); // Use 'POST' because we're handling it as a form submission
        document.getElementById('category-customer-form').appendChild(hiddenInput);
    }
</script>
@endsection
