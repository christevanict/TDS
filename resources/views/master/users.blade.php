@extends('layouts.master')

@section('title', 'Master User')
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
<x-page-title title="Master" pagetitle="User" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">User</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput" @if (!in_array('create', $privileges)) disabled @endif>
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
								<th>No</th>
								<th>Username</th>
								<th>Role</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($users as $user)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory(
                                '{{$user->username }}',
                                '{{$user->fullname }}',
                                '{{$user->role}}',
                                '{{$user->email}}',
                                '{{$user->department}}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$user->username}}</td>
                                    <td>{{$user->roles->name}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                        '{{$user->username }}',
                                        '{{$user->role}}',
                                        '{{$user->email}}',
                                        '{{$user->department}}',
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="inactive-form-{{ $user->username }}" action="{{ url('/TDS/master/users/inactive/' . $user->username) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-info"
                                            @if ($user->username=='superadminICT')
                                                disabled
                                            @endif
                                            onclick="confirmDelete('{{ $user->username }}','{{$user->status==1?'inactive':'active'}}'
                                            )"><i class="material-icons-outlined">
                                                @if ($user->status==1)
                                                    toggle_off
                                                @else
                                                    toggle_on
                                                @endif
                                            </i>
                                            </button>
                                        </form>
                                        <form id="token-reset-form-{{ $user->username }}" action="{{ route('token.reset') }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <input type="hidden" name="username" value="{{$user->username}}">
                                            <button type="button" class="btn btn-sm btn-outline-info"
                                            @if ($user->username=='superadminICT'||!in_array('delete', $privileges))
                                                disabled
                                            @endif
                                            onclick="confirmReset('{{ $user->username }}','delete')"><i class="material-icons-outlined">refresh</i></button>
                                        </form>
                                        <form id="delete-form-{{ $user->username }}" action="{{ url('/TDS/master/users/delete/' . $user->username) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"
                                            @if ($user->username=='superadminICT'||!in_array('delete', $privileges))
                                                disabled
                                            @endif
                                            onclick="confirmDelete('{{ $user->username }}','delete')"><i class="material-icons-outlined">delete</i></button>
                                        </form>


                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
						<tfoot>
							<tr>
								<th>No</th>
								<th>Username</th>
								<th>Role</th>
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
                  <h5 class="modal-title" id="legendForm">New User Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="type-company-form" id="type-company-form" method="post" action="{{url('/TDS/master/users/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Username</label>
                    <div class="input-group mb-3">
                        <input type="text" id="username" name="username" class="form-control"  placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Fullname</label>
                    <div class="input-group mb-3">
                        <input type="text" id="fullname" name="fullname" class="form-control"  placeholder="Fullname" aria-label="fullname" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Password</label>
                    <div class="input-group mb-3">
                        <input type="text" id="password" name="password" class="form-control" placeholder="Password" aria-label="password" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Email</label>
                    <div class="input-group mb-3">
                        <input type="text" id="email" name="email" class="form-control" placeholder="Email for Reset Password" aria-label="password" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Department</label>
                    <select class="form-select" id="department" name="department">
                        @foreach ($depts as $dept)
                            <option value="{{$dept->department_code}}">{{$dept->department_name}}</option>
                        @endforeach
                    </select>
                    <br>
                    <label for="exampleInputEmail1" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        @foreach ($roles as $role)
                            <option value="{{$role->role_number}}">{{$role->name}}</option>
                        @endforeach
                    </select>
                    <br>
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
                paging:false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
		} );
	</script>
    <script>
        function confirmDelete(id,type) {
            Swal.fire({
                title: type=='inactive'?'Are you sure want deactivate this user':(type=='active')?'Are you sure want to activate this user':'Are you sure want delete this user',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0c6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: type == 'inactive' ? 'Yes, deactivate it' : (type =='active' ? 'Yes, activate it' : 'Yes, delete it!')
            }).then((result) => {
                if (result.isConfirmed) {
                    if(type=='inactive'||type=='active'){
                        document.getElementById('inactive-form-' + id).submit();
                    }else{
                        document.getElementById('delete-form-' + id).submit();
                    }
                }
            })
        }

        function confirmReset(id){
            Swal.fire({
                title: 'Apakah anda yakin akan reset login user '+id,
                text: "User tersebut akan bisa login dengan device lain setelah ini",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0c6efd',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya Reset'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('token-reset-form-' + id).submit();
                }
            })
        }
        let privileges = @json($privileges);
        function cancelEdit() {
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('email').value = '';
            document.getElementById('role').value = '';
            document.getElementById('department').value = '';
            document.getElementById('legendForm').innerText = 'User Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('type-company-form').action = `/TDS/master/users/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }

        function editCategory(username,fullname,role,email,department) {
            document.getElementById('legendForm').innerText = 'User Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('username').value = username;
            document.getElementById('fullname').value = fullname;
            document.getElementById('department').value = department;
            document.getElementById('password').placeholder = "Keep empty if you don't want to change the password";
            document.getElementById('password').required = false;
            document.getElementById('role').value = role;
            document.getElementById('email').value = email;

            if(username=='superadminICT'||!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }

            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('type-company-form').action = `/TDS/master/users/edit/${username}`;
        }
    </script>

    <script>

    </script>
@endsection
