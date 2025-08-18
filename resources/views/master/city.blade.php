@extends('layouts.master')

@section('title', 'Master City')
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
<x-page-title title="Master" pagetitle="City" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">City</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput" @if (!in_array('create', $privileges)) disabled @endif>
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
								<th>No</th>
                                <th>Code</th>
								<th>Name</th>
                                <th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($citys as $city)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory(
                                '{{$city->city_code }}',
                                '{{$city->scity_name}}',
                                '{{$city->is_active}}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$city->city_code}}</td>
                                    <td>{{$city->city_name}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                        '{{$city->city_code }}',
                                        '{{$city->city_name}}',
                                        '{{$city->is_active}}',
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="inactive-form-{{ $city->city_code }}" action="{{ url('/TDS/master/city/inactive/' . $city->city_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-info"
                                            onclick="confirmDelete('{{ $city->city_code }}','{{$city->is_active==1?'inactive':'active'}}'
                                            )"><i class="material-icons-outlined">
                                                @if ($city->is_active*1==1)
                                                    toggle_off
                                                @else
                                                    toggle_on
                                                @endif
                                            </i>
                                            </button>
                                        </form>
                                        <form id="delete-form-{{ $city->city_code }}" action="{{ url('/TDS/master/city/delete/' . $city->city_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('{{ $city->city_code }}','delete')"><i class="material-icons-outlined">delete</i></button>
                                        </form>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
						<tfoot>
							<tr>
								<th>No</th>
								<th>Code</th>
								<th>Name</th>
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
                    <form name="type-company-form" id="type-company-form" method="post" action="{{url('/TDS/master/city/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="city_code" name="city_code" class="form-control"  placeholder="Code" aria-label="city_code" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="city_name" name="city_name" class="form-control"  placeholder="Name" aria-label="city_name" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Active</label>
                    <div class="form-group">
                        <select class="form-select" id="is_active" name="is_active" required>
                            <option value=1>Aktif</option>
                            <option value=0>Tidak Aktif</option>
                        </select>
                    </div>
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
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
		} );
	</script>
    <script>
        function confirmDelete(id,type) {
            Swal.fire({
                title: type=='inactive'?'Are you sure want deactivate this City':(type=='active')?'Are you sure want to activate this City':'Are you sure want delete this City',
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
        let privileges = @json($privileges);
        function cancelEdit() {
            document.getElementById('city_code').value = '';
            document.getElementById('city_name').value = '';
            document.getElementById('is_active').value = 1;
            document.getElementById('legendForm').innerText = 'City Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('type-company-form').action = `/TDS/master/city/insert`;

            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }

        function editCategory(city_code,city_name,is_active) {
            document.getElementById('legendForm').innerText = 'City Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('city_code').value = city_code;
            document.getElementById('city_name').value = cityname;
            document.getElementById('is_active').value = is_active;

            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }

            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('type-company-form').action = `/TDS/master/city/edit/${city_code}`;
        }
    </script>

    <script>

    </script>
@endsection
