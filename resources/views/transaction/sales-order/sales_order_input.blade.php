@extends('layouts.master')

@section('title', 'Input Sales Order')
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
    <x-page-title title="{{__('Sales Order')}}" pagetitle="{{__('Sales Order')}} Input" />
    <hr>
    <div class="container content">
        <h2>{{__('Sales Order')}} Input</h2>
        <form id="bank-cash-out-form" action="{{ isset($salesOrder) ? route('transaction.sales_order.update', $salesOrder->id) : route('transaction.sales_order.store') }}" method="POST">
            @csrf
            @if(isset($salesOrder)) @method('PUT') @endif
            <input type='hidden' id="checkHPP" name='checkHPP' value=0 />
            <input type="hidden" name="token" id="token" value="{{$token}}">
            <div class="card mb-3">
                <div class="card-header">{{__('Sales Order')}} {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">

                                <div class="form-group">
                                    <label for="search">{{__('Search Customer')}}</label>
                                    <input type="text" id="search" class="form-control" placeholder="Search by Customer Code, Name, or Address">
                                    <div id="search-results" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="customer_code">{{__('Customer Code')}}</label>
                                    <input type="text" name="customer_code" id="customer_code" class="form-control" readonly required value="{{old("customer_code",'')}}">
                                    <input type="hidden" name="category_customer" id="category_customer" class="form-control" readonly>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="customer_name">{{__('Customer Name')}}</label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control" readonly required value="{{old("customer_name",'')}}">
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="address">{{__('Address')}}</label>
                                    <input type="text" name="address" id="address" class="form-control" readonly value="{{old("address",'')}}">
                                </div>
                            {{-- <div class="form-group">
                                <label for="sales_order_number">{{__('Sales Order Number')}}</label>
                                <input type="text" name="sales_order_number" class="form-control" readonly required value="{{ old('sales_order_number', $salesOrder->sales_order_number ?? $sales_order_number) }}">
                            </div> --}}
                            <br>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="notes">Note</label>
                                <textarea name="notes" class="form-control" rows="5">{{ old('notes', $salesOrder->notes ?? '') }}</textarea>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date"  name="document_date" class="form-control date-picker" required value="{{ old('document_date', $salesOrder->document_date ?? date('Y-m-d')) }}" id="document_date">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="delivery_date">{{__('Delivery Date')}}</label>
                                <input type="date" name="delivery_date" class="form-control date-picker" required value="{{ old('delivery_date', $salesOrder->delivery_date ?? date('Y-m-d')) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" name="due_date" class="form-control date-picker" required value="{{ old('due_date', $salesOrder->due_date ?? date('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department_code">{{__('Department Code')}}</label>
                                <input type="hidden" name="department_code" class="form-control" value="{{$department_TDS}}">
                                <input type="text" name="department_name" id="department_name" class="form-control" value="{{$department_TDSn->department_name}}" readonly>
                                {{-- <div class="input-group mb-3">
                                    <select class="form-select" id="department_code" name="department_code" required readonly>
                                        @foreach ($departments as $department)
                                            <option value="{{$department_TDS}}" {{ $department->department_code == $department_TDS ? 'selected' : '' }}>{{$department->department_name}}</option>
                                        @endforeach
                                    </select>
                                </div> --}}
                            </div>
                            <br>
                            <div class="form-group d-none">
                                <label for="disc_nominal">{{__('Discount')}}</label>
                                <input type="text" oninput="formatNumber(this)" name="disc_nominal" id="disc_nominal" class="form-control text-end nominal" required value="0">
                            </div>
                            <br>
                            <div class="form-group d-none">
                                <label for="exampleInputEmail1" class="form-label">PBR</label>
                                <div class="form-group">
                                    <input type="radio" name="pbr" value="yes" id="pbr_yes" required>
                                    <label for="pbr_yes">Yes</label><br>

                                    <input type="radio" name="pbr" value="no" id="pbr_no" checked>
                                    <label for="pbr_no">No</label><br>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <select hidden class="form-select" id="company_code" name="company_code" required>
                                        @foreach ($companies as $company)
                                            <option value="{{$company->company_code}}">{{$company->company_name.' ('.$company->company_code.')'}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card for Sales Order Details -->
            <div class="card mb-3">
                <div class="card-header">{{__('Sales Order')}} Details</div>
                <div class="card-body">
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <input type="hidden" id="count_rows" name="count_rows" value="{{old('count_rows',0)}}">
                    <div style="overflow-x: auto;">
                    <table class="table" id="cash-out-details-table">
                        <thead>
                            <tr>
                                <th style="min-width: 430px">{{__('Item')}}</th>
                                <th>Cari</th>
                                <th style="min-width: 150px">Qty</th>
                                <th style="min-width: 150px">Unit</th>
                                <th style="min-width: 200px">{{__('Price')}} / Unit Dasar</th>
                                <th style="min-width: 150px">{{__('Discount')}} (%)</th>
                                <th style="min-width: 200px">{{__('Discount')}}</th>
                                <th style="min-width: 200px">Nominal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemRows">
                            <tr data-row-id="0">
                                <td>
                                    <div class="form-group">
                                        <input type="hidden" class="form-control item-input" name="details[0][item_id]" id="item_code_0" placeholder="{{__('Search Item')}}">
                                        <input type="text" class="form-control item-input" name="details[0][item_name]" id="item-search-0" placeholder="{{__('Search Item')}}">
                                        <div id="item-search-results-0" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                            <!-- Search results will be injected here -->
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm cust-item-btn" data-row-id="0"><i class="material-icons-outlined">search</i></button>
                                </td>
                                <td>
                                    <input type="text"  name="details[0][qty]" id="qty_0" class="form-control" value="1" min="1" required placeholder="Quantity">
                                </td>
                                <td>
                                    <select id="unit_0" name="details[0][unit]" class="form-control unit-dropdown">
                                        @foreach ($itemUnits as $unit)
                                            <option value="{{$unit->unit}}">{{$unit->unit_name}}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="conversion_value_0" name="details[0][conversion_value]" />
                                </td>
                                <td>
                                    <input type="text" name="details[0][price]"
                                    oninput="formatNumber(this)" id="price_0" class="form-control text-end" value="0"  required placeholder="{{__('Price')}}">
                                </td>
                                <td>
                                    <input type="text" name="details[0][disc_percent]" max="100" id="disc_percent_0" oninput="formatNumber(this)" class="form-control text-end" value="0" required placeholder="% Discount">
                                </td>
                                <td>
                                    <input type="text" name="details[0][disc_nominal]" oninput="formatNumber(this)" id="disc_nominal_0" class="form-control text-end" value="0" required placeholder="Discount">
                                </td>
                                <td>
                                    <input type="text" name="details[0][nominal]" oninput="formatNumber(this)" readonly id="nominal_0" class="form-control text-end nominal" value="0" required placeholder="Discount">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <button type="button" id="add-row" class="btn btn-primary mt-3">Add Detail</button>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Sales Order')}}</button>
            </div>
        </form>
    </div>
    <div class="modal fade" id="custItemModal" tabindex="-1" aria-labelledby="custItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="custItemModalLabel">History Harga Jual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered" id="custItemTable">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Permintaan Penjualan</th>
                                <th>Tanggal Permintaan</th>
                                <th>Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be populated via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
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
<script>

document.addEventListener('DOMContentLoaded', function () {
            // Check if the success message is present
            @if(session('success'))
                // Show SweetAlert confirmation modal
                Swal.fire({
                    title: '{{__('Sales Order')}} Created',
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
                            window.location.href = "{{ route('transaction.sales_order.print', ['id' => ':id']) }}".replace(':id', id);
                        }
                    }
                });
            @endif
        });

