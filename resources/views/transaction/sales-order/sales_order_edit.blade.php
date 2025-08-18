@extends('layouts.master')

@section('title', 'Edit Sales Order')



@section('content')
<div class="row">
    <x-page-title title="{{__('Sales Order')}}" pagetitle="Edit {{__('Sales Order')}}" />
    <hr>
    <div class="container content">
        <h2>Edit {{__('Sales Order')}}</h2>
        @if ($salesOrder->cancel_notes)
            <h5 style="color: red">Alasan batal: {{$salesOrder->cancel_notes}}</h5>
        @endif
        @if($salesOrder->status!='Cancelled')
        <form id="print-form" target="_blank" action="{{ route('transaction.sales_order.print', $salesOrder->id) }}" method="GET" style="display:inline;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
                Print SO</button>
        </form>
        <form id="print-form" target="_blank" action="{{ route('sales_order.print.netto', $salesOrder->id) }}" method="GET" style="display:inline;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif >
                Print SO Netto</button>
        </form>
        @endif
        @if($salesOrder->status!='Cancelled')
        <form id="print-form" action="{{ route('sales-order.print', $salesOrder->id) }}" method="GET" style="display:none;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
                Print Picking Order
            </button>
        </form>
        @endif
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

        <form id="contract-form" action="{{$editable ? route('transaction.sales_order.update', $salesOrder->id) : route('transaction.sales_order') }}" method="POST"
            >
            @csrf
            @if(!$editable) @method('GET')@else @method('PUT') @endif
            <input type='hidden' id="checkHPP" name='checkHPP' value=0 />
            <div class="mb-3 card">
                <div class="card-header">{{__('Sales Order')}} {{__('Information')}} : <strong>{{$salesOrder->sales_order_number}}</strong></div>
                <div class="card-body">
                    <div class="row">
                        {{-- <div class="col-md-12">
                            @if (!$editable)
                                    <h4><strong class="text-danger">This document can't be edited because used in other document</strong></h4>
                                @endif
                        </div> --}}
                        {{-- <hr> --}}
                        <div class="col-md-4">
                            <div class="form-group">

                            </div>
                            <div class="form-group">
                                <label for="search">{{__('Search Customer')}}</label>
                                <input type="text" id="search" class="form-control" placeholder="Search by Customer Code, Name, or Address">
                                <div id="search-results" class="list-group" style="display:none;"></div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_code">{{__('Customer Code')}}</label>
                                <input type="text" id="customer_code" class="form-control" name="customer_code" value="{{ old('customer_code', $salesOrder->customer_code) }}" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" id="customer_name" class="form-control" name="customer_name" value="{{ old('customer_name', $salesOrder->customers->customer_name) }}" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" id="address" class="form-control" name="address" value="{{ old('address', $salesOrder->customers->address) }}" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="sales_order_number">{{__('Sales Order Number')}}</label>
                                <input type="text" name="sales_order_number" class="form-control" readonly required value="{{ old('sales_order_number', $salesOrder->sales_order_number) }}">
                            </div>
                            <br>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="notes">Note</label>
                                <textarea name="notes" class="form-control" rows="5">{{ old('notes', $salesOrder->notes) }}</textarea>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required value="{{ old('document_date', $salesOrder->document_date) }}" id="document_date">
                            </div>
                            {{-- <br>
                            <div class="form-group">
                                <label for="eta_date">ETA Date</label>
                                <input type="date" id="eta_date" name="eta_date" class="form-control" required value="{{ old('eta_date', $salesOrder->eta_date) }}">
                            </div> --}}
                            <br>
                            <div class="form-group">
                                <label for="delivery_date">{{__('Delivery Date')}}</label>
                                <input type="date" id="delivery_date" name="delivery_date" class="form-control date-picker" required value="{{ old('delivery_date', $salesOrder->delivery_date) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" name="due_date" class="form-control" required value="{{ old('due_date', $salesOrder->due_date ?? date('Y-m-d')) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department_code">{{__('Department Code')}}</label>
                                <input type="hidden" name="department_code" class="form-control" value="{{$department_TDS}}">
                                <input type="text" name="department_name" id="department_name" class="form-control" value="{{$department_TDSn->department_name}}" readonly>
                            </div>
                            <br>
                            <div class="form-group d-none">
                                <label for="disc_nominal">{{__('Discount')}}</label>
                                <input type="text" id="disc_nominal" name="disc_nominal" oninput="formatNumber(this)" class="form-control nominal text-end" required value="{{ old('disc_nominal', number_format($salesOrder->disc_nominal,0,'.',',')) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="exampleInputEmail1" class="form-label">PBR</label>
                                <div class="form-group">
                                    <input type="radio" name="pbr" value="yes" id="pbr_yes" required @if ($salesOrder->is_pbr)checked @endif>
                                    <label for="pbr_yes">Yes</label><br>

                                    <input type="radio" name="pbr" value="no" id="pbr_no" @if (!$salesOrder->is_pbr) checked @endif>
                                    <label for="pbr_no">No</label><br>
                                </div>
                            </div>

                            {{-- <div class="form-group">
                                <label for="tax">Tax</label>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="tax" name="tax" required>
                                        @foreach ($taxs as $tax)
                                            <option value="{{ old('tax', $salesOrder->tax) }}">{{$tax->tax_name.' ('.$tax->tax_code.')'}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            <div class="form-group">
                                <div class="mb-3 input-group">
                                    <select hidden class="form-select" id="company_code" name="company_code" required>
                                        @foreach ($companies as $company)
                                            <option value="{{$company->company_code}}" {{ old('company_code', $salesOrder->company_code) == $company->company_code ? 'selected' : '' }}>{{$company->company_name.' ('.$company->company_code.')'}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card for Sales Order Details -->
            <div class="mb-3 card">
                <div class="card-header">Sales Order Details</div>
                <div class="card-body table-responsive">
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="sales-order-details-table">
                        <thead>
                            <tr>
                                <th style="min-width: 430px">{{__('Item')}}</th>
                                <th>Cari</th>
                                <th style="min-width: 150px">Qty</th>
                                <th style="min-width: 150px">Qty Belum Proses</th>
                                <th style="min-width: 150px">Unit</th>
                                <th style="min-width: 200px">{{__('Price')}} / Unit Dasar</th>
                                <th style="min-width: 150px">{{__('Discount')}} (%)</th>
                                <th style="min-width: 200px">{{__('Discount')}}</th>
                                <th style="min-width: 200px">Nominal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populate existing sales order details -->
                            @foreach($salesOrder->details as $index => $detail)
                                <tr data-row-id="{{ $index }}">
                                    <td>
                                        <input type="hidden" class="form-control item-input" name="details[{{ $index }}][item_id]" id="item_code_{{$index}}" placeholder="{{__('Search Item')}}" value="{{ $detail->item_id}}">
                                        <input type="text" class="form-control item-input" name="details[{{ $index }}][item_name]" id="item-search-{{ $index }}" placeholder="{{__('Search Item')}}" value="{{ $detail->items->item_name }}">
                                        <div id="item-search-results-{{ $index }}"  class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                            <!-- Search results will be injected here -->
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-info btn-sm cust-item-btn" data-row-id="{{$index}}"><i class="material-icons-outlined">search</i></button>
                                    </td>
                                    <td>
                                        @if($editable)
                                        <input type="number" name="details[{{ $index }}][qty]" id="qty_{{$index}}" class="form-control" value="{{ $detail->qty }}" min="1" required>
                                        @else
                                        <input type="number" name="details[{{ $index }}][qty]" id="qty_{{$index}}" class="form-control" value="{{ $detail->qty }}" min="1" required readonly>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" name="details[{{ $index }}][qty_left]" class="form-control" value="{{ $detail->qty_left }}" min="1" required readonly>

                                    </td>
                                    {{-- <td>
                                        @if($editable)
                                        <select class="form-select unit-select" id="unit_{{ $index }}" name="details[{{ $index }}][unit]" required>
                                            <option></option>
                                            @foreach ($itemUnits as $unit)
                                                <option value="{{ $unit->unit }}" {{ $unit->unit == $detail->unit ? 'selected' : '' }}>
                                                    {{ $unit->unit_name }} ({{ $unit->unit }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @else
                                        <input type="text" class="form-control" readonly value="{{ $detail->units->unit_name .' ('. $detail->units->unit .')' }}">
                                        <input type="hidden" id="unit_{{ $index }}" name="details[{{ $index }}][unit]" class="form-control" value="{{ $detail->unit }}">
                                        @endif
                                    </td> --}}
                                    <td>
                                        <select id="unit_{{$index}}" name="details[{{$index}}][unit]" class="form-control unit-dropdown">
                                            @foreach ($itemUnits as $unit)
                                                @if (in_array($unit->unit, array_column($detail->items->itemDetails->toArray(), 'unit_conversion')))
                                                    <option value="{{$unit->unit}}" @if ($detail->unit == $unit->unit) selected @endif>
                                                        {{$unit->unit_name}}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                        <input type="hidden" id="conversion_value_{{$index}}" name="details[{{$index}}][conversion_value]" value="{{$detail->base_qty}}" />
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $index }}][price]" oninput="formatNumber(this)" id="price_{{$index}}" class="form-control price-input text-end" id="price_{{ $index }}" value="{{ number_format($detail->price,0,'.',',') }}" required >
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $index }}][disc_percent]" oninput="formatNumber(this)" id="disc_percent_{{$index}}" class="form-control text-end" value="{{ number_format($detail->disc_percent,0,'.',',') }}" required max="100">
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $index }}][disc_nominal]" oninput="formatNumber(this)" id="disc_nominal_{{$index}}" class="form-control text-end" value="{{ number_format($detail->disc_nominal,0,'.',',') }}" required>
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $index }}][nominal]" oninput="formatNumber(this)" id="nominal_{{$index}}" class="form-control text-end nominal" value="{{ number_format($detail->nominal,0,'.',',') }}" required readonly>
                                    </td>
                                    {{-- <td>
                                        <select class="form-select" id="status" name="details[{{ $index }}][status]" value="{{ $detail->status }}">
                                            <option value="Not" {{ $detail->status == 'Not' ? 'selected' : '' }}>Not Ready</option>
                                            <option value="Ready" {{ $detail->status == 'Ready' ? 'selected' : '' }}>Ready</option>
                                        </select>
                                    </td> --}}
                                    <td>
                                        <button type="button" class="btn btn-danger remove-row" @if (!$editable)
                                        disabled
                                        @endif ><i class="material-icons-outlined">delete</i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <button type="button" id="add-row" class="btn btn-primary mt-3"
                    @if (!$editable)
                        disabled
                    @endif
                    >Add Row</button>
                </div>
            </div>
            @if($editable)
            <button type="submit" class="btn btn-primary mb-3" @if(!in_array('update', $privileges)) disabled @endif>Update Sales Order</button>
            @else
            <a class="btn btn-secondary mb-3" href="{{ route('transaction.sales_order') }}" >Back</a>
            @endif
        </form>
        @if($salesOrder->status!='Cancelled'&&$salesOrder->status!='Closed')
        <form id="cancel-form" action="{{ route('transaction.sales_order.cancel', $salesOrder->id) }}" method="POST" >
            @csrf
            @method('POST')
            <input type="hidden" name="reason" id="cancellation-reason">
            <button type="button" class="btn btn-danger mb-3 " onclick="confirmCancel(event,'{{ $salesOrder->id }}')"
                @if(!in_array('update', $privileges))
                    style="display: none"
                @endif
            >Pembatalan</button>
        </form>
        @endif
        @if($editable)
        <form id="delete-form" action="{{ route('transaction.sales_order.destroy', $salesOrder->id) }}" method="POST" >
            @csrf
            @method('POST')
            <input type="hidden" name="reason" id="deletion-reason">
            <button type="button" class="btn btn-sm btn-danger mb-3 " onclick="confirmDelete(event,'{{ $salesOrder->id }}')"
                @if(!in_array('delete', $privileges)) disabled @endif
            ><i class="material-icons-outlined">delete</i></button>
        </form>
        @endif

    </div>
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


