@extends('layouts.master')

@section('title', 'Master Coa')
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection

@section('content')
    <x-page-title title="Master" pagetitle="Coa Type" />
    <hr>
    <div class="card">
        <div class="card-body">
            <h6 class="mb-2 text-uppercase">Coa Type</h6>

            <!-- Tambah Baru and Import Database Buttons -->
            <button type="button" class="mb-3 btn btn-primary" data-bs-toggle="modal" onclick="cancelEdit()"
                data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                Tambah Baru
            </button>

            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Account Sub Type</th>
                            <th>Account Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coass as $coa)
                            <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"
                                onclick="editCategory(
                                '{{ addslashes($coa->id ?? '') }}',
                            '{{ addslashes($coa->account_sub_type ?? '') }}',
                            '{{ addslashes($coa->account_type ?? '') }}',
                            '{{ addslashes($coa->company_code ?? '') }}'
                        )">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $coa->account_sub_type }}</td>
                                <td>{{ $coa->account_type }}</td>

                                <td>
                                    <button class="btn btn-sm btn-warning"
                                        onclick="editCategory(
                                        '{{ addslashes($coa->id ?? '') }}',
                                    '{{ addslashes($coa->account_sub_type ?? '') }}',
                                    '{{ addslashes($coa->account_type ?? '') }}',
                                    '{{ addslashes($coa->company_code ?? '') }}'
                                )"><i
                                            class="material-icons-outlined">edit</i></button>

                                    <form id="delete-form-{{ $coa->id }}"
                                        action="{{ url('/TDS/master/coa-type/delete/' . $coa->id) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete(event, '{{ $coa->id }}')" @if(!in_array('delete', $privileges)) disabled @endif><i
                                                class="material-icons-outlined">delete</i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>No</th>

                            <th>Account  Sub Type</th>
                            <th>Account Type</th>

                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalInput" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="legendForm">Coa Type Insert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="Coa-form" id="Coa-form" method="post" action="{{ url('/TDS/master/coa-type/insert') }}">
                        @csrf
                        {{-- <label for="exampleInputEmail1" class="form-label">Account Number</label>
                        <div class="mb-3 input-group">
                            <input type="text" id="account_number" name="account_number" class="form-control"
                                placeholder="Account Number" aria-label="account_number" aria-describedby="basic-addon1"
                                required>
                        </div> --}}
                        <label for="exampleInputEmail1" class="form-label">Account Sub Type</label>
                        <div class="mb-3 input-group">
                            <input type="text" id="account_sub_type" name="account_sub_type" class="form-control"
                                placeholder="Account Sub Type " aria-label="account_sub_type" aria-describedby="basic-addon1"
                                required>
                        </div>
                        <label for="exampleInputEmail1" class="form-label">Account Type</label>
                        <div class="mb-3 input-group">
                            <select class="form-select" id="account_type" name="account_type" required>
                                <option value="Asset">Asset</option>
                                <option value="Payable">Payable</option>
                                <option value="Equity" >Equity</option>
                                <option value="Sales">Sales</option>
                                <option value="COGS">COGS</option>
                                <option value="Expense">Expense</option>
                                <option value="Other Revenue">Other Revenue</option>
                                <option value="Other Expense">Other Expense</option>
                            </select>
                        </div>
                        <label hidden for="company" class="form-label">Company</label>
                        <div class="mb-3 input-group">
                            <select hidden class="form-select" id="company_code" name="company_code" required>
                                @foreach ($companies as $company)
                                    <option value={{ $company->company_code }}>
                                        {{ $company->company_name . ' (' . $company->company_code . ') ' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button id="btn-action" name="btn-action" type="submit"
                            class="btn btn-primary btn-md">Insert</button>
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

    <!-- Import Modal -->

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
        let privileges = @json($privileges);
        function cancelEdit() {
            document.getElementById('account_sub_type').value = '';
            document.getElementById('account_type').value = '';
            document.getElementById('legendForm').innerText = 'Coa Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('Coa-form').action = `/TDS/master/coa-type/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }

        function editCategory(id, account_sub_type, account_type, company_code) {
            document.getElementById('account_sub_type').value = account_sub_type;
            document.getElementById('account_type').value = account_type;
            document.getElementById('company_code').value = company_code;
            document.getElementById('legendForm').innerText = 'Coa Update';
            document.getElementById('cancelButton').style.display = 'inline-block';

            document.getElementById('btn-action').innerText = 'Edit';

            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
            document.getElementById('Coa-form').action = `/TDS/master/coa-type/edit/${id}`;
        }
    </script>
@endsection