var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);
var itemDetails = @json($itemDetails);

let items = @json($items);
let activeIndex = -1;
function setupItemSearch(rowId) {
     // Track the active item in the dropdown
    const searchInput = document.getElementById(`item-search-${rowId}`);
    const resultsContainer = document.getElementById(`item-search-results-${rowId}`);

    // Input event listener for filtering
    searchInput.addEventListener('input', function() {
        activeIndex = -1; // Reset active index on new input
        let query = this.value.toLowerCase();
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';

        if (query.length > 0) {
            let filteredItems = items.filter(item =>
                item.item_code.toLowerCase().includes(query) ||
                item.items.item_name.toLowerCase().includes(query)
            );

            if (filteredItems.length > 0) {
                resultsContainer.style.display = 'block';
                filteredItems.forEach((item, index) => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.innerHTML = `
                        <small><strong>${item.items.item_name}</strong> (${item.item_code})</small>
                    `;

                    listItem.addEventListener('click', function(e) {
                        e.preventDefault();
                        selectItem(item, rowId);
                    });

                    resultsContainer.appendChild(listItem);
                });
            }
        }
    });

    // Keydown event listener for navigation
    searchInput.addEventListener('keydown', function(e) {
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

// Helper function to update active item
function updateActiveItem(items) {
    items.forEach((item, index) => {
        item.classList.toggle('active', index === activeIndex);
    });
    if (activeIndex >= 0) {
        items[activeIndex].scrollIntoView({ block: 'nearest' });
    }
}

// Helper function to handle item selection
function selectItem(item, rowId) {
    let units = [];

    item.item_details.forEach(element => {
        if(element.department_code==item.department_code&&element.status==true){
            units.push(element.unit_conversion);
        }
    });

    document.querySelector(`input[name="details[${rowId}][item_id]"]`).value = item.item_code;
    document.querySelector(`input[name="details[${rowId}][item_name]"]`).value = item.items.item_name;

    const unitSelect = document.getElementById(`unit_${rowId}`);
    Array.from(unitSelect.options).forEach(option => {
        option.style.display = units.includes(option.value) ? 'block' : 'none';
    });

    unitSelect.value = item.unit;
    document.getElementById(`price_${rowId}`).value = item.sales_price.split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    let selectedUnit = unitSelect.value;
    let conversionDetail = itemDetails.find(i => i.item_code === item.item_code && i.unit_conversion === selectedUnit);
    console.log(conversionDetail);

    let conversionValue = conversionDetail ? conversionDetail.conversion : 1;

    document.getElementById(`conversion_value_${rowId}`).value = conversionValue;
    document.getElementById(`nominal_${rowId}`).value =
        (item.sales_price * conversionValue).toString().split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    document.getElementById(`item-search-results-${rowId}`).style.display = 'none';
    calculateTotals();
    addInputListeners();
}

// Add CSS for active item (add this once in your main script)
const style = document.createElement('style');
style.textContent = `
    .list-group-item.active {
        background-color: blue;
        border-color: #dee2e6;
    }
`;
document.head.appendChild(style);
    function setupUnitChangeListener(rowNumber) {
        // Construct the IDs based on the row number
        var selectId = 'unit_' + rowNumber;
        var hiddenInputId = 'conversion_value_' + rowNumber;



        document.getElementById(selectId).addEventListener('change', function() {
            var itemCode = document.getElementById(`item_code_${rowNumber}`).value;
            var selectedOption = this.options[this.selectedIndex];
            var conversionValue = itemDetails.find((i)=>i.item_code == itemCode&&i.unit_conversion ==selectedOption.value).conversion;
            document.getElementById(hiddenInputId).value = conversionValue;
        });
    }
    let rowCount = $('#itemRows tr').length;
    console.log(rowCount);

    for (let i = 0; i < rowCount; i++) {

        setupItemSearch(i);
        setupUnitChangeListener(i);
        document.getElementById(`item_code_${i}`).addEventListener('change', updatePrice);
        document.getElementById(`qty_${i}`).addEventListener('input', function() {
            updateNominalValue(i); // Call the function when the event occurs
        });

        document.getElementById(`price_${i}`).addEventListener('input', function() {
            updateNominalValue(i); // Call the function when the event occurs
        });
        document.getElementById(`disc_percent_${i}`).addEventListener('input', function() {
            updateNominalValue(i); // Call the function when the event occurs
        });

        document.getElementById(`disc_nominal_${i}`).addEventListener('input', function() {
            updateNominalValue(i); // Call the function when the event occurs
        });

        // Customer Items button listener (moved from event delegation to per-row attachment)
        const custItemBtn = document.querySelector(`#cash-out-details-table tr[data-row-id="${i}"] .cust-item-btn`);
        if (custItemBtn) {
            custItemBtn.addEventListener('click', function() {
                const itemId = document.getElementById(`item_code_${i}`).value;
                const customerCode = document.getElementById('customer_code').value;

                if (!itemId || !customerCode) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Please select both customer and item first!',
                    });
                    return;
                }

                // AJAX call
                $.ajax({
                    url: "{{ route('sales_order.custItem') }}",
                    method: 'POST',
                    data: {
                        item_id: itemId,
                        customer_code: customerCode,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        const tbody = document.querySelector('#custItemTable tbody');
                        tbody.innerHTML = '';

                        if (response.length > 0) {
                            response.forEach((item, index) => {
                                console.log(item);

                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${index + 1}</td>
                                    <td>${item.sales_order_number || '-'}</td>
                                    <td>${item.document_date || '-'}</td>
                                    <td>${item.price ? item.price.toString().split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '0'}</td>
                                `;
                                tbody.appendChild(row);
                            });
                        } else {
                            tbody.innerHTML = '<tr><td colspan="4">Tidak ada data barang untuk pelanggan yang dipilih</td></tr>';
                        }

                        const modal = new bootstrap.Modal(document.getElementById('custItemModal'));
                        modal.show();
                    },
                    error: function(xhr) {
                        console.log(xhr);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to fetch customer items. Please try again.',
                        });
                    }
                });
            });
        }

    }

    function updateNominalValue(row) {
        const qty = parseFloat(document.getElementById(`qty_${row}`).value) || 0;
        const price = document.getElementById(`price_${row}`).value.replace(/,/g, '') || 0;
        const disc_nominal_header = 0;
        const disc_percent = document.getElementById(`disc_percent_${row}`).value.replace(/,/g, '') || 0;
        const disc_nominal = document.getElementById(`disc_nominal_${row}`).value.replace(/,/g, '') || 0;
        const conversion = document.getElementById(`conversion_value_${row}`).value.replace(/,/g, '') || 0;
        // const disc_percent = parseFloat(document.getElementById(`disc_percent_${row}`).value.replace(/,/g, '')) || 0;
        // const disc_nominal = parseFloat(document.getElementById(`disc_nominal_${row}`).value.replace(/,/g, '')) || 0;

        const nominalInput = document.getElementById(`nominal_${row}`);
        const nominalValue = ((qty * price*parseFloat(conversion))-((qty * price*parseFloat(conversion))*disc_percent/100)-disc_nominal)+"";


        let formattedValue = nominalValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        nominalInput.value = formattedValue; // Update nominal value
        calculateTotals();
    }


    function formatNumber(input) {
        const cursorPosition = input.selectionStart;
        let value = input.value.replace(/[^0-9]/g, '').replace(/,/g, '');
        let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        input.value = formattedValue;
        setTimeout(() => {
            const newCursorPosition = formattedValue.length - (value.length - cursorPosition);
            try {
                input.setSelectionRange(newCursorPosition, newCursorPosition);
            } catch (e) {
                console.warn('Failed to set cursor position:', e);
            }
        }, 0);
    }
    function updatePrice() {
        const rowId = this.closest('tr').getAttribute('data-row-id');
        const itemCode = document.getElementById(`item_code_${rowId}`).value;

        const itemDetail = itemDetails.find(detail => detail.item_code === itemCode);
            // console.log(itemDetail);
    }



    function calculateTotals() {
        let total = 0;
        const disc_nominal = document.getElementById('disc_nominal').value.replace(/,/g, '') || 0;
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
    document.getElementById('disc_nominal').addEventListener('input',function(){
        calculateTotals();
    });
    function addInputListeners() {
        document.querySelectorAll('.nominal').forEach(function (input) {
            input.addEventListener('change', function () {

                calculateTotals(); // Calculate totals when any input changes
            });
        });
    }
    addInputListeners();

    // document.getElementById('tax').value = '';
    const customers = @json($customers);


    let activeIndexCust = -1; // Track the active customer in the dropdown

    document.getElementById('search').addEventListener('input', function () {
        activeIndexCust = -1; // Reset active index on new input
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';

        if (query.length > 0) {
            let filteredCustomers = customers.filter(c =>
                c.customer_code.toLowerCase().includes(query) ||
                c.customer_name.toLowerCase().includes(query) ||
                c.address.toLowerCase().includes(query)
            );

            if (filteredCustomers.length > 0) {
                resultsContainer.style.display = 'block';
                filteredCustomers.forEach((customer, index) => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.dataset.index = index; // Store index for reference
                    listItem.innerHTML = `
                        <strong>${customer.customer_code}</strong> -
                        ${customer.customer_name} <br>
                        <small>${customer.address} - ${customer.city}</small>`;
                    listItem.addEventListener('click', function (e) {
                        e.preventDefault();
                        selectCustomer(customer);
                    });
                    resultsContainer.appendChild(listItem);
                });
            }
        }
    });

    // Keydown event listener for navigation
    document.getElementById('search').addEventListener('keydown', function (e) {
        const resultsContainer = document.getElementById('search-results');
        const items = resultsContainer.querySelectorAll('.list-group-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (activeIndexCust < items.length - 1) {
                activeIndexCust++;
                updateActiveCustomer(items);
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeIndexCust > -1) {
                activeIndexCust--;
                updateActiveCustomer(items);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndexCust >= 0 && items[activeIndexCust]) {
                items[activeIndexCust].click(); // Trigger click event
            }
        }
    });

    // Helper function to update active customer
    function updateActiveCustomer(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndexCust);
        });
        if (activeIndexCust >= 0 && items[activeIndexCust]) {
            items[activeIndexCust].scrollIntoView({ block: 'nearest' });
        }
    }

    // Helper function to handle customer selection
    function selectCustomer(customer) {
        let customerId = customer.customer_code;
        document.getElementById('customer_code').value = customer.customer_code;
        document.getElementById('customer_name').value = customer.customer_name;
        document.getElementById('address').value = customer.address;
        document.getElementById('category_customer').value = customer.category_customer || '';
        document.getElementById('search-results').style.display = 'none';

        // Show all rows if no customer is selected
        if (!customerId) {
            $('#invoiceTable tbody tr').show();
        } else {
            // Hide rows that do not match the selected customer
            $('#invoiceTable tbody tr').each(function () {
                const customerIds = $(this).data('customer-id');
                let customerOrigin = customers.find(c => c.customer_code === customerIds);

                if (customerOrigin && customer.group_customer === customerOrigin.group_customer) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('#search')) {
            document.getElementById('search-results').style.display = 'none';
            document.getElementById('search').value=''; }});

        function updateCustomerInfo() {
            const customerSelect = document.getElementById('customer_code');
            const selectedOption = customerSelect.options[customerSelect.selectedIndex];

            // Get customer name and address from the selected option
            const customerName = selectedOption.getAttribute('data-customer-name');
            const address = selectedOption.getAttribute('data-address');

            // Set the values in the readonly fields
            document.getElementById('customer_name').value = customerName;
            document.getElementById('address').value = address;
        }
    // Initialize row count

    document.addEventListener('DOMContentLoaded', function () {

        // Function to add a new row
        function addNewRow() {
            const detailsTableBody = document.querySelector('#cash-out-details-table tbody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-row-id', rowCount); // Set unique row identifier
            const currentRow = rowCount;
            newRow.innerHTML = `
                <td>
                    <div class="input-group">
                        <input type="hidden" id="item_code_${rowCount}" class="form-control item-input" name="details[${rowCount}][item_id]" placeholder="{{__('Search Item')}}">
                        <input type="text" class="form-control item-input" name="details[${rowCount}][item_name]" id="item-search-${rowCount}" placeholder="{{__('Search Item')}}">
                        <div id="item-search-results-${rowCount}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                            <!-- Search results will be injected here -->
                        </div>
                    </div>
                </td>
                <td>
                    <button type="button" class="btn btn-info btn-sm cust-item-btn" data-row-id="${rowCount}"><i class="material-icons-outlined">search</i></button>
                </td>
                <td>
                    <input type="number" id="qty_${rowCount}" name="details[${rowCount}][qty]" class="form-control" value="1" min="1"  required placeholder="Quantity">
                </td>
                <td>
                    <select id="unit_${rowCount}" name="details[${rowCount}][unit]" class="form-control unit-dropdown">
                        @foreach ($itemUnits as $unit)
                            <option value="{{$unit->unit}}">{{$unit->unit_name}}</option>
                        @endforeach
                    </select>
                    <input type="hidden" id="conversion_value_${rowCount}" name = "details[${rowCount}][conversion_value]" />
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][price]" oninput="formatNumber(this)" class="form-control price-input text-end" id="price_${rowCount}" value="0" required placeholder="Price">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][disc_percent]" oninput="formatNumber(this)" id="disc_percent_${rowCount}" class="form-control text-end" value="0" required placeholder="% Discount">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][disc_nominal]" oninput="formatNumber(this)" id="disc_nominal_${rowCount}" class="form-control text-end" value="0" required placeholder="Discount">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][nominal]" oninput="formatNumber(this)" readonly id="nominal_${rowCount}" class="form-control text-end nominal" value="0" required placeholder="Discount">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                </td>
            `;

            detailsTableBody.appendChild(newRow);
            setupItemSearch(rowCount);
            setupUnitChangeListener(rowCount);
            document.getElementById(`item_code_${rowCount}`).addEventListener('change', updatePrice);

            document.getElementById(`qty_${currentRow}`).addEventListener('input', function() {
                updateNominalValue(currentRow); // Call the function when the event occurs
            });

            document.getElementById(`price_${currentRow}`).addEventListener('input', function() {
                updateNominalValue(currentRow); // Call the function when the event occurs
            });
            document.getElementById(`disc_percent_${currentRow}`).addEventListener('input', function() {
                updateNominalValue(currentRow); // Call the function when the event occurs
            });
            document.getElementById(`disc_nominal_${currentRow}`).addEventListener('input', function() {
                updateNominalValue(currentRow); // Call the function when the event occurs
            });
            // Customer Items button listener (moved from event delegation to per-row attachment)
            const custItemBtn = document.querySelector(`#cash-out-details-table tr[data-row-id="${currentRow}"] .cust-item-btn`);
            if (custItemBtn) {
                custItemBtn.addEventListener('click', function() {
                    const itemId = document.getElementById(`item_code_${currentRow}`).value;
                    const customerCode = document.getElementById('customer_code').value;

                    if (!itemId || !customerCode) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Oops...',
                            text: 'Harap pilih pelanggan dan barang terlebih dahulu!',
                        });
                        return;
                    }

                    // AJAX call
                    $.ajax({
                        url: "{{ route('sales_order.custItem') }}",
                        method: 'POST',
                        data: {
                            item_id: itemId,
                            customer_code: customerCode,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            const tbody = document.querySelector('#custItemTable tbody');
                            tbody.innerHTML = '';
                            console.log(response);

                            if (response.length > 0) {
                                response.forEach((item, index) => {
                                    console.log(item);

                                    const row = document.createElement('tr');
                                    row.innerHTML = `
                                        <td>${index + 1}</td>
                                        <td>${item.sales_order_number || '-'}</td>
                                        <td>${item.document_date || '-'}</td>
                                        <td>${item.price ? item.price.toString().split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',') : '0'}</td>
                                    `;
                                    tbody.appendChild(row);
                                });
                            } else {
                                tbody.innerHTML = '<tr><td colspan="4">No data found</td></tr>';
                            }

                            const modal = new bootstrap.Modal(document.getElementById('custItemModal'));
                            modal.show();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to fetch customer items. Please try again.',
                            });
                        }
                    });
                });
            }
            document.getElementById(`item-search-${currentRow}`).focus();
            calculateTotals();
            addInputListeners();
            rowCount++; // Increment row count for the next row


        }

        // Function to update price based on item and unit selections


        // Add event listener for adding a new row
        document.getElementById('add-row').addEventListener('click', addNewRow);

        // Event delegation for row removal
        document.querySelector('#cash-out-details-table').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                rowCount--; // Decrement row count
            }
        });

        // Initialize the first row
    });

    $(document).ready(function (x) {
        x = {
            headers: {
                "X-CSRF-TOKEN": "{{csrf_token()}}"
            }
        }
        $.ajaxSetup(x)
    });

    $("#bank-cash-out-form").on("submit",function(e) {
        let hasEmptyItemId = false;
        let isValid = true;
        const rows = document.querySelectorAll('#itemRows tr');
        const customer = document.getElementById('customer_code').value;
        console.log(customer);

        if(!customer||customer==''){
            isValid=false;
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Pelanggan belum dipilih!',
            });
        }
        // Loop through each row
        rows.forEach(row => {
            const itemIdInput = row.querySelector('input[name$="[item_id]"]'); // Match inputs ending with [item_id]
            if (!itemIdInput.value || itemIdInput.value.trim() === '') {
                hasEmptyItemId = true;
            }
        });

        // If any item_id is empty, show warning and prevent submission
        if (hasEmptyItemId) {
            isValid=false;
            Swal.fire({
                icon: 'warning',
                title: 'Oops...',
                text: 'Ada barang yang belum dipilih!',
            });
        }
        if(!isValid){
            e.preventDefault(); // Stop form submission
        }
    });

</script>
@endsection

@endsection
