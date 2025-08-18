@extends('layouts.master')

@section('title', 'Master Location')
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
<x-page-title title="Master" pagetitle="Location" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Location</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>Location Code</th>
								<th>Location Name</th>
								<th>Company</th>
                                <th>Department</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($locations as $location)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($location->location_code ?? '') }}',
                                    '{{ addslashes($location->location_name ?? '') }}',
                                    '{{ addslashes($location->company_code ?? '') }}',
                                    '{{ addslashes($location->department_code ?? '') }}'
                                )">
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$location->location_code}}</td>
                                    <td>{{$location->location_name}}</td>
                                    <td>{{$location->company ? $location->company->company_name:''}}</td>
                                    <td>{{$location->department ? $location->department->department_name:''}}</td>
                                    <td>

                                    <button class="btn btn-sm btn-warning" onclick="editCategory(
                                    '{{ addslashes($location->location_code ?? '') }}',
                                    '{{ addslashes($location->location_name ?? '') }}',
                                    '{{ addslashes($location->company_code ?? '') }}',
                                    '{{ addslashes($location->department_code ?? '') }}'
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="delete-form-{{ $location->location_code}}" action="{{ url('/TDS/master/location/delete/' . $location->location_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $location->location_code }}')"><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>Location Code</th>
								<th>Location Name</th>
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
                  <h5 class="modal-title" id="legendForm">Location  Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="location-form" id="location-form" method="post" action="{{url('/TDS/master/location/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Location Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="location_code" name="location_code" class="form-control" placeholder="Location Code" aria-label="location_code" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Location Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="location_name" name="location_name" class="form-control" placeholder="Location Name " aria-label="location_name" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Department</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="department_code" name="department_code" required>
                            @foreach ($departments as $department)
                                <option value={{$department->department_code}}>{{$department->department_name.' ('.$department->department_code.')'}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label hidden for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_name.' ('.$company->company_code.')'}}</option>
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
            document.getElementById('location_code').value = '';
            document.getElementById('location_name').value = '';
            document.getElementById('department_code').value = '';
            document.getElementById('legendForm').innerText = 'Location Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('location-form').action = `/TDS/master/location/insert`;
        }


        function editCategory(location_code,location_name,company_code, department_code) {
            document.getElementById('location_code').value = location_code;
            document.getElementById('location_name').value = location_name;
            document.getElementById('company_code').value = company_code;
            document.getElementById('department_code').value = department_code;
            document.getElementById('legendForm').innerText = 'Location Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('location-form').action = `/TDS/master/location/edit/${location_code}`;
        }
    </script>
@endsection
