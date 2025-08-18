@extends('layouts.master')

@section('title', 'Master Currency')
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
<x-page-title title="Master" pagetitle="Currency" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Currency</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>Currency Code</th>
								<th>Currency Name</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($currencies as $curr)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory('{{ $curr->currency_code }}','{{$curr->currency_name}}')">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$curr->currency_code}}</td>
                                    <td>{{$curr->currency_name}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory('{{ $curr->currency_code }}','{{$curr->currency_name}}')"><i class="material-icons-outlined">edit</i></button>

                                        <form id="delete-form-{{ $curr->currency_code }}" action="{{ url('/TDS/master/currency/delete/' . $curr->currency_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $curr->currency_code }}')"><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>Currency Code</th>
								<th>Currency Name</th>
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
                  <h5 class="modal-title" id="exampleModalLabel">Currency Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="type-company-form" id="type-company-form" method="post" action="{{url('/TDS/master/currency/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Currency Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="currency_code" name="currency_code" class="form-control" placeholder="Currency Code" aria-label="Username" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Currency Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="currency_name" name="currency_name" class="form-control" placeholder="Currency Name" aria-label="Username" aria-describedby="basic-addon1" required>
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
            document.getElementById('currency_code').value = '';
            document.getElementById('currency_name').value = '';
            document.getElementById('exampleModalLabel').innerText = 'Currency Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('type-company-form').action = `/TDS/master/currency/insert`;
        }

        function editCategory(currency_code,currency_name) {
            document.getElementById('exampleModalLabel').innerText = 'Currency Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('currency_code').value = currency_code;
            document.getElementById('currency_name').value = currency_name;

            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('type-company-form').action = `/TDS/master/currency/edit/${currency_code}`;
        }
    </script>

    <script>

    </script>
@endsection
