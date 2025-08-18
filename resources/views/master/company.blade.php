@extends('layouts.master')

@section('title', 'Master Company')
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
<x-page-title title="Master" pagetitle="Company" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Company</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>Company Code</th>
								<th>Company Name</th>
								<th>Type Company</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($companies as $company)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($company->company_code ?? '') }}',
                                    '{{ addslashes($company->company_name ?? '') }}',
                                    '{{ addslashes($company->address ?? '') }}',
                                    '{{ addslashes($company->phone_number ?? '') }}',
                                    '{{ addslashes($company->npwp ?? '') }}',
                                    '{{ addslashes($company->pkp ?? '') }}',
                                    '{{ addslashes($company->final_tax ?? '') }}',
                                    '{{ addslashes($company->type_company ?? '') }}'
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$company->company_code}}</td>
                                    <td>{{$company->company_name}}</td>
                                    <td>{{$company->type_company}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                            '{{ addslashes($company->company_code ?? '') }}',
                                            '{{ addslashes($company->company_name ?? '') }}',
                                            '{{ addslashes($company->address ?? '') }}',
                                            '{{ addslashes($company->phone_number ?? '') }}',
                                            '{{ addslashes($company->npwp ?? '') }}',
                                            '{{ addslashes($company->pkp ?? '') }}',
                                            '{{ addslashes($company->final_tax ?? '') }}',
                                            '{{ addslashes($company->type_company ?? '') }}'
                                        )"><i class="material-icons-outlined">edit</i></button>

                                        <form id="delete-form-{{ $company->company_code}}" action="{{ url('/TDS/master/company/delete/' . $company->company_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $company->company_code }}')"><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>Company Code</th>
								<th>Company Name</th>
								<th>Type Company</th>
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
                  <h5 class="modal-title" id="legendForm">Company  Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="company-form" id="company-form" method="post" action="{{url('/TDS/master/company/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Company Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="company_code" name="company_code" class="form-control" placeholder="Company Code" aria-label="company_code" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label"> Company Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="company_name" name="company_name" class="form-control" placeholder="Company Name " aria-label="company_name" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label"> {{__('Address')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="address" name="address" class="form-control" placeholder="Address " aria-label="address" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label"> {{__('Phone Number')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="{{__('Phone Number')}} " aria-label="phone_number" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label"> NPWP</label>
                    <div class="input-group mb-3" required>
                        <input type="text" id="npwp" name="npwp" class="form-control" placeholder="NPWP" aria-label="npwp" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">PKP</label>
                    <div class="form-group">
                        <input type="radio" name="pkp" value="yes" id="pkp_yes" required>
                        <label for="pkp_yes">Yes</label><br>

                        <input type="radio" name="pkp" value="no" id="pkp_no">
                        <label for="pkp_no">No</label><br>
                    </div>
                    <br>
                    <label for="exampleInputEmail1" class="form-label">Final Tax</label>
                    <div class="form-group">
                        <input type="radio" name="final_tax" value="yes" id="final_tax_yes" required>
                        <label for="final_tax_yes">Yes</label><br>

                        <input type="radio" name="final_tax" value="no" id="final_tax_no">
                        <label for="final_tax_no">No</label><br>
                    </div>
                    <br>
                    <label for="exampleInputEmail1" class="form-label"> Type Company</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="type_company" name="type_company" required>
                            @foreach ($typeCompanies as $type)
                                <option value={{$type->type_company}}>{{$type->type_company}}</option>
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

        document.getElementById('company_code').value = '';
            document.getElementById('company_name').value = '';
            document.getElementById('address').value = '';
            document.getElementById('phone_number').value = '';
            document.getElementById('npwp').value = '';
            document.getElementById('type_company').value = '';

        function cancelEdit() {
            document.getElementById('company_code').value = '';
            document.getElementById('company_name').value = '';
            document.getElementById('address').value = '';
            document.getElementById('phone_number').value = '';
            document.getElementById('npwp').value = '';

            document.getElementById('type_company').value = '';
            document.getElementById('legendForm').innerText = 'Company Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('company-form').action = `/TDS/master/company/insert`;
        }


        function editCategory(company_code,company_name,address,phone_number,npwp,pkp,final_tax,type_company) {
            console.log(company_code,company_name,address,phone_number,npwp,pkp,final_tax,type_company);

            document.getElementById('company_code').value =company_code;
            document.getElementById('company_name').value = company_name;
            document.getElementById('address').value = address;
            document.getElementById('phone_number').value = phone_number;
            document.getElementById('npwp').value =npwp;

            document.getElementById('type_company').value =type_company;
            document.getElementById('legendForm').innerText = 'Company Update';
            document.getElementById('cancelButton').style.display = 'inline-block';

            if (pkp === "1") {
            document.getElementById('pkp_yes').checked = true;
            } else {
            document.getElementById('pkp_no').checked = true;
            }
            if (final_tax === "1") {
            document.getElementById('final_tax_yes').checked = true;
            } else {
            document.getElementById('final_tax_no').checked = true;
            }

            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('company-form').action = `/TDS/master/company/edit/${company_code}`;
        }
    </script>
@endsection
