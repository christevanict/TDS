@extends('layouts.master')

@section('title', 'Master Payment Method')
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
<x-page-title title="Master" pagetitle="Payment Method" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Payment Method</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>Payment Method Code</th>
								<th>Payment Method Name</th>
								<th>Cost Payment</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($pays as $pay)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($pay->payment_method_code?? '') }}',
                                    '{{ addslashes($pay->payment_name?? '') }}',
                                    '{{ addslashes($pay->cost_payment?? '') }}',
                                    '{{ addslashes($pay->account_number?? '') }}',
                                    '{{ addslashes($pay->acc_number_cost?? '') }}',
                                    '{{ addslashes($pay->company_code?? '') }}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$pay->payment_method_code}}</td>
                                    <td>{{$pay->payment_name}}</td>
                                    <td>{{$pay->cost_payment}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                            '{{ addslashes($pay->payment_method_code?? '') }}',
                                            '{{ addslashes($pay->payment_name?? '') }}',
                                            '{{ addslashes($pay->cost_payment?? '') }}',
                                            '{{ addslashes($pay->account_number?? '') }}',
                                            '{{ addslashes($pay->acc_number_cost?? '') }}',
                                            '{{ addslashes($pay->company_code?? '') }}',

                                        )"><i class="material-icons-outlined">edit</i></button>

                                        <form id="delete-form-{{ $pay->payment_method_code}}" action="{{ url('/TDS/master/payment-method/delete/' . $pay->payment_method_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $pay->payment_method_code }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>Payment Method Code</th>
								<th>Payment Method Name</th>
								<th>Cost Payment</th>
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
                  <h5 class="modal-title" id="legendForm">Payment Method  Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="payment-method-form" id="payment-method-form" method="post" action="{{url('/TDS/master/payment-method/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Payment Method Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="payment_method_code" name="payment_method_code" class="form-control" placeholder="Payment Method Code" aria-label="payment_method_code" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Payment Method Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="payment_name" name="payment_name" class="form-control" placeholder="Payment Method Name " aria-label="payment_name" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Cost Payment</label>
                    <div class="input-group mb-3">
                        <input type="text" id="cost_payment" name="cost_payment" class="form-control" placeholder="Cost Payment" aria-label="cost_payment" aria-describedby="basic-addon1" required>
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
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_number" id="account_number">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Number Cost</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-cost" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-cost')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-cost" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_cost" id="acc_number_cost">
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
            document.getElementById('payment_method_code').value = '';
            document.getElementById('payment_name').value = '';
            document.getElementById('cost_payment').value = '';
            document.getElementById('account_number').value = '';
            document.getElementById('search-acc').value = '';
            document.getElementById('search-acc').readOnly = false;

            document.getElementById('acc_number_cost').value = '';
            document.getElementById('search-acc-cost').value ='';
            document.getElementById('search-acc-cost').readOnly = false;
            document.getElementById('legendForm').innerText = 'Payment Method Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('payment-method-form').action = `/TDS/master/payment-method/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }


        function editCategory(payment_method_code,payment_name,cost_payment,account_number,acc_number_cost,company_code) {
            document.getElementById('payment_method_code').value = payment_method_code;
            document.getElementById('payment_name').value = payment_name;
            document.getElementById('cost_payment').value = cost_payment;
            document.getElementById('account_number').value = account_number;
            textDisplay = coas.find((element)=>element.account_number ==account_number).account_name;
            document.getElementById('search-acc').value = account_number+' - '+textDisplay;
            document.getElementById('search-acc').readOnly = true;

            document.getElementById('acc_number_cost').value = acc_number_cost;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_cost).account_name;
            document.getElementById('search-acc-cost').value = acc_number_cost+' - '+textDisplay;
            document.getElementById('search-acc-cost').readOnly = true;
            document.getElementById('company_code').value = company_code;
            document.getElementById('legendForm').innerText = 'Payment Method Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }


            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('payment-method-form').action = `/TDS/master/payment-method/edit/${payment_method_code}`;
        }
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

        setupSearch('search-acc', 'search-result-acc','account_number');
        setupSearch('search-acc-cost', 'search-result-acc-cost','acc_number_cost');
</script>
@endsection
