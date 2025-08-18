@extends('layouts.master')

@section('title', 'Master '. __('Customer'))
@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }

    input:not([type]), input[type="checkbox"]{
        width: 20px;
        height: 20px;
        transform: scale(1.5);
        margin: 0;
        cursor: pointer;
    }

</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection
@section('content')
<x-page-title title="Master" pagetitle="{{__('Customer')}}" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">{{__('Customer')}}</h6>

                    <button type="button" class="mb-3 btn btn-primary" data-bs-toggle="modal" onclick="cancelEdit()"
                        data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                        Tambah Baru
                    </button>
                    <a style="font-size:25px"> | </a>
                    <button type="button" class="mb-3 btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport" @if(!in_array('create', $privileges)) disabled @endif>
                        Import
                    </button>
                    <button type="button" class="mb-3 btn btn-success d-none" data-bs-toggle="modal" data-bs-target="#modalImport2" @if(!in_array('create', $privileges)) disabled @endif>
                        Import Extra
                    </button>
                    <a href="{{ route('customer.export') }}" class="mb-3 btn btn-secondary">Download Template <i class="fa fa-download"></i>
                    </a>

				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>{{__('Customer Code')}}</th>
								<th>{{__('Customer Name')}}</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($customers as $cust)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editCategory(
                                '{{ addslashes($cust->customer_code ?? '') }}',
                                '{{ addslashes($cust->customer_name ?? '') }}',
                                '{{ addslashes($cust->address ?? '') }}',
                                '{{ addslashes($cust->warehouse_address ?? '') }}',
                                '{{ addslashes($cust->phone_number ?? '') }}',
                                '{{ $cust->pkp??''}}',
                                '{{ addslashes($cust->include??'') }}',
                                '{{ addslashes($cust->bonded_zone??'') }}',
                                '{{ addslashes($cust->currency_code ?? '') }}',
                                '{{ addslashes($cust->category_customer ?? '') }}',
                                '{{ addslashes($cust->group_customer ?? '') }}',
                                '{{ addslashes($cust->company_code ?? '') }}',
                                '{{ addslashes($cust->nik ?? '') }}',
                                '{{ addslashes($cust->npwp ?? '') }}',
                                '{{ addslashes($cust->zone ?? '') }}',
                                '{{ addslashes($cust->city ?? '') }}',
                                '{{ addslashes($cust->sales ?? '') }}',
                                '{{ addslashes($cust->email ?? '') }}',
                                '{{ addslashes($cust->account_receivable?? '') }}',
                                '{{ addslashes($cust->account_dp?? '') }}',
                                '{{ addslashes($cust->account_add_tax?? '') }}',
                                '{{ addslashes($cust->account_add_tax_bonded_zone?? '') }}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$cust->customer_code}}</td>
                                    <td>{{$cust->customer_name}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                '{{ addslashes($cust->customer_code ?? '') }}',
                                '{{ addslashes($cust->customer_name ?? '') }}',
                                '{{ addslashes($cust->address ?? '') }}',
                                '{{ addslashes($cust->warehouse_address ?? '') }}',
                                '{{ addslashes($cust->phone_number ?? '') }}',
                                '{{ addslashes($cust->pkp ?? '') }}',
                                '{{ addslashes($cust->include ?? '') }}',
                                '{{ addslashes($cust->bonded_zone ?? '') }}',
                                '{{ addslashes($cust->currency_code ?? '') }}',
                                '{{ addslashes($cust->category_customer ?? '') }}',
                                '{{ addslashes($cust->group_customer ?? '') }}',
                                '{{ addslashes($cust->company_code ?? '') }}',
                                '{{ addslashes($cust->nik ?? '') }}',
                                '{{ addslashes($cust->npwp ?? '') }}',
                                '{{ addslashes($cust->zone ?? '') }}',
                                '{{ addslashes($cust->city ?? '') }}',
                                '{{ addslashes($cust->sales ?? '') }}',
                                '{{ addslashes($cust->email ?? '') }}',
                                '{{ addslashes($cust->account_receivable?? '') }}',
                                '{{ addslashes($cust->account_dp?? '') }}',
                                '{{ addslashes($cust->account_add_tax?? '') }}',
                                '{{ addslashes($cust->account_add_tax_bonded_zone?? '') }}',
                                )"><i class="material-icons-outlined">edit</i></button>

                                        <form id="delete-form-{{ $cust->customer_code }}" action="{{ url('/TDS/master/customer/delete/' . $cust->customer_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('{{ $cust->customer_code }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>{{__('Customer Code')}}</th>
								<th>{{__('Customer Name')}}</th>
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
                  <h5 class="modal-title" id="legendForm">Input {{__('Customer')}} </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="type-company-form" id="type-company-form" method="post" action="{{url('/TDS/master/customer/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">{{__('Customer Code')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="customer_code" name="customer_code" class="form-control"  placeholder="Customer Code" aria-label="Username" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Customer Name')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="{{__('Customer Name')}}" aria-label="Username" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Address')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="address" name="address" class="form-control" placeholder="Address" aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Phone Number')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="{{__('Phone Number')}}" aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Email</label>
                    <div class="input-group mb-3">
                        <input type="text" id="email" name="email" class="form-control" placeholder="Email" aria-label="email" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">NIK</label>
                    <div class="input-group mb-3">
                        <input type="text" id="nik" name="nik" class="form-control" placeholder="NIK" aria-label="NIK" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">NPWP</label>
                    <div class="input-group mb-3">
                        <input type="text" id="npwp" name="npwp" class="form-control" placeholder="NPWP" aria-label="NPWP" aria-describedby="basic-addon1">
                    </div>

                    <label for="exampleInputEmail1" class="form-label">Kota</label>
                    <div class="input-group mb-3">
                        <input type="text" id="city" name="city" class="form-control" placeholder="City" aria-label="City" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">PKP</label>
                    <div class="form-group">
                        <input type="radio" name="pkp" value="yes" id="pkp_yes" required>
                        <label for="pkp_yes">Yes</label><br>

                        <input type="radio" name="pkp" value="no" id="pkp_no">
                        <label for="pkp_no">No</label><br>
                    </div>
                    <br>
                    <label for="exampleInputEmail1" class="form-label">Include</label>
                    <div class="form-group">
                        <input type="radio" name="include" value="yes" id="include_yes" required>
                        <label for="include_yes">Yes</label><br>

                        <input type="radio" name="include" value="no" id="include_no">
                        <label for="include_no">No</label><br>
                    </div>
                    <br>
                    {{-- <label for="exampleInputEmail1" class="form-label">Bonded Zone</label>
                    <div class="form-group">
                        <input type="radio" name="bonded_zone" value="yes" id="bonded_zone_yes" required>
                        <label for="bonded_zone_yes">Yes</label><br>

                        <input type="radio" name="bonded_zone" value="no" id="bonded_zone_no">
                        <label for="bonded_zone_no">No</label><br>
                    </div>
                    <br>
                    {{-- <label for="exampleInputEmail1" class="form-label">Currency Code</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="currency_code" name="currency_code" required>
                            @foreach ($currencies as $curr)
                                <option value={{$curr->currency_code}}>{{$curr->currency_code.' ('.$curr->currency_name.')'}}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <input type="hidden" name="group_customer" id="group_customer" value="DEFAULT">
                    <label for="exampleInputEmail1" class="form-label">{{__('Department')}}</label>
                    <div class="input-group mb-5">
                        <select class="form-select" id="department" name="department" required>
                            <option value="{{$department_code}}" selected>{{$department_name}}</option>
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Receivable</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-receivable" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-receivable')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-receivable" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_receivable" id="account_receivable">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account DP</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-dp" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-dp')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-dp" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_dp" id="account_dp">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Additional Tax</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-add-tax" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-add-tax')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-add-tax" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_add_tax" id="account_add_tax">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Add Tax Bonded Zone</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-add-tax-bond" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" >
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-add-tax-bond')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-add-tax-bond" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_add_tax_bonded_zone" id="account_add_tax_bonded_zone">
                    </div>

                    <label hidden for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option  value={{$company->company_code}}>{{$company->company_name.' ('.$company->company_code.')'}}</option>
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

          <div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="importCustomerLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="importForm" method="post" action="{{ url('/TDS/master/customer/import') }}"
                        enctype="multipart/form-data">
                            @csrf
                            <label for="importFile" class="form-label">Choose file to import</label>
                            <input type="file" id="importFile" name="importFile" class="form-control"
                            accept=".csv, .xlsx" required>
                            <br>
                            <label for="exampleInputEmail1" class="form-label">{{__('Department')}}</label>
                            <div class="input-group mb-3">
                                <select class="form-select" id="department" name="department" required>
                                    <option value="{{$department_code}}" selected>{{$department_name}}</option>
                                </select>
                            </div>
                            <label for="exampleInputEmail1" class="form-label">Account Receivable</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-receivable-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-receivable-1')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-receivable-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_receivable" id="account_receivable_1">
                            </div>
                            <label for="exampleInputEmail1" class="form-label">Account DP</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-dp-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" >
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-dp-1')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-dp-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_dp" id="account_dp_1">
                            </div>
                            <label for="exampleInputEmail1" class="form-label">Account Additional Tax</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-add-tax-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" >
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-add-tax-1')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-add-tax-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_add_tax" id="account_add_tax_1">
                            </div>
                            <label for="exampleInputEmail1" class="form-label">Account Add Tax Bonded Zone</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-add-tax-bond-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-add-tax-bond-1')"><i class="material-icons-outlined" >edit</i></button>
                                </div>
                                <div id="search-result-acc-add-tax-bond-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_add_tax_bonded_zone" id="account_add_tax_bonded_zone_1">
                            </div>


                            <button type="submit" class="btn btn-primary">Import</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    </div>

                </div>
            </div>
        </div>
          <div class="modal fade" id="modalImport2" tabindex="-1" aria-labelledby="importCustomerLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="importForm" method="post" action="{{ url('/TDS/master/customer/import-extra') }}"
                        enctype="multipart/form-data">
                            @csrf
                            <label for="importFile" class="form-label">Choose file to import</label>
                            <input type="file" id="importFile" name="importFile" class="form-control"
                            accept=".csv, .xlsx" required>
                            <br>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
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


        function confirmDelete(id) {
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
            document.getElementById('customer_code').value = '';
            document.getElementById('customer_name').value = '';
            document.getElementById('address').value = '';
            document.getElementById('phone_number').value = '';
            document.getElementById('pkp_no').checked = true;
            document.getElementById('include_no').checked = true;
            document.getElementById('nik').value = '';
            document.getElementById('npwp').value = '';
            document.getElementById('city').value = '';
            document.getElementById('email').value = '';
            document.getElementById('group_customer').value = '';

            document.getElementById('search-acc-receivable').value = '';
            document.getElementById('search-acc-receivable').readOnly = false;
            document.getElementById('account_receivable').value = ''; // Clear hidden input
            document.getElementById('search-acc-dp').value = '';
            document.getElementById('search-acc-dp').readOnly = false;
            document.getElementById('account_dp').value = ''; // Clear hidden input
            document.getElementById('search-acc-add-tax').value = '';
            document.getElementById('search-acc-add-tax').readOnly = false;
            document.getElementById('account_add_tax').value = ''; // Clear hidden input
            document.getElementById('search-acc-add-tax-bond').value = '';
            document.getElementById('search-acc-add-tax-bond').readOnly = false;
            document.getElementById('account_add_tax_bonded_zone').value = ''; // Clear hidden input

            document.getElementById('legendForm').innerText = 'Customer Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('type-company-form').action = `/TDS/master/customer/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }

        cancelEdit();

        function editCategory(customer_code,customer_name,address,warehouse_address,phone_number,pkp,include,bonded_zone,currency_code,category_customer,group_customer,company_code,nik,npwp,zone,city,sales,email,account_receivable,account_dp,account_add_tax,account_add_tax_bonded_zone) {

            document.getElementById('customer_code').value =customer_code;
            document.getElementById('customer_name').value =customer_name;
            document.getElementById('address').value = address;
            document.getElementById('nik').value = nik;
            document.getElementById('npwp').value = npwp;
            document.getElementById('city').value = city;
            document.getElementById('email').value = email;
            document.getElementById('phone_number').value = phone_number;
            document.getElementById('cancelButton').style.display = 'inline-block';
            // document.getElementById('currency_code').value = currency_code;
            document.getElementById('group_customer').value = group_customer;
            document.getElementById('company_code').value = company_code;
            document.getElementById('legendForm').innerText = 'Customer Update';
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('type-company-form').action = `/TDS/master/customer/edit/${customer_code}`;

            if (pkp === "1") {
            document.getElementById('pkp_yes').checked = true;
            } else {
            document.getElementById('pkp_no').checked = true;
            }
            if (include === "1") {
            document.getElementById('include_yes').checked = true;
            } else {
            document.getElementById('include_no').checked = true;
            }
            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }

            if(account_receivable){
                document.getElementById('account_receivable').value = account_receivable;
                let textDisplay = coas.find((element)=>element.account_number ==account_receivable)?.account_name; // Added optional chaining
                if (textDisplay) {
                    document.getElementById('search-acc-receivable').value = account_receivable+' - '+textDisplay;
                    document.getElementById('search-acc-receivable').readOnly = true;
                } else {
                    document.getElementById('search-acc-receivable').value = account_receivable; // Show just number if name not found
                    document.getElementById('search-acc-receivable').readOnly = true;
                }
            }

            if(account_dp){
                document.getElementById('account_dp').value = account_dp;
                textDisplay = coas.find((element)=>element.account_number ==account_dp)?.account_name;
                if (textDisplay) {
                    document.getElementById('search-acc-dp').value = account_dp+' - '+textDisplay;
                    document.getElementById('search-acc-dp').readOnly = true;
                } else {
                    document.getElementById('search-acc-dp').value = account_dp;
                    document.getElementById('search-acc-dp').readOnly = true;
                }
            }

            if(account_add_tax){
                document.getElementById('account_add_tax').value = account_add_tax;
                textDisplay = coas.find((element)=>element.account_number ==account_add_tax)?.account_name;
                if (textDisplay) {
                    document.getElementById('search-acc-add-tax').value = account_add_tax+' - '+textDisplay;
                    document.getElementById('search-acc-add-tax').readOnly = true;
                } else {
                    document.getElementById('search-acc-add-tax').value = account_add_tax;
                    document.getElementById('search-acc-add-tax').readOnly = true;
                }
            }

            if(account_add_tax_bonded_zone){
                document.getElementById('account_add_tax_bonded_zone').value = account_add_tax_bonded_zone;
                textDisplay = coas.find((element)=>element.account_number ==account_add_tax_bonded_zone)?.account_name;
                if (textDisplay) {
                    document.getElementById('search-acc-add-tax-bond').value = account_add_tax_bonded_zone+' - '+textDisplay;
                    document.getElementById('search-acc-add-tax-bond').readOnly = true;
                } else {
                    document.getElementById('search-acc-add-tax-bond').value = account_add_tax_bonded_zone;
                    document.getElementById('search-acc-add-tax-bond').readOnly = true;
                }
            }
        }
        function updateActiveItem(items) {
            items.forEach((item, index) => {
                item.classList.toggle('active', index === activeIndex);
            });
            if (activeIndex >= 0) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }
        function clearInput(inputId) {
            document.getElementById(inputId).value = '';
            document.getElementById(inputId).readOnly = false;
        }


        const style = document.createElement('style');
        style.textContent = `
            .list-group-item.active {
                background-color: blue;
                border-color: #dee2e6;
            }
        `;

        const coas = @json($coas);

        function setupSearch(inputId, resultsContainerId,inputHid) {
            const inputElement = document.getElementById(inputId);
            const resultsContainer = document.getElementById(resultsContainerId);

            inputElement.addEventListener('input', function () {
                let query = this.value.toLowerCase();
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';

                if (query.length > 0) {
                    let filteredResults = coas.filter(item =>
                        item.account_number.toLowerCase().includes(query) ||
                        item.account_name.toLowerCase().includes(query)
                    );

                    if (filteredResults.length > 0) {
                        resultsContainer.style.display = 'block';
                        filteredResults.forEach(item => {
                            let listItem = document.createElement('a');
                            listItem.className = 'list-group-item list-group-item-action';
                            listItem.href = '#';
                            listItem.innerHTML = `
                                <strong>${item.account_number}</strong> -
                                ${item.account_name} <br>`;
                            listItem.addEventListener('click', function(e) {
                                e.preventDefault();
                                inputElement.value = item.account_number + ' - ' + item.account_name;
                                inputElement.readOnly = true;
                                document.getElementById(inputHid).value = item.account_number;
                                resultsContainer.style.display = 'none';
                            });
                            resultsContainer.appendChild(listItem);
                        });
                    }
                }
            });
        }
        function clearInput(inputId) {
            document.getElementById(inputId).value = '';
            document.getElementById(inputId).readOnly = false;
        }
        setupSearch('search-acc-receivable', 'search-result-acc-receivable','account_receivable');
        setupSearch('search-acc-receivable-1', 'search-result-acc-receivable-1','account_receivable_1');
        setupSearch('search-acc-dp', 'search-result-acc-dp','account_dp');
        setupSearch('search-acc-dp-1', 'search-result-acc-dp-1','account_dp_1');
        setupSearch('search-acc-add-tax', 'search-result-acc-add-tax','account_add_tax');
        setupSearch('search-acc-add-tax-1', 'search-result-acc-add-tax-1','account_add_tax_1');
        setupSearch('search-acc-add-tax-bond', 'search-result-acc-add-tax-bond','account_add_tax_bonded_zone');
        setupSearch('search-acc-add-tax-bond-1', 'search-result-acc-add-tax-bond-1','account_add_tax_bonded_zone_1');
    </script>

@endsection
