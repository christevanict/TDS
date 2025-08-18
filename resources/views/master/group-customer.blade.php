@extends('layouts.master')

@section('title', 'Master '. __('Group Customer'))
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
<x-page-title title="Master" pagetitle="{{__('Group Customer')}}" />
		<hr>
		<div class="card">
            <div class="card-body">
                <h6 class="mb-2 text-uppercase">{{__('Group Customer')}}</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput"
                @if(!in_array('create', $privileges)) disabled @endif
                >
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-bordered table-hover" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>{{__('Code Group')}}</th>
								<th>{{__('Name Group')}}</th>
								{{-- <th>Detail Customer Name</th> --}}
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($groupCustomers as $group)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory('{{ $group->code_group }}','{{$group->name_group}}','{{$group->detail_customer_name}}','{{$group->company_code}}' )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$group->code_group}}</td>
                                    <td>{{$group->name_group}}</td>
                                    {{-- <td>{{$group->detail_customer_name}}</td> --}}
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory('{{ $group->code_group }}','{{$group->name_group}}','{{$group->detail_customer_name}}')"><i class="material-icons-outlined"

                                        >edit</i></button>

                                        <form id="delete-form-{{ $group->code_group }}" action="{{ url('/TDS/master/group-customer/delete/' . $group->code_group) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $group->code_group }}')"
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
								<th>{{__('Code Group')}}</th>
								<th>{{__('Name Group')}}</th>
								{{-- <th>Detail Customer Name</th> --}}
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
                  <h5 class="modal-title" id="legendForm">{{__('Group Customer')}} Input</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="group-customer-form" id="group-customer-form" method="post" action="{{url('/TDS/master/group-customer/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">{{__('Code Group')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="code_group" name="code_group" class="form-control" placeholder="{{__('Code Group')}}" aria-label="Username" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Name Group')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="name_group" name="name_group" class="form-control" placeholder="{{__('Name Group')}}" aria-label="Username" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Detail Customer Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="detail_customer_name" name="detail_customer_name" class="form-control" placeholder="Detail Customer Name" aria-label="Username" aria-describedby="basic-addon1" required>
                    </div>
                    <label hidden for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_name.' ('.$company->company_code.') '}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="btn-action" name="btn-action" type="submit" class="btn btn-primary btn-md"
                    @if(Auth::user()->role === 1||Auth::user()->role === 2)
                        disabled
                    @endif
                    >Insert</button>
                    <button type="button" class="btn btn-danger" id="cancelButton" data-bs-dismiss="modal" style="display:none;" onclick="cancelEdit()">Cancel</button>
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
        function confirmDelete(id) {
            console.log('ada'+id)
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
            document.getElementById('code_group').value = '';
            document.getElementById('name_group').value = '';
            document.getElementById('detail_customer_name').value = '';
            document.getElementById('legendForm').innerText = 'Group Customer Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('group-customer-form').action = `/TDS/master/group-customer/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }

        function editCategory(code_group,name_group,detail_customer_name,company_code) {
            document.getElementById('legendForm').innerText = 'Group Customer Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('code_group').value = code_group;
            document.getElementById('name_group').value = name_group;
            document.getElementById('company_code').value = company_code;
            document.getElementById('detail_customer_name').value = detail_customer_name;
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('cancelButton').style.display = 'inline';
            document.getElementById('group-customer-form').action = `/TDS/master/group-customer/edit/${code_group}`;

            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }
    </script>
@endsection
