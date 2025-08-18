@extends('layouts.master')

@section('title', 'Edit Contract')

@section('css')
<style>

.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%; /* Ensure the table takes full width */
    min-width: 600px; /* Set a minimum width to ensure horizontal scrolling */
}
</style>
@endsection

@section('content')
<div class="row">
    <x-page-title title="REKAP {{__('Sales Invoice')}}" pagetitle="Edit REKAP {{__('Sales Invoice')}}" />
    <hr>
    <div class="container content">
        <h2>{{__('Sales Invoice')}} Edit</h2>
        @if ($salesInvoice->reason)
            <h5 style="color: red">Alasan edit: {{$salesInvoice->reason}}</h5>
        @endif
        <form id="print-form" target="_blank" action="{{ route('sales_invoice.print', $salesInvoice->id) }}" method="GET"
            style="display:inline;">
            <button type="submit" class="mb-3 btn btn-dark" @if(!in_array('print', $privileges)) disabled @endif>
                Print SI</button>
        </form>
        <form id="print-form" target="_blank" action="{{ route('sales_invoice.print.netto', $salesInvoice->id) }}" method="GET"
            style="display:inline;">
            <button type="submit" class="mb-3 btn btn-dark" @if(!in_array('print', $privileges)) disabled @endif>
                Print SI Netto</button>
        </form>
        <form id="print-form" target="_blank" action="{{ route('sales_invoice.print.do', $salesInvoice->id) }}" method="GET"
            style="display:inline;">
            <button type="submit" class="mb-3 btn btn-dark" @if(!in_array('print', $privileges)) disabled @endif>
                Print DO</button>
        </form>
        <form id="print-form" target="_blank" action="{{ route('sales_invoice.print.all', $salesInvoice->id) }}" method="GET"
            style="display:inline;">
            <button type="submit" class="mb-3 btn btn-dark" @if(!in_array('print', $privileges)) disabled @endif>
                Print Semua</button>
        </form>
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

        <form id="contract-form" action="{{ isset($salesInvoice) ? route('transaction.sales_invoice_recap.update', $salesInvoice->id) : route('transaction.sales_invoice_recap.store') }}" method="POST">
            @csrf
            @if(isset($salesInvoice)) @method('PUT') @endif

            <div class="card mb-3">
                <input type="hidden" id="checkHPP" value="0">
                <div class="card-header">REKAP {{__('Sales Invoice')}} {{__('Information')}} : <strong>{{$salesInvoice->sales_invoice_number}}</strong> </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                                <input type="hidden" name="edit_reason" id="edit_reason">
                                {{-- <div class="form-group">
                                    <label for="search">{{__('Search Customer')}}</label>
                                    <input type="text" id="search" class="form-control" placeholder="Search by Customer Code, Name, or Address">
                                    <div id="search-results" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                </div>
                                <br> --}}
                                <div class="form-group">
                                    <label for="customer_code">{{__('Customer Code')}}</label>
                                    <input type="text" name="customer_code" id="customer_code" class="form-control" readonly required value="{{ old('customer_code', $salesInvoice->customer_code) }}">
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="customer_name">{{__('Customer Name')}}</label>
                                    <input type="text" name="customer_name" id="customer_name" class="form-control" readonly value="{{ old('customer_name', $salesInvoice->customers->customer_name) }}" required>
                                </div>
                                <br>
                                <div class="form-group">
                                    <label for="address">{{__('Address')}}</label>
                                    <input type="text" name="address" id="address" class="form-control" value="{{ old('customer_name', $salesInvoice->customers->address) }}" readonly>
                                </div>
                            {{-- <div class="form-group">
                                <label for="sales_invoice_number">{{__('Sales Invoice Number')}}</label>
                                <input type="text" name="sales_invoice_number" class="form-control" readonly required value="{{ old('sales_invoice_number', $salesInvoice->sales_invoice_number ?? $sales_invoice_number) }}">
                            </div> --}}
                            <br>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="notes">Note</label>
                                <textarea name="notes" class="form-control" rows="5" value="{{ old('notes', $salesInvoice->notes) }}">{{ old('notes', $salesInvoice->notes) ?? ''}}</textarea>

                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" name="document_date" id= "document_date" class="form-control date-picker" required value="{{ old('document_date', $salesInvoice->document_date ?? date('Y-m-d')) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="delivery_date">{{__('Delivery Date')}}</label>
                                <input type="date" name="delivery_date" class="form-control date-picker" required value="{{ old('delivery_date', $salesInvoice->delivery_date ?? date('Y-m-d')) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}} Piutang</label>
                                <input type="date" name="due_date" class="form-control date-picker" required value="{{ old('due_date', $salesInvoice->due_date ?? date('Y-m-d')) }}">
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
                                <input type="text" oninput="formatNumber(this)" id="disc_nominal" name="disc_nominal" class="form-control text-end nominal" required value="{{ old('disc_nominal', number_format($salesInvoice->disc_nominal,0,'.',','))}}">
                            </div>
                            <br>
                            {{-- <div class="form-group">
                                <label for="tax">Tax</label>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="tax" name="tax" required>
                                        @foreach ($taxs as $tax)
                                            <option value="{{$tax->tax_code}}">{{$tax->tax_name.' ('.$tax->tax_code.')'}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            <div class="form-group">
                                <label for="tax">Tax</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" value="PPN / VAT" readonly>
                                    <select hidden class="form-select" id="tax" name="tax">
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $salesInvoice->tax }}" {{ old('tax', $salesInvoice->tax) === $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="tax">{{__('Revenue Tax')}}</label>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="tax_revenue" name="tax_revenue">
                                        <option value="0">Tidak kena pajak</option>
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $tax->tax_code }}" {{ $salesInvoice->tax_revenue_tariff == $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
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

            <!-- Card for {{__('Sales Invoice')}} Details -->
            <div class="card mb-3">
                <div class="card-header">{{__('Sales Invoice')}} Details</div>
                <div class="card-body">
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <td style="min-width: 270px">{{__('Sales Order Number')}}</td>
                            <td style="min-width: 270px">{{__('Warehouse')}}</td>
                            <td style="min-width: 430px">{{__('Item')}}</td>
                            <td style="min-width: 150px">Unit</td>
                            <td style="min-width: 150px">Qty</td>
                            <td style="min-width: 200px">{{__('Price')}}</td>
                            <td style="min-width: 150px">Disc (%)</td>
                            <td style="min-width: 200px">{{__('Discount')}}</td>
                            <td style="min-width: 200px">Nominal</td>
                            <td>Action</td>
                        </thead>
                        <tbody id="parentTbody">
                            @foreach ($salesInvoiceDetails as $index => $detail)
                            <tr>
                                <td>
                                    <input type="text" name="details[{{ $index }}][sales_order_number]" class="form-control" value="{{ $detail->so?$detail->so->sales_order_number:'' }}" readonly />
                                    <input type="hidden" name="details[{{ $index }}][so_id]" value="{{ $detail->so_id??'' }}">
                                </td>
                                <td>
                                    <input type="hidden" name="details[{{ $index }}][warehouse_code]" value="{{ $detail->warehouse_code }}" readonly />
                                    <input type="text" name="details[{{ $index }}][warehouse_name]" class="form-control" value="{{ $warehouses->where('warehouse_code',$detail->warehouse_code)->first()->warehouse_name }}" readonly />
                                </td>
                                <td>
                                    <input type="hidden" id="item_code_{{$index}}" name="details[{{ $index }}][item_id]" class="form-control" value="{{ $detail->item_id }}" readonly />
                                    <input type="text" name="details[{{ $index }}][item_name]" class="form-control" value="{{ $detail->items->item_name }}" readonly />
                                </td>
                                <td>
                                    {{-- <input type="hidden" name="details[{{ $index }}][unit]" class="form-control" value="{{ $detail->unit }}" readonly />
                                    <input type="text" name="details[{{ $index }}][unit_name]" class="form-control" value="{{ $detail->units->unit_name }}" readonly /> --}}
                                    <select id="unit_{{$index}}" name="details[{{$index}}][unit]" class="form-control unit-dropdown">
                                        @php
                                            // Filter all itemDetails rows matching $detail->item_id
                                            $id = $itemDetails->toArray();
                                            $matchingItems = array_filter($id, fn($e) => $e['item_code'] == $detail->item_id);

                                            // Collect all unit_conversion values into a single array
                                            $allowedUnits = [];
                                            foreach ($matchingItems as $item) {
                                                $units = is_array($item['unit_conversion']) ? $item['unit_conversion'] : explode(',', $item['unit_conversion']);
                                                $allowedUnits = array_merge($allowedUnits, $units);
                                            }
                                            // Remove duplicates
                                            $allowedUnits = array_unique($allowedUnits);
                                        @endphp
                                        @foreach ($itemUnits as $unit)
                                            @if (in_array($unit->unit, $allowedUnits))
                                                <option value="{{ $unit->unit }}" @if($detail->unit == $unit->unit) selected @endif>{{ $unit->unit_name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="hidden" id="conversion_value_{{$index}}" name="details[{{ $index }}][base_qty]" value="{{ $detail->base_qty }}" readonly />
                                    <input type="number" id="qty_{{$index}}" name="details[{{ $index }}][qty]" class="form-control" max="{{ $detail->qty }}" value="{{ $detail->qty }}" min="1"/>
                                </td>
                                <td>
                                    <input type="text" id="price_{{$index}}" name="details[{{ $index }}][price]" oninput="formatNumber(this)" class="form-control text-end" value="{{ number_format($detail->price,0,'.',',') }}" />
                                </td>
                                <td >
                                    <input type="text" id="disc_percent_{{$index}}" name="details[{{ $index }}][disc_percent]" oninput="formatNumber(this)" class="form-control text-end" value="{{ number_format($detail->disc_percent,0,'.',',') }}"  />
                                </td>
                                <td >
                                    <input type="text" id="disc_nominal_{{$index}}" name="details[{{ $index }}][disc_nominal]" oninput="formatNumber(this)"  class="form-control text-end" value="{{ number_format($detail->disc_nominal,0,'.',',') }}"  />
                                </td>
                                <td >
                                    <input type="text" id="nominal_{{$index}}" name="details[{{ $index }}][nominal]"  class="form-control text-end nominal" value="{{ number_format($detail->nominal,0,'.',',') }}"  readonly/>
                                </td>
                                <td id="pay-row-{{ $index }}">
                                    <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    </div>
                    {{-- <button type="button" class="btn btn-secondary mt-3" id="addRow">{{__('Select Document')}}</button> --}}
                    <button type="button" onclick="addNewRow()" class="btn btn-primary mt-3">Tambah Barang</button>
                </div>
            </div>

            @if($editable)
            <div class="form-group submit-btn mb-3">
                <button type="button" onclick="confirmEdit(event,'{{ $salesInvoice->id }}')" class="btn btn-primary" @if(!in_array('update', $privileges)) disabled @endif>Update {{__('Sales Invoice')}}</button>
            </div> @endif
            <a href="{{route('transaction.sales_invoice_recap')}}" class="btn btn-secondary mb-3">Back</a>
        </form>
        @if($editable)
        <form id="delete-form" action="{{ route('transaction.sales_invoice_recap.destroy', $salesInvoice->id) }}" method="POST" style="display:inline;" >
            @csrf
            @method('POST')
            <input type="hidden" name="reason" id="deletion-reason">
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $salesInvoice->id }}')"
                @if(!in_array('delete', $privileges)) disabled @endif
            ><i class="material-icons-outlined">delete</i></button>
        </form>
    @endif
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
            window.location.href = "{{ route('sales_invoice_recap.sales_invoice') }}";
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
let rowCount = {{ isset($salesInvoice) ? count($salesInvoice->details) : 1 }};
let items = @json($items);

function confirm(event, id) {
    event.preventDefault(); // Prevent form submission
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0c6efd',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Approve!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('approve-form').submit(); // Submit the form
        }
    });
    }

    function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this sales invoice?',
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
                return 'You need to provide a reason for cancellation!';
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

    function confirmEdit(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to edit this sales invoice?',
            icon: 'warning',
            input: 'select', // Change input type to 'select'
            inputOptions: {
                'Barang tidak ketemu': 'Barang tidak ketemu',
                'Truk full': 'Truk full',
                'Pelanggan tidak mau': 'Pelanggan tidak mau',
                'Ekspedisi tidak mau': 'Ekspedisi tidak mau',
                'Salah Unit':'Salah Unit',
                'Tambah Barang':'Tambah Barang',
            },
            inputPlaceholder: 'Select a reason',
            showCancelButton: true,
            confirmButtonText: 'Yes, edit it!',
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to select a reason!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const reason = result.value; // Get the selected value
                document.getElementById('edit_reason').value = reason;
                document.getElementById('contract-form').submit();
            }
        });
    }

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


        document.querySelector(`input[name="details[${rowId}][item_id]"]`).value = item.item_code;
        document.querySelector(`input[name="details[${rowId}][item_name]"]`).value = item.items.item_name;
        document.querySelector(`input[name="details[${rowId}][warehouse_code]"]`).value = item.items.warehouses?item.items.warehouses.warehouse_code:'';
        document.querySelector(`input[name="details[${rowId}][warehouse_name]"]`).value = item.items.warehouses?item.items.warehouses.warehouse_name:'';

        let units = [];
        item.item_details.forEach(element => {
            if(element.department_code==item.department_code){
                units.push(element.unit_conversion);
            }
        });

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

    function updatePrice() {
        const rowId = this.closest('tr').getAttribute('data-row-id');
        const itemCode = document.getElementById(`item_code_${rowId}`).value;

        const itemDetail = itemDetails.find(detail => detail.item_code === itemCode);
            // console.log(itemDetail);
    }

    function addNewRow() {
        const detailsTableBody = document.querySelector('#parentTbody');
        const newRow = document.createElement('tr');
        newRow.setAttribute('data-row-id', rowCount); // Set unique row identifier
        const currentRow = rowCount;
        newRow.innerHTML = `
            <td>
                <input type="text" name="details[${rowCount}][sales_order_number]" class="form-control" value="" readonly />
                <input type="hidden" name="details[${rowCount}][so_id]" class="form-control" value="" readonly />
            </td>
            <td>
                <input type="hidden" name="details[${rowCount}][warehouse_code]" value="" />
                <input type="text" name="details[${rowCount}][warehouse_name]" class="form-control" value="" readonly />
            </td>
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
                <select id="unit_${rowCount}" name="details[${rowCount}][unit]" class="form-control unit-dropdown">
                    @foreach ($itemUnits as $unit)
                        <option value="{{$unit->unit}}">{{$unit->unit_name}}</option>
                    @endforeach
                </select>
                <input type="hidden" id="conversion_value_${rowCount}" name = "details[${rowCount}][conversion_value]" />
            </td>
            <td>
                <input type="number" id="qty_${rowCount}" name="details[${rowCount}][qty]" class="form-control" value="1" min="1"  required placeholder="Quantity">
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
                <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">remove</i></button>
            </td>
        `;

        detailsTableBody.appendChild(newRow);
        setupItemSearch(rowCount);
        setupUnitChangeListener(currentRow);
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
        calculateTotals();
        addInputListeners();
        rowCount++; // Increment row count for the next row
    }



    function calculateTotals() {
        let total = 0;
        const disc_nominal = document.getElementById('disc_nominal').value.replace(/,/g, '') || 0;

        document.querySelectorAll('.nominal').forEach(function (input) {

            input.value = input.value.replace(/,/g, ''); // Remove any thousand separators
            if(input.id=='disc_nominal'){
                total -= parseFloat(input.value) || 0;
                console.log(total);

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

    let itemDetails = @json($itemDetails);
    function setupUnitChangeListener(rowNumber) {
        // Construct the IDs based on the row number
        var selectId = 'unit_' + rowNumber;
        var hiddenInputId = 'conversion_value_' + rowNumber;



        document.getElementById(selectId).addEventListener('change', function() {
            var itemCode = document.getElementById(`item_code_${rowNumber}`).value;
            var selectedOption = this.options[this.selectedIndex];
            var conversionValue = itemDetails.find((i)=>i.item_code == itemCode&&i.unit_conversion ==selectedOption.value).conversion;
            document.getElementById(hiddenInputId).value = conversionValue;
            updateNominalValue(rowNumber);
            calculateTotals();
        });
    }

    function addInputListeners() {
        document.querySelectorAll('.nominal').forEach(function (input) {
            input.addEventListener('change', function () {
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
    for (let i = 0; i < rowCount; i++) {
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
            setupUnitChangeListener(i);

    }

    document.querySelector('#dynamicTable').addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        rowCount--; // Decrement row count
        }
    });

    $("#contract-form").on("submit",function(e) {
        let formData = new FormData(this);
        let aa = true;
        if($("#checkHPP").val() == 1 ||$("#checkHPP").val() == "1"){
            aa = false;
        }
        if(aa){
            e.preventDefault();
            formData.set("_method","POST");
            $.ajax({
                url: "{{ route('getStockByDate') }}",
                type: "POST",
                data: formData,
                success: function (rs) {
                    if(rs.length > 0){
                        let itName = "";
                        rs.forEach((a) => itName += "<li>" + a.item_name + " dengan sisa stok: " + a.stock + "</li>");
                        Swal.fire({
                            title: 'Tidak bisa disimpan!',
                            html: "Item berikut qty tidak mencukupi<br/>"+itName,
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
    });
</script>
@endsection
@endsection
