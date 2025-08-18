@extends('layouts.master')

@section('title', 'Master '. __('Tax'))
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
<x-page-title title="Master" pagetitle="{{__('Tax')}}" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">{{__('Tax')}}</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
								<th>No</th>
								<th>{{__('Tax Code')}}</th>
								<th>{{__('Tax Name')}}</th>
								<th>Tariff (in%)</th>
								<th>DPP</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($taxs as $tax)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($tax->id?? '') }}',
                                    '{{ addslashes($tax->tax_code?? '') }}',
                                    '{{ addslashes($tax->tax_name?? '') }}',
                                    '{{ addslashes($tax->tariff?? '') }}',
                                    '{{ addslashes($tax->tax_base?? '') }}',
                                    '{{ addslashes($tax->account_number?? '') }}',
                                    '{{ addslashes($tax->company_code?? '') }}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$tax->tax_code}}</td>
                                    <td>{{$tax->tax_name}}</td>
                                    <td>{{$tax->tariff}}</td>
                                    <td>{{$tax->tax_base}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                            '{{ addslashes($tax->id?? '') }}',
                                            '{{ addslashes($tax->tax_code?? '') }}',
                                            '{{ addslashes($tax->tax_name?? '') }}',
                                            '{{ addslashes($tax->tariff?? '') }}',
                                            '{{ addslashes($tax->tax_base?? '') }}',
                                            '{{ addslashes($tax->account_number?? '') }}',
                                            '{{ addslashes($tax->company_code?? '') }}',

                                        )"><i class="material-icons-outlined">edit</i></button>

                                        <form id="delete-form-{{ $tax->id}}" action="{{ url('/TDS/master/tax-master/delete/' . $tax->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $tax->id }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
								<th>No</th>
								<th>{{__('Tax Code')}}</th>
								<th>{{__('Tax Name')}}</th>
								<th>Tariff (in%)</th>
								<th>DPP</th>
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
                <h5 class="modal-title" id="legendForm">{{__('Tax')}} Insert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="tax-master-form" id="tax-master-form" method="post" action="{{url('/TDS/master/tax-master/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">{{__('Tax Code')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="tax_code" name="tax_code" class="form-control" placeholder="Tax Master Code" aria-label="tax_code" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Tax Name')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="tax_name" name="tax_name" class="form-control" placeholder="Tax Name " aria-label="tax_name" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Tariff (in %)</label>
                    <div class="input-group mb-3">
                        <input type="text" id="tariff" name="tariff" class="form-control" placeholder="Tariff (in %)" aria-label="tariff" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">DPP</label>
                    <div class="input-group mb-3">
                        <input type="text" id="tax_base" name="tax_base" class="form-control" placeholder="DPP" aria-label="tax_base" aria-describedby="basic-addon1" required>
                    </div>
                    <label hidden for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_name.' ('.$company->company_code.')'}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="account_number" name="account_number" required>
                            @foreach ($coas as $coa)
                                <option data-company="{{$coa->company_code}}" value={{$coa->account_number}}>{{$coa->account_number.' - '.$coa->account_name}}</option>
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
            document.getElementById('tax_code').value = '';
            document.getElementById('tax_name').value = '';
            document.getElementById('tariff').value = '';
            document.getElementById('tax_base').value = '';
            document.getElementById('account_number').value = '';
            document.getElementById('legendForm').innerText = 'Tax Master Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('tax-master-form').action = `/TDS/master/tax-master/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }


        function editCategory(id,tax_code,tax_name,tariff,tax_base,account_number,company_code) {
            document.getElementById('tax_code').value = tax_code;
            document.getElementById('tax_name').value = tax_name;
            document.getElementById('tariff').value = tariff;
            document.getElementById('tax_base').value = tax_base;
            document.getElementById('account_number').value = account_number;
            document.getElementById('company_code').value = company_code;
            document.getElementById('legendForm').innerText = 'Tax Master Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('tax-master-form').action = `/TDS/master/tax-master/edit/${id}`;
        }
    </script>

<script>
    var companySelect = document.getElementById('company_code');
    var accountNumberSelect = document.getElementById('account_number');

    // Add event listener for when the company is selected
    companySelect.addEventListener('change', function() {
        var selectedCompany = this.value;
        accountNumberSelect.value = '';
        for (var i = 0; i < accountNumberSelect.options.length; i++) {
            var optionNumber = accountNumberSelect.options[i];
            var coaCompanyCode = optionNumber.getAttribute('data-company');
            if (selectedCompany === "" || coaCompanyCode === selectedCompany) {
                optionNumber.style.display = 'block';
            } else {
                optionNumber.style.display = 'none';
            }
        }
        accountNumberSelect.value = '';
    });
</script>
@endsection
