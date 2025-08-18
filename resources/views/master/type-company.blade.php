@extends('layouts.master')

@section('title', 'Master Type Company')
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
<x-page-title title="Master" pagetitle="Type Company" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Type Company</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
								<th>No</th>
								<th>Type Company</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($typeCompany as $type)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory('{{ $type->id }}','{{$type->type_company}}')">
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$type->type_company}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory('{{ $type->id }}','{{$type->type_company}}')"><i class="material-icons-outlined">edit</i></button>

                                        <form id="delete-form-{{ $type->id }}" action="{{ url('/TDS/master/type-company/delete/' . $type->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $type->id }}')"><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
								<th>No</th>
								<th>Type Company</th>
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
                  <h5 class="modal-title" id="legendForm">Type Company Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="type-company-form" id="type-company-form" method="post" action="{{url('/TDS/master/type-company/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Type Company</label>
                    <div class="input-group mb-3">
                        <input type="text" id="type_company" name="type_company" class="form-control" placeholder="Type Company" aria-label="Username" aria-describedby="basic-addon1" value='' required>
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

        function cancelEdit() {
            document.getElementById('legendForm').innerText = 'Type Company Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('type-company-form').action = `/TDS/master/type-company/insert`;
        }

        function editCategory(id,type_company) {
            console.log(type_company);
            document.getElementById('legendForm').innerText = 'Type Company Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('type_company').value = type_company;

            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('type-company-form').action = `/TDS/master/type-company/edit/${id}`;
        }
    </script>

    <script>

    </script>
@endsection
