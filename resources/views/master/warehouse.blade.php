@extends('layouts.master')

@section('title', 'Master Warehouse')
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
<x-page-title title="Master" pagetitle="{{__('Warehouse')}}" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">{{__('Warehouse')}}</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>{{__('Warehouse Code')}}</th>
								<th>{{__('Warehouse Name')}}</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($warehouses as $warehouse)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($warehouse->id ?? '') }}',
                                    '{{ addslashes($warehouse->warehouse_code ?? '') }}',
                                    '{{ addslashes($warehouse->warehouse_name ?? '') }}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$warehouse->warehouse_code}}</td>
                                    <td>{{$warehouse->warehouse_name}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                    '{{ addslashes($warehouse->id ?? '') }}',
                                    '{{ addslashes($warehouse->warehouse_code ?? '') }}',
                                    '{{ addslashes($warehouse->warehouse_name ?? '') }}',
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="delete-form-{{ $warehouse->id}}" action="{{ url('/TDS/master/warehouse/delete/' . $warehouse->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $warehouse->id }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>{{__('Warehouse Code')}}</th>
								<th>{{__('Warehouse Name')}}</th>
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
                  <h5 class="modal-title" id="legendForm">{{__('Warehouse')}}  Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="warehouse-form" id="warehouse-form" method="post" action="{{url('/TDS/master/warehouse/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">{{__('Warehouse Code')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="warehouse_code" name="warehouse_code" class="form-control" placeholder="{{__('Warehouse Code')}} " aria-label="warehouse_code" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Warehouse Name')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="warehouse_name" name="warehouse_name" class="form-control" placeholder="{{__('Warehouse Name')}}" aria-label="warehouse_name" aria-describedby="basic-addon1" required>
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
            document.getElementById('warehouse_name').value = '';
            document.getElementById('legendForm').innerText = 'Warehouse Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('warehouse-form').action = `/TDS/master/warehouse/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }


        function editCategory(id,warehouse_code,warehouse_name) {
            document.getElementById('warehouse_code').value = warehouse_code;
            document.getElementById('warehouse_name').value = warehouse_name;
            document.getElementById('legendForm').innerText = 'Warehouse Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('warehouse-form').action = `/TDS/master/warehouse/edit/${id}`;
            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }
    </script>
@endsection