@if(session('success'))
<script>
    Swal.fire({
        title: 'Success!',
        text: "{{ session('success') }}",
        icon: 'success',
        confirmButtonText: 'OK'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('transaction.sales_order') }}";
        }
    });
</script>
@endif

@if($errors->any())
<script>


    Swal.fire({
        title: 'Error!',
        html: `<ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>`,
        icon: 'error',
        confirmButtonText: 'OK'
    });
</script>
@endif
</div>

@section('scripts')
<script>
    var now = new Date(),
    maxDate = now.toISOString().substring(0,10);
    $('#document_date').prop('max', maxDate);
    let rowCount = {{ isset($salesOrder) ? count($salesOrder->details) : 1 }};
    let items = @json($items);
    var itemDetails = @json($itemDetails);
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
        if(element.department_code==item.department_code){
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

    let changeCustomer = @json($changeCustomer);
    let customers = @json($customers);

    document.getElementById('search').addEventListener('input', function () {
        let activeIndex = -1;
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        if (query.length > 0&&changeCustomer) {
            let filteredCustomers = customers.filter(c =>
                c.customer_code.toLowerCase().includes(query) ||
                c.customer_name.toLowerCase().includes(query) ||
                c.address.toLowerCase().includes(query));
            if (filteredCustomers.length > 0) {
                resultsContainer.style.display = 'block';
                filteredCustomers.forEach(customer => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.innerHTML = `
                        <strong>${customer.customer_code}</strong> -
                        ${customer.customer_name} <br>
                        <small>${customer.address} - ${customer.city}</small>`;
                    listItem.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('customer_code').value = customer.customer_code;
                        document.getElementById('customer_name').value = customer.customer_name;
                        document.getElementById('address').value = customer.address;
                        resultsContainer.style.display = 'none';

                        // for (let i = 0; i < rowCount; i++) {
                        //     document.getElementById(`item_code_${rowCount-1}`).value="";
                        //     // document.getElementById(`unit_${rowCount-1}`).value="";
                        //     document.getElementById(`price_${rowCount-1}`).value="";
                        // }

                    });
                    resultsContainer.appendChild(listItem);
                });
            }
        }
    });
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


