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
    <x-page-title title="Master" pagetitle="Coa" />
    <hr>
    <div class="card">
        <div class="card-body">
            <h6 class="mb-2 text-uppercase">Coa</h6>

            <!-- Tambah Baru and Import Database Buttons -->
            <button type="button" class="mb-3 btn btn-primary" data-bs-toggle="modal" onclick="cancelEdit()"
                data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                Tambah Baru
            </button>
            <a style="font-size:25px"> | </a>
            <button type="button" class="mb-3 btn btn-success d-none" data-bs-toggle="modal" data-bs-target="#modalImport">
                Import Data
            </button>

            <a href="{{ route('coa.export') }}" class="mb-3 btn btn-secondary d-none">Download Template <i class="fa fa-download"></i>
            </a>

            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>{{__('Account Number')}}</th>
                            <th>Account Name</th>
                            <th>Account Type</th>
                            <th>Normal Balance</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($coas as $coa)
                            <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"
                                onclick="editCategory(
                            '{{ addslashes($coa->account_number ?? '') }}',
                            '{{ addslashes($coa->account_name ?? '') }}',
                            '{{ addslashes($coa->account_type ?? '') }}',
                            '{{ addslashes($coa->normal_balance ?? '') }}',
                            '{{ addslashes($coa->company_code ?? '') }}'
                        )">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $coa->account_number }}</td>
                                <td>{{ $coa->account_name }}</td>
                                <td>{{ $coa->coasss->account_sub_type }}</td>
                                <td>{{ $coa->normal_balance }}</td>

                                <td>
                                    <button class="btn btn-sm btn-warning"
                                        onclick="editCategory(
                                    '{{ addslashes($coa->account_number ?? '') }}',
                                    '{{ addslashes($coa->account_name ?? '') }}',
                                    '{{ addslashes($coa->account_type ?? '') }}',
                                    '{{ addslashes($coa->normal_balance ?? '') }}',
                                    '{{ addslashes($coa->company_code ?? '') }}'
                                )"><i
                                            class="material-icons-outlined">edit</i></button>

                                    <form id="delete-form-{{ $coa->account_number }}"
                                        action="{{ url('/TDS/master/coa/delete/' . $coa->account_number) }}" method="POST"
                                        style="display:inline;">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete(event, '{{ $coa->account_number }}')" @if(!in_array('delete', $privileges)) disabled @endif><i
                                                class="material-icons-outlined">delete</i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>No</th>
                            <th>{{__('Account Number')}}</th>
                            <th>Account Name</th>
                            <th>Account Type</th>
                            <th>Normal Balance</th>
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
                    <h5 class="modal-title" id="legendForm">Coa Insert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="Coa-form" id="Coa-form" method="post" action="{{ url('/TDS/master/coa/insert') }}">
                        @csrf
                        <label for="exampleInputEmail1" class="form-label">Account Number</label>
                        <div class="mb-3 input-group">
                            <input type="text" id="account_number" name="account_number" class="form-control"
                                placeholder="Account Number" aria-label="account_number" aria-describedby="basic-addon1"
                                required>
                        </div>
                        <label for="exampleInputEmail1" class="form-label">Account Name</label>
                        <div class="mb-3 input-group">
                            <input type="text" id="account_name" name="account_name" class="form-control"
                                placeholder="Account Name " aria-label="account_name" aria-describedby="basic-addon1"
                                required>
                        </div>
                        <label for="exampleInputEmail1" class="form-label">Account Type</label>
                        <div class="mb-3 input-group">
                            <select class="form-select" id="account_type" name="account_type" required>
                                @foreach ($coass as $coa)
                                    <option value={{ $coa->id }}>
                                        {{ $coa->account_sub_type . ' (' . $coa->account_type . ') ' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label for="normal_balance" class="form-label">Normal Balance</label>
                        <select class="form-select" id="normal_balance" name="normal_balance" required>
                            <option value="Debet">Debet</option>
                            <option value="Credit">Credit</option>
                        </select>
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
    <div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importForm" method="post" action="{{ url('/TDS/master/coa/import') }}"
                        enctype="multipart/form-data">
                        @csrf
                        <label for="importFile" class="form-label">Choose file to import</label>
                        <input type="file" id="importFile" name="importFile" class="form-control"
                            accept=".csv, .xlsx" required>
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Import</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
        let privileges = @json($privileges);
        function cancelEdit() {
            document.getElementById('account_number').value = '';
            document.getElementById('account_name').value = '';
            document.getElementById('account_type').value = '';
            document.getElementById('normal_balance').value = '';
            document.getElementById('legendForm').innerText = 'Coa Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('Coa-form').action = `/TDS/master/coa/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }

        function editCategory(account_number, account_name, account_type, normal_balance, company_code) {
            document.getElementById('account_number').value = account_number;
            document.getElementById('account_name').value = account_name;
            document.getElementById('account_type').value = account_type;
            document.getElementById('normal_balance').value = normal_balance;
            document.getElementById('company_code').value = company_code;
            document.getElementById('legendForm').innerText = 'Coa Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }

            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('Coa-form').action = `/TDS/master/coa/edit/${account_number}`;
        }
    </script>
@endsection
