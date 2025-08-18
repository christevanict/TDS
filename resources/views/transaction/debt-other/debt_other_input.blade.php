@extends('layouts.master')

@section('title',  __('Hutang Lain'))
@section('css')
<style>
    #search-result-dest {
        max-height: 200px; /* Set your desired maximum height */
        overflow-y: auto; /* Enable vertical scrolling */
        border: 1px solid #ccc; /* Optional: Add a border */
        background-color: #fff; /* Optional: Set a background color */
        display: none; /* Initially hidden */
    }
</style>
@endsection
@section('content')
<div class="row">
    <x-page-title title="{{__('Hutang Lain')}}" pagetitle="{{__('Hutang Lain')}} Input" />
    <hr>
    <div class="container content">
        <h2>{{__('Hutang Lain')}} Input</h2>

        <div id="message-container">
            @if(session('success'))
                <div id="success-message" class="alert alert-success fade show">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div id="error-message" class="alert alert-danger fade show">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <form id="si-form" action="{{ isset($salesInvoice) ? route('transaction.debt_other.update', $salesInvoice->id) : route('transaction.debt_other.store') }}" method="POST">
            @csrf
            @if(isset($salesInvoice)) @method('PUT') @endif
            <div class="card mb-3">
                <div class="card-header">{{__('Hutang Lain')}} {{__('Information')}}</div>
                <div class="card-body">
                    <input type="hidden" id="checkHPP" value="0">
                    <input type="hidden" name="token" id="token" value="{{$token}}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search">{{__('Search Supplier')}}</label>
                                <input type="text" id="search" class="form-control" placeholder="Search by Supplier Code, Name, or Address" autocomplete="off">
                                <div id="search-results" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                    <!-- Search results will be injected here -->
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="supplier_code">{{__('Supplier Code')}}</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control" readonly required>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="supplier_name">{{__('Supplier Name')}}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" readonly required>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly>
                            </div>
                            <br>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="department_code">Departemen</label>
                                <input type="text" name="department_name" id="department_name" class="form-control" value="{{$dpName}}" readonly>
                                <input type="hidden" name="department_code" id="department_code" class="form-control" value="{{$dp_code}}" >
                            </div>
                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <textarea name="notes" class="form-control" rows="5">{{ old('notes', '') }}</textarea>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" name="document_date" id="document_date" class="form-control date-picker" required value="{{ old('document_date', $salesInvoice->document_date ?? date('Y-m-d')) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" name="due_date" id="due_date" class="form-control date-picker" required value="{{ old('due_date', $salesInvoice->due_date ?? date('Y-m-d')) }}">
                            </div>
                            <br>


                        </div>
                    </div>
                </div>
            </div>

            <!-- Card for {{__('Hutang Lain')}} Details -->
            <div class="card mb-3">
                <div class="card-header">{{__('Hutang Lain')}} Details</div>
                <div class="card-body">
                    <div style="overflow-x: auto;">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <td>{{__('Akun')}}</td>
                            <td>{{__('Nominal')}}</td>
                            <td>{{__('Catatan')}}</td>
                            <td>Action</td>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form-group mb-3">
                                        <div class="input-group">
                                            <input type="text" id="search-acc-0" autocomplete="off" class="form-control" placeholder="Search by Account Number or Account Name" required>
                                            <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-0')"><i class="material-icons-outlined">edit</i></button>
                                        </div>
                                        <div id="search-result-acc-0" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                            <!-- Search results will be injected here -->
                                        </div>
                                        <input type="hidden" name="details[0][account_number]" id="acc_number_0">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" oninput="formatNumber(this)" name="details[0][nominal]" class="form-control text-end nominal" required placeholder="Nominal">
                                </td>
                                <td>
                                    <input type="text" name="details[0][note]" class="form-control" placeholder="...">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <td></td>
                            <td><h5 class="text-end">Total: <span id="total-value">0</span></h5></td>
                            <td colspan="2"></td>
                        </tfoot>
                    </table>
                    </div>
                    <button type="button" id="add-row" class="btn btn-primary">Tambah Detail</button>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Hutang Lain')}}</button>
            </div>
        </form>
    </div>

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
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>

document.addEventListener('DOMContentLoaded', function () {
            // Check if the success message is present
            @if(session('success'))
                // Show SweetAlert confirmation modal
                Swal.fire({
                    title: '{{__('Hutang Lain')}} Created',
                    text: 'Do you want to print it?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Yes',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        // User clicked "Yes"
                        var id = "{{ session('id') }}"; // Get the id from the session
                        if (id) {
                            // Navigate to the edit route with the id
                            window.location.href = "{{ route('debt_other.print', ['id' => ':id']) }}".replace(':id', id);
                        }
                    }
                });
            @endif
        });
let rowCount = 0;
const suppliers = @json($suppliers);
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

    document.getElementById('search').addEventListener('input', function () {
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        if (query.length > 0) {
        let filteredSuppliers = suppliers.filter(c =>
            c.supplier_code.toLowerCase().includes(query) ||
            c.supplier_name.toLowerCase().includes(query) ||
            c.address.toLowerCase().includes(query));
        if (filteredSuppliers.length > 0) {
            resultsContainer.style.display = 'block';
            filteredSuppliers.forEach(supplier => {
                let listItem = document.createElement('a');
                listItem.className = 'list-group-item list-group-item-action';
                listItem.href = '#';
                listItem.innerHTML = `
                    <strong>${supplier.supplier_code}</strong> -
                    ${supplier.supplier_name} <br>
                    <small>${supplier.address}</small>`;
                listItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('supplier_code').value = supplier.supplier_code;
                    supplierId = supplier.supplier_code;
                    document.getElementById('supplier_name').value = supplier.supplier_name;
                    document.getElementById('address').value = supplier.address;
                    resultsContainer.style.display = 'none';

                    // Show all rows if no supplier is selected
                    if (supplierId === "") {
                        $('#invoiceTable tbody tr').show();
                    } else {
                        // Hide rows that do not match the selected supplier
                        $('#invoiceTable tbody tr').each(function () {
                            const supplierIds = $(this).data('supplier-id');
                            let supplierOrigin  = suppliers.find((c)=>c.supplier_code ==supplierIds);

                            if (supplier.group_supplier == supplierOrigin.group_supplier) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    }

                });
                resultsContainer.appendChild(listItem);
            });
        }
    }
    });

    const coas = @json($coas);
    function setupSearch(inputId, resultsContainerId, inputHid) {
    const inputElement = document.getElementById(inputId);
    const resultsContainer = document.getElementById(resultsContainerId);
    let selectedIndex = -1; // Track the currently selected item

    inputElement.addEventListener('input', function () {
        let query = this.value.toLowerCase();
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        selectedIndex = -1; // Reset selection when input changes

        if (query.length > 0) {
            let filteredResults = coas.filter(item =>
                item.account_number.toLowerCase().includes(query) ||
                item.account_name.toLowerCase().includes(query)
            );
            if (filteredResults.length > 0) {
                resultsContainer.style.display = 'block';
                filteredResults.forEach((item, index) => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.innerHTML = `
                        <strong>${item.account_number}</strong> -
                        ${item.account_name} <br>`;
                    listItem.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectItem(item);
                    });
                    resultsContainer.appendChild(listItem);
                });
            }
        }
    });

    // Add keydown event listener for arrow keys and enter
    inputElement.addEventListener('keydown', function(e) {
        const items = resultsContainer.getElementsByClassName('list-group-item');

        if (items.length === 0) return;

        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                if (selectedIndex < items.length - 1) {
                    selectedIndex++;
                    updateSelection(items);
                }
                break;

            case 'ArrowUp':
                e.preventDefault();
                if (selectedIndex > 0) {
                    selectedIndex--;
                    updateSelection(items);
                }
                break;

            case 'Enter':
            e.preventDefault();
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    // Get the original item from filtered results using the stored index
                    const filteredResults = coas.filter(item =>
                        item.account_number.toLowerCase().includes(inputElement.value.toLowerCase()) ||
                        item.account_name.toLowerCase().includes(inputElement.value.toLowerCase())
                    );
                    const selectedItem = filteredResults[selectedIndex];
                    if (selectedItem) {
                        selectItem(selectedItem);
                    }
                }
                break;
        }
    });

    // Helper function to update visual selection
    function updateSelection(items) {
        for (let i = 0; i < items.length; i++) {
            items[i].classList.remove('active');
            if (i === selectedIndex) {
                items[i].classList.add('active');
                items[i].scrollIntoView({ block: 'nearest' });
            }
        }
    }

    // Helper function to handle selection
    function selectItem(item) {
        inputElement.value = item.account_number + ' - ' + item.account_name;
        inputElement.readOnly = true;
        document.getElementById(inputHid).value = item.account_number;
        resultsContainer.style.display = 'none';
        selectedIndex = -1;
    }
}
    function clearInput(inputId) {
        document.getElementById(inputId).value = '';
        document.getElementById(inputId).readOnly = false;
    }
    setupSearch('search-acc-0', 'search-result-acc-0','acc_number_0');
    function calculateTotals() {
        let total = 0;
        const disc_nominal = 0;
        document.querySelectorAll('.nominal').forEach(function (input) {

            input.value = input.value.replace(/,/g, ''); // Remove any thousand separators
            if(input.id=='disc_nominal'){
                total -= parseFloat(input.value) || 0;
            }else{
                total += parseFloat(input.value) || 0;
            }

            const cursorPosition = input.selectionStart;
            let value = input.value.replace(/,/g, '');
            // Format the number with thousand separators
            let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            // Set the new value
            input.value = formattedValue;

            // Adjust the cursor position
            const newCursorPosition = formattedValue.length - (value.length - cursorPosition);
            input.setSelectionRange(newCursorPosition, newCursorPosition);
        });
        let strTotal = (total)+"";
        let value2 = strTotal.replace(/,/g, '');
            // Format the number with thousand separators
        let formattedValue2 = value2.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('total-value').innerText = formattedValue2;

        return { total }; // Return totals for validation
    }
    calculateTotals();

    function addInputListeners() {
        document.querySelectorAll('.nominal').forEach(function (input) {
            input.addEventListener('input', function () {
                calculateTotals(); // Calculate totals when any input changes
            });
        });
    }
    addInputListeners();

    function formatNumber(input) {
        // Get the cursor position
        const cursorPosition = input.selectionStart;
        input.value = input.value.replace(/[^0-9]/g, '');
        // Remove any existing thousand separators
        let value = input.value.replace(/,/g, '');

        // Format the number with thousand separators
        let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        // Set the new value
        input.value = formattedValue;

        // Adjust the cursor position
        const newCursorPosition = formattedValue.length - (value.length - cursorPosition);
        input.setSelectionRange(newCursorPosition, newCursorPosition);
    }

    let supplierId='';
    let dp_code = @json($dp_code);





    document.addEventListener('DOMContentLoaded', function () {
        let rowCount = {{ isset($bankCashIn) ? count($bankCashIn->details) : 1 }};

        document.getElementById('add-row').addEventListener('click', function () {
            const detailsTableBody = document.querySelector('#dynamicTable tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-${rowCount}" autocomplete="off" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-${rowCount}')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-${rowCount}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="details[${rowCount}][account_number]" id="acc_number_${rowCount}">
                    </div>
                </td>
                <td>
                    <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][nominal]" class="form-control text-end nominal" required placeholder="Nominal">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][note]" class="form-control"  placeholder="...">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                </td>

            `;
            detailsTableBody.appendChild(newRow);
            setupSearch(`search-acc-${rowCount}`, `search-result-acc-${rowCount}`,`acc_number_${rowCount}`);

            calculateTotals();
            addInputListeners();
            rowCount++;
        });

        document.querySelector('#dynamicTable tbody').addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    });

    // Add CSS for active item (add this once in your main script)
    const style = document.createElement('style');
    style.textContent = `
        .list-group-item.active {
            background-color: blue;
            border-color: #dee2e6;
        }
    `;

    document.getElementById('si-form').addEventListener('submit', function(e) {
        // Check if supplier is selected
        const supplierCode = document.getElementById('supplier_code').value;
        if (!supplierCode || supplierCode.trim() === '') {
            e.preventDefault();
            Swal.fire({
                title: 'Error!',
                text: 'Please select a supplier first',
                icon: 'error',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Check details
        const detailsRows = document.querySelectorAll('#dynamicTable tbody tr');
        let hasErrors = false;
        let errorMessages = [];

        detailsRows.forEach((row, index) => {
            const accountNumberInput = row.querySelector(`input[name="details[${index}][account_number]"]`);
            const nominalInput = row.querySelector(`input[name="details[${index}][nominal]"]`);

            if (!accountNumberInput.value || accountNumberInput.value.trim() === '') {
                hasErrors = true;
                errorMessages.push(`Akun belum terpilih untuk baris ${index + 1}`);
            }

            const nominalValue = nominalInput.value.replace(/,/g, '');
            if (!nominalValue || nominalValue.trim() === '' || parseFloat(nominalValue) <= 0) {
                hasErrors = true;
                errorMessages.push(`Valid nominal amount is required for row ${index + 1}`);
            }
        });

        if (hasErrors) {
            e.preventDefault();
            Swal.fire({
                title: 'Validation Error',
                html: '<ul>' + errorMessages.map(msg => `<li>${msg}</li>`).join('') + '</ul>',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });

</script>
@endsection

@endsection