function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this sales order?',
            icon: 'warning',
            input: 'text', // This adds an input field
            inputPlaceholder: 'Enter reason for deletion',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
            if (!value) {
                return 'You need to provide a reason for deletion!';
            }
        }
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value; // Get the input value
                document.getElementById('deletion-reason').value = reason;
                document.getElementById('delete-form').submit();
            }
        });
    }

    function confirmCancel(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Pembatalan Permintaan Penjualan?',
            text: 'Apakah anda yakin membatalkan Permintaan penjualan ini?',
            icon: 'warning',
            input: 'text', // This adds an input field
            inputPlaceholder: 'Masukkan alasan pembatalan',
            showCancelButton: true,
            confirmButtonText: 'Ya, batalkan!',
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Tidak jadi batal',
            inputValidator: (value) => {
            if (!value) {
                return 'Anda perlu masukkan alasan pembatalan!';
            }
        }
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value; // Get the input value
                document.getElementById('cancellation-reason').value = reason;
                document.getElementById('cancel-form').submit();
            }
        });
    }

// Initialize row count


    function updateNominalValue(row) {
        const qty = parseFloat(document.getElementById(`qty_${row}`).value) || 0;
        const price = document.getElementById(`price_${row}`).value.replace(/,/g, '') || 0;
        const disc_nominal_header = document.getElementById('disc_nominal').value.replace(/,/g, '') || 0;
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

    function updatePrice() {
        const rowId = this.closest('tr').getAttribute('data-row-id');
        const itemCode = document.getElementById(`item_code_${rowId}`).value;
        var itemDetails = @json($itemDetails);
        const itemDetail = itemDetails.find(detail => detail.item_code === itemCode);
            // console.log(itemDetail);
    }

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
        const custItemBtn = document.querySelector(`#sales-order-details-table tr[data-row-id="${i}"] .cust-item-btn`);
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


// Function to add a new row
    function addNewRow() {
        const detailsTableBody = document.querySelector('#sales-order-details-table tbody');
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-row-id', rowCount);
        const currentRow = rowCount;
        newRow.innerHTML = `
                <td>
                    <div class="input-group mb-3">
                        <input type="hidden" class="form-control item-input" name="details[${rowCount}][item_id]" id="item_code_${rowCount}" placeholder="{{__('Search Item')}}">
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
                    <input type="number" name="details[${rowCount}][qty]" id="qty_${rowCount}"  class="form-control" value="1" min="1"  required placeholder="Quantity">
                </td>
                <td>
                    <input type="number" name="details[${rowCount}][qty_left]" class="form-control" value="1" min="1"  readonly placeholder="Quantity Left">
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
                    <input type="text" name="details[${rowCount}][price]"  oninput="formatNumber(this)" class="form-control price-input text-end" id="price_${rowCount}" value="0" required placeholder="{{__('Price')}}">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][disc_percent]" oninput="formatNumber(this)" id="disc_percent_${rowCount}" class="form-control text-end" value="0" required placeholder="% Discount">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][disc_nominal]" oninput="formatNumber(this)" id="disc_nominal_${rowCount}" class="form-control text-end" value="0" required placeholder="Discount">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][nominal]" id="nominal_${rowCount}" class="form-control text-end nominal" readonly value="0" required placeholder="Discount">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                </td>
        `;

        detailsTableBody.appendChild(newRow);
        setupItemSearch(rowCount);
        setupUnitChangeListener(rowCount);
        console.log(rowCount);
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
        const custItemBtn = document.querySelector(`#sales-order-details-table tr[data-row-id="${currentRow}"] .cust-item-btn`);
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



        rowCount++; // Increment row count for the next row
    }

    // Add event listener for adding a new row
    document.getElementById('add-row').addEventListener('click', addNewRow);

    // Event delegation for row removal
    document.querySelector('#sales-order-details-table').addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        rowCount--; // Decrement row count
        }
    });

    {{--$("#contract-form").on("submit",function(e) {
        let formData = new FormData(this);
        let aa = true;
        if($("#checkHPP").val() == 1 ||$("#checkHPP").val() == "1"){
            aa = false;
        }
        if(aa){
            e.preventDefault();
            formData.set("_method","POST");
            $.ajax({
                url: "{{ route('hpp') }}",
                type: "POST",
                data: formData,
                success: function (rs) {
                    if(rs.length > 0){
                        let itName = "";
                        rs.forEach((a) => itName = itName.concat("<li>",a.item_name,"</li>"));
                        itName = "<ul>"+itName+"</ul>";
                        Swal.fire({
                            title: 'Tidak bisa disimpan!',
                            html: "Item berikut harga penjualan dibawah HPP<br/>"+itName,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }else{
                        $("#checkHPP").val(1);
                        formData.set("_method","PUT");
                        $("#contract-form").submit();
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    //let data = JSON.parse(xhr.responseText);
                },
                cache: false,
                contentType: false,
                processData: false,
            });
        }
    });--}}
</script>
@endsection
@endsection
