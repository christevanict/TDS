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
                    <label for="exampleInputEmail1" class="form-label">Account Receivable</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_number" id="account_number">
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
            document.getElementById('search-acc').value = '';
            document.getElementById('search-acc').readOnly = false;
            document.getElementById('account_number').value = '';
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
            if(account_number){
                document.getElementById('account_number').value = account_number;
                let textDisplay = coas.find((element)=>element.account_number ==account_number)?.account_name; // Added optional chaining
                if (textDisplay) {
                    document.getElementById('search-acc').value = account_number+' - '+textDisplay;
                    document.getElementById('search-acc').readOnly = true;
                } else {
                    document.getElementById('search-acc').value = account_number; // Show just number if name not found
                    document.getElementById('search-acc').readOnly = true;
                }
            }
        }

        const coas = @json($coas);

        function setupSearch(inputId, resultsContainerId,inputHid) {
            const inputElement = document.getElementById(inputId);
            const resultsContainer = document.getElementById(resultsContainerId);

            inputElement.addEventListener('input', function () {
                activeIndex = -1;
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
            // Keydown event listener for navigation
            inputElement.addEventListener('keydown', function(e) {
                const items = resultsContainer.querySelectorAll('.list-group-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (activeIndex < items.length - 1) {
                        activeIndex++;
                        updateActiveItem(items);
                    }
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (activeIndex > -1) { // Allow going back to no selection
                        activeIndex--;
                        updateActiveItem(items);
                    }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (activeIndex >= 0 && items[activeIndex]) {
                        items[activeIndex].click();
                    }
                }
            });
        }
        function clearInput(inputId) {
            document.getElementById(inputId).value = '';
            document.getElementById(inputId).readOnly = false;
        }
        function updateActiveItem(items) {
            items.forEach((item, index) => {
                item.classList.toggle('active', index === activeIndex);
            });
            if (activeIndex >= 0) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }
        setupSearch('search-acc', 'search-result-acc','account_number');
    </script>
@endsection
