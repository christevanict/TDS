@extends('layouts.master')

@section('title', 'Master Depreciation')
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
<x-page-title title="Master" pagetitle="Depreciation" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Depreciation</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
								<th>Depreciation Code</th>
								<th>Depreciation Name</th>
								<th>Company</th>
                                <th>Department</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($depreciations as $depreciation)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($depreciation->depreciation_code ?? '') }}',
                                    '{{ addslashes($depreciation->depreciation_name ?? '') }}',
                                    '{{ addslashes($depreciation->company_code ?? '') }}',
                                    '{{ addslashes($depreciation->department_code ?? '') }}'
                                )">
                                    <td>{{$depreciation->depreciation_code}}</td>
                                    <td>{{$depreciation->depreciation_name}}</td>
                                    <td>{{$depreciation->company ? $depreciation->company->company_name:''}}</td>
                                    <td>{{$depreciation->department ? $depreciation->department->department_name:''}}</td>
                                    <td>

                                    <button class="btn btn-sm btn-warning" onclick="editCategory(
                                    '{{ addslashes($depreciation->depreciation_code ?? '') }}',
                                    '{{ addslashes($depreciation->depreciation_name ?? '') }}',
                                    '{{ addslashes($depreciation->company_code ?? '') }}',
                                    '{{ addslashes($depreciation->department_code ?? '') }}'
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="delete-form-{{ $depreciation->depreciation_code}}" action="{{ url('/TDS/master/depreciation/delete/' . $depreciation->depreciation_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $depreciation->depreciation_code }}')"><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
								<th>Depreciation Code</th>
								<th>Depreciation Name</th>
								<th>Company</th>
                                <th>Department</th>
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
                  <h5 class="modal-title" id="legendForm">Depreciation  Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="depreciation-form" id="depreciation-form" method="post" action="{{url('/TDS/master/depreciation/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Depreciation Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="depreciation_code" name="depreciation_code" class="form-control" placeholder="Depreciation Code" aria-label="depreciation_code" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Depreciation Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="depreciation_name" name="depreciation_name" class="form-control" placeholder="Depreciation Name " aria-label="depreciation_name" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="company_code" name="company_code">
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_code.' '.$company->company_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Department</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="department_code" name="department_code">
                            @foreach ($departments as $department)
                                <option value={{$department->department_code}}>{{$department->department_code.' '.$department->department_name}}</option>
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
			var table = $('#example').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
		} );
	</script>
    <script>
        function confirmDelete(event,id) {
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
            })
        }

        function cancelEdit() {
            document.getElementById('depreciation_code').value = '';
            document.getElementById('depreciation_name').value = '';
            document.getElementById('company_code').value = '';
            document.getElementById('department_code').value = '';
            document.getElementById('legendForm').innerText = 'Depreciation Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('depreciation-form').action = `/TDS/master/depreciation/insert`;
        }


        function editCategory(depreciation_code,depreciation_name,company_code, department_code) {
            document.getElementById('depreciation_code').value = depreciation_code;
            document.getElementById('depreciation_name').value = depreciation_name;
            document.getElementById('company_code').value = company_code;
            document.getElementById('department_code').value = department_code;
            document.getElementById('legendForm').innerText = 'Depreciation Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('depreciation-form').action = `/TDS/master/depreciation/edit/${depreciation_code}`;
        }
    </script>
@endsection
