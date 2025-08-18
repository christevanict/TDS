@extends('layouts.master')

@section('title', 'Master Promo')
@section('css')
    <style>
        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover,
        .clickable-row:focus {
            background-color: #f1f1f1;
        }
    </style>
@endsection

@section('content')
    <x-page-title title="Master" pagetitle="Promo" />
    <hr>
    <div class="card">
        <div class="card-body">
            <h6 class="mb-2 text-uppercase">Promo</h6>
            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()"
                data-bs-target="#modalInput">
                Tambah Baru
            </button>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Promo Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Item Limit</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($promos as $promo)
                            <tr class="clickable-row" data-bs-toggle="modal" data-bs-target="#modalInput"
                                onclick="editPromo(
                                    '{{ $promo->id }}',
                                    '{{ $promo->name }}',
                                    '{{ $promo->promo_type }}',
                                    '{{ $promo->value }}',
                                    '{{ $promo->item_limit }}',
                                    '{{ $promo->start_date }}',
                                    '{{ $promo->end_date }}',
                                    '{{ $promo->status }}'
                                )">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $promo->name }}</td>
                                <td>{{ $promo->promo_type }}</td>
                                <td>{{ $promo->value }}</td>
                                <td>{{ $promo->item_limit }}</td>
                                <td>{{ $promo->start_date }}</td>
                                <td>{{ $promo->end_date }}</td>
                                <td>{{ ucfirst($promo->status) }}</td>
                                <td>
                                    <form id="delete-form-{{ $promo->id }}" method="POST"
                                        action="{{ route('promo.destroy', $promo->id) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete(event, '{{ $promo->id }}')"><i
                                                class="material-icons-outlined">delete</i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>No</th>
                            <th>Promo Name</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Item Limit</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalInput" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="legendForm">Promo Insert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="promo-form" method="POST">
                        @csrf
                        <input type="hidden" id="_method" name="_method" value="POST">
                        <label for="name" class="form-label">Promo Name</label>
                        <div class="input-group mb-3">
                            <input type="text" id="name" name="name" class="form-control" placeholder="Promo Name" required>
                        </div>
                        <label for="promo_type" class="form-label">Type</label>
                        <div class="input-group mb-3">
                            <select id="promo_type" name="promo_type" class="form-select" required>
                                <option value="percentage">Percentage</option>
                                <option value="nominal">Nominal</option>
                            </select>
                        </div>
                        <label for="value" class="form-label">Value</label>
                        <div class="input-group mb-3">
                            <input type="number" id="value" name="value" class="form-control" placeholder="Value" required>
                        </div>
                        <label for="item_limit" class="form-label">Item Limit</label>
                        <div class="input-group mb-3">
                            <input type="number" id="item_limit" name="item_limit" class="form-control"
                                placeholder="Item Limit">
                        </div>
                        <label for="start_date" class="form-label">Start Date</label>
                        <div class="input-group mb-3">
                            <input type="date" id="start_date" name="start_date" class="form-control date-picker" required>
                        </div>
                        <label for="end_date" class="form-label">End Date</label>
                        <div class="input-group mb-3">
                            <input type="date" id="end_date" name="end_date" class="form-control date-picker" required>
                        </div>
                        <label for="status" class="form-label">Status</label>
                        <div class="input-group mb-3">
                            <select id="status" name="status" class="form-select" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <button id="btn-action" type="submit" class="btn btn-primary btn-md">Insert</button>
                        <button type="button" class="btn btn-danger" id="cancelButton" style="display:none;"
                            data-bs-dismiss="modal" onclick="cancelEdit()">Cancel</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                        onclick="cancelEdit()">Close</button>
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
            $('#example').DataTable({
                lengthChange: false,
            });
        });

        function confirmDelete(event, id) {
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
            document.getElementById('promo-form').reset();
            document.getElementById('_method').value = 'POST';
            document.getElementById('promo-form').action = '{{ route('promo.store') }}';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('cancelButton').style.display = 'none';
        }

        function editPromo(id, name, promo_type, value, item_limit, start_date, end_date, status) {
            document.getElementById('name').value = name;
            document.getElementById('promo_type').value = promo_type;
            document.getElementById('value').value = value;
            document.getElementById('item_limit').value = item_limit;
            document.getElementById('start_date').value = start_date;
            document.getElementById('end_date').value = end_date;
            document.getElementById('status').value = status;
            document.getElementById('_method').value = 'POST';
            document.getElementById('promo-form').action = '{{ url('/promo/edit') }}/' + id;
            document.getElementById('btn-action').innerText = 'Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
        }
    </script>
@endsection
