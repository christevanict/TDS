@extends('layouts.master')

@section('title', 'Master Unit Barang')

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
<x-page-title title="Master" pagetitle="Unit Barang" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Unit Barang</h6>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput"
        @if(!in_array('create', $privileges)) disabled @endif
        >
            Tambah Baru
        </button>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Unit</th>
                        <th>Nama Unit</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemUnits as $itemUnit)
                        <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editItemUnit(
                            '{{ addslashes($itemUnit->unit ?? '') }}',
                            '{{ addslashes($itemUnit->unit_name ?? '') }}',
                            '{{ addslashes($itemUnit->company_code ?? '') }}',
                            @if (Auth::user()->role != 1 &&Auth::user()->role != 2)
                                true
                            @else
                                false
                            @endif
                        )">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $itemUnit->unit }}</td>
                            <td>{{ $itemUnit->unit_name }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning"
                                onclick="editItemUnit(
                                    '{{ addslashes($itemUnit->unit ?? '') }}',
                                    '{{ addslashes($itemUnit->unit_name ?? '') }}',
                                    '{{ addslashes($itemUnit->company_code ?? '') }}',
                                     @if (Auth::user()->role != 1 &&Auth::user()->role != 2)
                                        true
                                    @else
                                        false
                                    @endif
                                )"><i class="material-icons-outlined">edit</i></button>

                                <form id="delete-form-{{ $itemUnit->unit }}" action="{{ url('/TDS/master/item-unit/delete/' . $itemUnit->unit) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(event, '{{ $itemUnit->unit }}')"
                                        @if(!in_array('delete', $privileges)) disabled @endif
                                        ><i class="material-icons-outlined">delete</i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Kode Unit</th>
                        <th>Nama Unit</th>
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
                <legend id="legendForm">Input Unit Barang</legend>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="item-unit-form" id="item-unit-form" method="post" action="{{ url('master/item-unit/insert') }}">
                    @csrf


                    <label for="unit" class="form-label">Kode Unit</label>
                    <div class="input-group mb-3">
                        <input type="text" id="unit" name="unit" class="form-control" placeholder="Kode Unit" aria-label="unit" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="unit_name" class="form-label">Nama Unit</label>
                    <div class="input-group mb-3">
                        <input type="text" id="unit_name" name="unit_name" class="form-control" placeholder="Nama Unit" aria-label="unit_name" aria-describedby="basic-addon1" required>
                    </div>
                    <label hidden for="company_code" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_name.' ('.$company->company_code.')'}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="btn-action" name="btn-action" type="submit" class="btn btn-primary btn-md"
                    @if(Auth::user()->role == 1)
                            style="display: none"
                        @endif
                    >Insert</button>

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
        $('#example').DataTable();
      } );
</script>
<script>
    $(document).ready(function() {
        var table = $('#example2').DataTable( {
            lengthChange: false,
            buttons: [ 'copy', 'excel', 'pdf', 'print']
        } );

        table.buttons().container()
            .appendTo( '#example2_wrapper .col-md-6:eq(0)' );
    } );
</script>
<script>

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
        document.getElementById('unit').value = '';
        document.getElementById('unit_name').value = '';
        document.getElementById('legendForm').innerText = 'Unit Barang Insert';
        document.getElementById('cancelButton').style.display = 'none';
        document.getElementById('btn-action').innerText = 'Insert';
        document.getElementById('btn-action').disabled=false;
        document.getElementById('item-unit-form').action = `/TDS/master/item-unit/insert`;
        if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
    }

    function editItemUnit(unit, unit_name, company_code,update) {
        document.getElementById('btn-action').innerText = 'Edit';
        if(!update){
            document.getElementById('btn-action').style.display = 'none';
        }else{
            document.getElementById('btn-action').style.display = 'inline';
        }
        document.getElementById('btn-action').innerText = 'Edit';
        document.getElementById('unit').value = unit;
        document.getElementById('unit_name').value = unit_name;
        document.getElementById('company_code').value = company_code;
        document.getElementById('legendForm').innerText = 'Unit Barang Update';
        document.getElementById('cancelButton').style.display = 'inline-block';

        document.getElementById('item-unit-form').action = `/TDS/master/item-unit/edit/${unit}`;
        if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
    }
</script>
@endsection
