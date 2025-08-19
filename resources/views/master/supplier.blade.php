@extends('layouts.master')

@section('title', 'Master Vendor')
@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }
</style>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection
@section('content')
<x-page-title title="Master" pagetitle="{{__('Supplier')}}" />
        <hr>
        <div class="card">
            <div class="card-body">
                <h6 class="mb-2 text-uppercase">{{__('Supplier')}}</h6>
                <button type="button" class="mb-3 btn btn-primary" data-bs-toggle="modal" onclick="cancelEdit()"
                data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                Tambah Baru
                </button>
                {{-- <a style="font-size:25px"> | </a> --}}
                <button type="button" class="mb-3 btn btn-success d-none" data-bs-toggle="modal" data-bs-target="#modalImport" @if(!in_array('create', $privileges)) disabled @endif>
                    Import
                </button>
                <a href="{{ route('supplier.export') }}" class="mb-3 btn btn-secondary d-none">Download Template <i class="fa fa-download"></i>
                </a>
                <div class="table-responsive">
                    <table id="example" class="table table-hover table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>{{__('Supplier Code')}}</th>
                                <th>{{__('Supplier Name')}}</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suppliers as $supp)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($supp->supplier_code?? '') }}',
                                    '{{ addslashes($supp->supplier_name?? '') }}',
                                    '{{ addslashes($supp->address?? '') }}',
                                    '{{ addslashes($supp->warehouse_address?? '') }}',
                                    '{{ addslashes($supp->phone_number?? '') }}',
                                    '{{ addslashes($supp->pkp?'true':'false') }}',
                                    '{{ addslashes($supp->include?'true':'false') }}',
                                    '{{ addslashes($supp->currency_code?? '') }}',
                                    '{{ addslashes($supp->company_code?? '') }}',
                                )">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{$supp->supplier_code}}</td>
                                    <td>{{$supp->supplier_name}}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" onclick="editCategory(
                                            '{{ addslashes($supp->supplier_code?? '') }}',
                                            '{{ addslashes($supp->supplier_name?? '') }}',
                                            '{{ addslashes($supp->address?? '') }}',
                                            '{{ addslashes($supp->warehouse_address?? '') }}',
                                            '{{ addslashes($supp->phone_number?? '') }}',
                                            '{{ addslashes($supp->pkp?'true':'false') }}',
                                            '{{ addslashes($supp->include?'true':'false') }}',
                                            '{{ addslashes($supp->currency_code?? '') }}',
                                            '{{ addslashes($supp->company_code?? '') }}',

                                        )"><i class="material-icons-outlined">edit</i></button>

                                        <form id="delete-form-{{ $supp->supplier_code}}" action="{{ url('/TDS/master/supplier/delete/' . $supp->supplier_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $supp->supplier_code }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        <tfoot>
                            <tr>
                                <th>No</th>
                                <th>{{__('Supplier Code')}}</th>
                                <th>{{__('Supplier Name')}}</th>
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
                <h5 class="modal-title" id="legendForm">Vendor  Insert</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="supplier-form" id="supplier-form" method="post" action="{{url('/TDS/master/supplier/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">{{__('Supplier Code')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="supplier_code" name="supplier_code" class="form-control" placeholder="{{__('Supplier Code')}}" aria-label="supplier_code" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Supplier Name')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="supplier_name" name="supplier_name" class="form-control" placeholder="Vendor Name " aria-label="supplier_name" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Address')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="address" name="address" class="form-control" placeholder="Address" aria-label="address" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Phone Number')}}</label>
                    <div class="input-group mb-3">
                        <input type="text" id="phone_number" name="phone_number" class="form-control" placeholder="{{__('Phone Number')}}" aria-label="phone_number" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">NPWP</label>
                    <div class="input-group mb-3">
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
                    <label for="exampleInputEmail1" class="form-label">Include</label>
                    <div class="form-group">
                        <input type="radio" name="include" value="yes" id="include_yes" required>
                        <label for="include_yes">Yes</label><br>

                        <input type="radio" name="include" value="no" id="include_no">
                        <label for="include_no">No</label><br>
                    </div>

                    {{-- <label for="exampleInputEmail1" class="form-label">Currency</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="currency_code" name="currency_code" required>
                            @foreach ($currencies as $curr)
                                <option value={{$curr->currency_code}}>{{$curr->currency_code.' ('.$curr->currency_name.')'}}</option>
                            @endforeach
                        </select>
                    </div> --}}
                    <label hidden for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_name.' ('.$company->company_code.')'}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Department</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="department" name="department" required>
                            <option value="DP01" selected>TDS</option>
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Payable</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-payable" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-payable')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-payable" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_payable" id="account_payable">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account DP</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-dp" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-dp')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-dp" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_dp" id="account_dp">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Payable GRPO</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-pay-grpo" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-pay-grpo')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-pay-grpo" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_payable_grpo" id="account_payable_grpo">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Account Additional Tax</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-add-tax" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required autocomplete="off">
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-add-tax')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-add-tax" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="account_add_tax" id="account_add_tax">
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
                        <form id="importForm" method="post" action="{{ url('/TDS/master/supplier/import') }}"
                        enctype="multipart/form-data">
                            @csrf
                            <label for="importFile" class="form-label">Choose file to import</label>
                            <input type="file" id="importFile" name="importFile" class="form-control"
                            accept=".csv, .xlsx" required>
                            <br>
                            <label for="exampleInputEmail1" class="form-label">Account Payable</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-payable-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-payable-1')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-payable-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_payable" id="account_payable_1">
                            </div>
                            <label for="exampleInputEmail1" class="form-label">Account DP</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-dp-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-dp-1')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-dp-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_dp" id="account_dp_1">
                            </div>
                            <label for="exampleInputEmail1" class="form-label">Account Payable GRPO</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-pay-grpo-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-pay-grpo-1')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-pay-grpo-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_payable_grpo" id="account_payable_grpo_1">
                            </div>
                            <label for="exampleInputEmail1" class="form-label">Account Additional Tax</label>
                            <div class="form-group mb-3">
                                <div class="input-group">
                                    <input type="text" id="search-acc-add-tax-1" class="form-control" placeholder="Search by Account Number or Account Name" autocomplete="off" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-add-tax-1')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-add-tax-1" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                    <!-- Search results will be injected here -->
                                </div>
                                <input type="hidden" name="account_add_tax" id="account_add_tax_1">
                            </div>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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

            document.getElementById('supplier_code').value = '';
            document.getElementById('supplier_name').value = '';
            document.getElementById('address').value = '';
            document.getElementById('phone_number').value = '';
            // document.getElementById('department').value = '';
            // document.getElementById('currency_code').value = '';


            let privileges = @json($privileges);
        function cancelEdit() {
            document.getElementById('supplier_code').value = '';
            document.getElementById('supplier_name').value = '';
            document.getElementById('address').value = '';
            document.getElementById('phone_number').value = '';
            // document.getElementById('currency_code').value = '';

            document.getElementById('legendForm').innerText = 'Vendor Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('supplier-form').action = `/TDS/master/supplier/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }


        function editCategory(supplier_code,supplier_name,address,warehouse_address,phone_number,pkp,include,currency_code,company_code) {

            document.getElementById('supplier_code').value =supplier_code;
            document.getElementById('supplier_name').value = supplier_name;
            document.getElementById('address').value = address;
            document.getElementById('phone_number').value = phone_number;

            let textDisplay ='';
            document.getElementById('company_code').value = company_code;
            document.getElementById('legendForm').innerText = 'Vendor Update';
            document.getElementById('cancelButton').style.display = 'inline-block';


            if (pkp) {
            document.getElementById('pkp_yes').checked = true;
            } else {
            document.getElementById('pkp_no').checked = true;
            }
            if (include) {
            document.getElementById('include_yes').checked = true;
            } else {
            document.getElementById('include_no').checked = true;
            }

            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }

            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('supplier-form').action = `/TDS/master/supplier/edit/${supplier_code}`;
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

setupSearch('search-acc-payable', 'search-result-acc-payable','account_payable');
setupSearch('search-acc-payable-1', 'search-result-acc-payable-1','account_payable_1')
setupSearch('search-acc-dp', 'search-result-acc-dp','account_dp');
setupSearch('search-acc-dp-1', 'search-result-acc-dp-1','account_dp_1');
setupSearch('search-acc-pay-grpo', 'search-result-acc-pay-grpo','account_payable_grpo');
setupSearch('search-acc-pay-grpo-1', 'search-result-acc-pay-grpo-1','account_payable_grpo_1');
setupSearch('search-acc-add-tax', 'search-result-acc-add-tax','account_add_tax');
setupSearch('search-acc-add-tax-1', 'search-result-acc-add-tax-1','account_add_tax_1');



</script>
@endsection
