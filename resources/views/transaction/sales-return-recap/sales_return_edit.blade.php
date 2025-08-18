@extends('layouts.master')

@section('title', __('Sales Return'))

@section('css')
    <style>
        .dropdown-menu {
            position: absolute;
            z-index: 1000;
            background-color: white;
            border: 1px solid #ccc;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .item-list li {
            padding: 8px;
            cursor: pointer;
        }

        .item-list li:hover,
        .item-list li.highlight {
            background-color: #007bff;
            color: white;
        }

        .alert {
            display: none;
            margin-top: 5px;
            padding: 15px;
            border-radius: 5px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .fade {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .fade.show {
            opacity: 1;
        }

        .card {
            margin-bottom: 1rem;
            padding: 15px;
        }

        .form-group label {
            margin-bottom: 0.5rem;
        }

        .qtyw {
            min-width: 150px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            margin-bottom: 0.5rem;
        }

        .content {
            padding-top: 20px;
            padding-left: 10px;
            padding-right: 10px;
            max-width: 100%;
            width: calc(100% - 240px);
            margin-left: 240px;
        }

        .submit-btn {
            margin-top: 20px;
        }



        @media (max-width: 768px) {
            .content {
                padding-left: 10px;
                padding-right: 10px;
                padding-top: 10px;
                width: 100%;
                margin-left: 0;
            }
        }

        .info-button {
            background-color: #ccc;
            color: #555;
            border: none;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            cursor: pointer;
            line-height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        /* Mini modal styling */
        .mini-modal {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            color: #333;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
            max-width: 230px; /* Set max width */
            word-wrap: break-word; /* Ensure word wrapping */
            white-space: normal; /* Allow text to wrap */
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }

    </style>
@endsection

@section('content')
    <x-page-title title="Transaction" pagetitle="REKAP {{__('Sales Return')}}" />
    <hr>
    <div class="container content">
        <h2>REKAP {{__('Sales Return')}} Edit</h2>
        <form id="print-form" target="_BLANK" action="REKAP {{ route('transaction.sales_return_recap.print', $salesReturn->id) }}" method="GET"
            style="display:inline;">
            <button type="submit" class="mb-3 btn btn-dark" @if(!in_array('print', $privileges)) disabled @endif>
                Print SR</button>
        </form>
        <form id="po-form" action="{{ route('transaction.sales_return_recap.update', $salesReturn->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card mb-3">
                <div class="card-header">{{__('Sales Return')}} {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            {{-- <div class="form-group">
                                <label for="search">{{__('Search Customer')}}</label>
                                <input type="text" id="search" class="form-control"
                                    placeholder="Search by Customer Code, Name, or Address">
                                <div id="search-results" class="list-group"
                                    style="display:none; position:relative; z-index:1000; width:100%;"></div>
                            </div> --}}
                            <div class="form-group">
                                <label for="customer_code">{{__('Customer Code')}}</label>
                                <input type="text" name="customer_code" id="customer_code" class="form-control" readonly value="{{ $salesReturn->customer_code }}">
                            </div>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" readonly value="{{ $salesReturn->customers->customer_name }}">
                            </div>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly value="{{ $salesReturn->customers->address }}">
                            </div>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department_code">{{__('Sales Invoice Number')}}</label>
                                <input type="text" name="sales_invoice_number" id="sales_invoice_number" class="form-control" readonly value="{{ $salesReturn->sales_invoice_number }}" readonly>
                            </div>
                            <div class="form-group">
                                {{-- <label for="department_code">Department</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $salesReturn->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">{{__('Notes')}}</label>
                                <textarea name="notes" class="form-control" value="" rows="4">{{ $salesReturn->notes }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required
                                    value="{{ $salesReturn->document_date }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control date-picker" required
                                    value="{{$salesReturn->due_date}}">
                            </div>
{{--
                            <label for="exampleInputEmail1" class="form-label">Include</label>
                            <div class="form-group">
                                <input type="radio" name="include" value="yes" id="include_yes" {{ old('include') === 'yes' ? 'checked' : '' }} required>
                                <label for="include_yes">Yes</label><br>

                                <input type="radio" name="include" value="no" id="include_no" {{ old('include') === 'no' ? 'checked' : '' }}>
                                <label for="include_no">No</label><br>
                            </div> --}}

                            <div class="form-group">
                                <label for="tax">{{__('Tax')}}</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" value="PPN / VAT" readonly>
                                    <select hidden class="form-select" id="tax" name="tax">
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $salesReturn->tax_code }}" {{ old('tax', $salesReturn->tax) === $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="company_code" value="{{ $salesReturn->company_code }}"
                                class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>



            <div class="card mb-3">
                <div class="card-header">{{__('Sales Return')}} Details</div>
                <div class="card-body">
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="pr-details-table">
                        <thead>
                            <td style="min-width: 430px">{{__('Item')}}</td>
                            <td style="min-width: 150px">Qty</td>
                            <td style="min-width: 150px">Unit</td>
                            <td style="min-width: 200px">{{__('Price')}}</td>
                            <th style="min-width: 150px">{{__('Discount')}} (%)</th>
                            <th style="min-width: 200px">{{__('Discount')}}</th>
                            <th style="min-width: 200px">Nominal</th>
                            <th>Action</th>
                        </thead>
                        <tbody id="parentTbody">
                            @foreach ($salesReturn->details as $index => $detail)
                                <tr>
                                    <td>
                                        <input type="hidden" name="details[{{ $index }}][item_id]" id="item_code_{{$index}}" class="form-control" value="{{ $detail->item_id }}" readonly />
                                        <input type="text" name="details[{{ $index }}][item_name]" class="form-control" value="{{ $detail->items->item_name }}" readonly />
                                    </td>
                                    <td>
                                        <input type="hidden" id="conversion_value_{{$index}}" name="details[{{ $index }}][base_qty]" value="{{ $detail->base_qty }}" readonly />
                                        <input type="number" id="qty_{{$index}}" name="details[{{ $index }}][qty]" class="form-control" value="{{ $detail->qty }}" min="1"/>
                                    </td>
                                    <td>
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
                                        <input type="text" id="price_{{$index}}" name="details[{{ $index }}][price]" oninput="formatNumber(this)" class="form-control text-end" value="{{ number_format($detail->price,0,'.',',') }}" />
                                    </td>
                                    <td >
                                        <input type="text" id="disc_percent_{{$index}}" oninput="formatNumber(this)" name="details[{{ $index }}][disc_percent]" step="1" class="form-control text-end" value="{{ number_format($detail->disc_percent,0,'.',',') }}"  />
                                        <input type="hidden" oninput="formatNumber(this)" name="details[{{$index}}][disc_header]" id="disc_percent_0" class="form-control text-end" value="0"/>
                                    </td>
                                    <td >
                                        <input type="text" id="disc_nominal_{{$index}}" name="details[{{ $index }}][disc_nominal]"  class="form-control text-end" value="{{ number_format($detail->disc_nominal,0,'.',',') }}"  />
                                    </td>
                                    <td>
                                        <input type="text" id="nominal_{{$index}}" name="details[{{ $index }}][nominal]" class="form-control text-end nominal" value="{{ number_format(($detail->nominal),0,'.',',') }}" readonly />
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <button type="button" id="add-row" class="btn btn-primary mt-3">{{__('Add Item')}}</button>
                </div>
            </div>

            <button type="submit" class="mb-3 btn btn-primary" @if(!in_array('update', $privileges)) disabled @endif>Update {{__('Sales Return')}}</button>
        </form>
        <form id="delete-form" action="{{ route('transaction.sales_return.destroy', $salesReturn->id) }}" method="POST" style="display:inline;" >
            @csrf
            @method('POST')
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $salesReturn->id }}')"
                @if(!in_array('delete', $privileges)) disabled @endif
            ><i class="material-icons-outlined">delete</i></button>
        </form>
        <div class="mini-modal" id="miniModal">
            The discount is accumulated from all discounts in invoices per Item.
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
<script>
    function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this sales return?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    }


    let prDetails = @json($salesReturn->details);
    let rowCount = prDetails.length;

    let items = @json($items);
    let itemDetails = @json($itemDetails);



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
            input.addEventListener('change', function () {

                calculateTotals(); // Calculate totals when any input changes
            });
        });
    }
    addInputListeners();

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
            console.log(selectedOption);
            var conversionValue = itemDetails.find((i)=>i.item_code == itemCode&&i.unit_conversion ==selectedOption.value).conversion;
            document.getElementById(hiddenInputId).value = conversionValue;
            updateNominalValue(rowNumber);
            calculateTotals();
        });
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

    document.getElementById('add-row').addEventListener('click', addNewRow);
    function addNewRow() {
        const detailsTableBody = document.querySelector('#pr-details-table tbody');
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
                <input type="number" id="qty_${rowCount}" name="details[${rowCount}][qty]" class="form-control" value="1" min="1"  required placeholder="Quantity">
            </td>
            <td>
                <select id="unit_${rowCount}" name="details[${rowCount}][unit]" class="form-control unit-dropdown">
                    @foreach ($itemUnits as $unit)
                        <option value="{{$unit->unit}}">{{$unit->unit_name}}</option>
                    @endforeach
                </select>
                <input type="hidden" id="conversion_value_${rowCount}" name = "details[${rowCount}][base_qty]" value="1"/>
            </td>
            <td>
                <input type="text" name="details[${rowCount}][price]" oninput="formatNumber(this)" class="form-control price-input text-end" id="price_${rowCount}" value="0" required placeholder="{{__('Price')}}">
            </td>
            <td>
                <input type="text" name="details[${rowCount}][disc_percent]" oninput="formatNumber(this)" id="disc_percent_${rowCount}" class="form-control text-end" value="0" required placeholder="% Discount">
                <input type="hidden" name="details[${rowCount}][disc_header]" class="form-control" value="0" readonly />
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

    $('#pr-details-table').on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
    });



    </script>
@endsection
