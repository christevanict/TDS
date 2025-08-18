@extends('layouts.master')

@section('title', 'Master '. __('Department'))
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
<x-page-title title="Master" pagetitle="{{__('Department')}}" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">{{__('Department')}}</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>{{__('Department Code')}}</th>
								<th>{{__('Department Name')}}</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($departments as $dept)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($dept->id ?? '') }}',
                                    '{{ addslashes($dept->department_name ?? '') }}',
                                    '{{ addslashes($dept->address ?? '') }}',
                                    '{{ addslashes($dept->phone ?? '') }}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$dept->department_code}}</td>
                                    <td>{{$dept->department_name}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                        '{{ addslashes($dept->id ?? '') }}',
                                    '{{ addslashes($dept->department_name ?? '') }}',
                                    '{{ addslashes($dept->address ?? '') }}',
                                    '{{ addslashes($dept->phone ?? '') }}',
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="delete-form-{{ $dept->id}}" action="{{ url('/TDS/master/department/delete/' . $dept->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $dept->id }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>{{__('Department Code')}}</th>
								<th>{{__('Department Name')}}</th>
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
                  <h5 class="modal-title" id="legendForm">{{__('Department ')}} Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="department-form" id="department-form" method="post" action="{{url('/TDS/master/department/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">{{__('Department Name')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="department_name" name="department_name" class="form-control" placeholder="{{__('Department Name')}} " aria-label="department_name" aria-describedby="basic-addon1" required>
                    </div>

                    <label for="exampleInputEmail1" class="form-label">{{__('Address')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="address" name="address" class="form-control" placeholder="Address" aria-label="address" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Phone Number')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="phone" name="phone" class="form-control" placeholder="{{__('Phone Number')}}" aria-label="phone" aria-describedby="basic-addon1" required>
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
        let privileges = @json($privileges);
        function cancelEdit() {
            document.getElementById('department_name').value = '';
            document.getElementById('legendForm').innerText = 'Department Insert';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('department-form').action = `/TDS/master/department/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }


        function editCategory(id,department_name,address,phone) {
            document.getElementById('department_name').value = department_name;
            document.getElementById('address').value = address;
            document.getElementById('phone').value = phone;
            document.getElementById('legendForm').innerText = 'Department Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('department-form').action = `/TDS/master/department/edit/${id}`;
            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }
    </script>
@endsection
