@extends('layouts.master')

@section('title', __('Purchase Invoice'))

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
    </style>
@endsection

@section('content')
    <x-page-title title="Transaction" pagetitle="{{__('Purchase Invoice')}}" />
    <hr>
    <div class="container content">
        <h2>{{__('Purchase Invoice')}} Edit</h2>
        <form id="print-form" action="{{ route('transaction.purchase_invoice.print', $purchaseOrder->id) }}" target="_blank" method="GET" style="display:inline;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
                Print PI
            </button>
        </form>
        <form id="print-form" action="{{ route('transaction.purchase_invoice.print.netto', $purchaseOrder->id) }}" target="_blank" method="GET" style="display:inline;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
                Print PI Netto
            </button>
        </form>
        <form id="po-form" action="{{ route('transaction.purchase_invoice.update', $purchaseOrder->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card mb-3">
                <div class="card-header">{{__('Purchase Invoice')}} {{__('Information')}}</div>
                @if (!$editable)
                    <h7 style="color:red; font-weight:bold;">This document can't be edited or deleted because already used in Payable Payment</h7>
                @endif
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            {{-- <div class="form-group">
                                <label for="search">{{__('Search Supplier')}}</label>
                                <input type="text" id="search" class="form-control"
                                    placeholder="Search by Vendor Code, Name, or Address">
                                <div id="search-results" class="list-group"
                                    style="display:none; position:relative; z-index:1000; width:100%;"></div>
                            </div> --}}
                            <div class="form-group">
                                <label for="supplier_code">{{__('Supplier Code')}}</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control" readonly value="{{ $purchaseOrder->supplier_code }}">
                            </div>
                            <div class="form-group">
                                <label for="supplier_name">{{__('Supplier Name')}}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" readonly value="{{ $purchaseOrder->suppliers->supplier_name }}">
                            </div>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly value="{{ $purchaseOrder->suppliers->address }}">
                            </div>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {{-- <label for="department_code">Department</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $purchaseOrder->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">{{__('Notes')}}</label>
                                <textarea name="notes" class="form-control" value="" rows="4">{{ $purchaseOrder->notes }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="disc_nominal">{{__('Discount')}} Nominal</label>
                                <input type="number" step="0.01" name="disc_nominal" id="disc_nominal"
                                    class="form-control" placeholder="Enter Discount Nominal" required value="{{ $purchaseOrder->disc_nominal }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required
                                    value="{{ $purchaseOrder->document_date }}">
                            </div>
                            <div class="form-group">
                                <label for="delivery_date">{{__('Delivery Date')}}</label>
                                <input type="date" id="delivery_date" name="delivery_date" class="form-control date-picker " required
                                    value="{{ $purchaseOrder->delivery_date }}">
                            </div>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}} Hutang</label>
                                <input type="date" id="due_date" name="due_date" class="form-control date-picker" required
                                    value="{{ $purchaseOrder->due_date }}">
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
                                            <option value="{{ $purchaseOrder->tax_code }}" {{ old('tax', $purchaseOrder->tax) === $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tax">{{__('Revenue Tax')}}</label>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="tax_revenue" name="tax_revenue">
                                        <option value="0">Tidak kena pajak</option>
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $tax->tax_code }}" {{ old('tax', $purchaseOrder->tax_revenue_tariff) === $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="company_code" value="{{ $purchaseOrder->company_code }}"
                                class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card mb-3">
                <div class="card-header">{{__('Purchase Invoice')}} Details</div>
                <div class="card-body">
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <td style="min-width: 270px">{{__('Good Receipt Number')}}</td>
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
                            @foreach ($purchaseOrderDetails as $index => $detail)
                                <tr>
                                    <td>
                                        <input type="text" name="details[{{ $index }}][good_receipt_number]" class="form-control" value="{{ $detail->good_receipt_number }}" readonly />
                                        <input type="hidden" name="details[{{ $index }}][purchase_order_number]" class="form-control" value="{{ $detail->purchase_order_number }}" readonly />
                                    </td>
                                    <td>
                                        <input type="hidden" id="item_code_{{$index}}" name="details[{{ $index }}][item_id]" class="form-control" value="{{ $detail->item_id }}" readonly />
                                        <input type="text" name="details[{{ $index }}][item_name]" class="form-control" value="{{ $detail->items->item_name }}" readonly />
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
                                        <input type="hidden" id="conversion_value_{{$index}}" name="details[{{ $index }}][base_qty]" value="{{ $detail->base_qty }}" readonly />
                                        <input type="number" id="qty_{{$index}}" name="details[{{ $index }}][qty]" class="form-control" value="{{ $detail->qty }}" min="1" readonly/>
                                    </td>
                                    <td>
                                        <input type="text" id="price_{{$index}}" name="details[{{ $index }}][price]" class="form-control text-end" value="{{ number_format($detail->price,0,'.',',') }}" />
                                    </td>
                                    <td >
                                        <input type="text" id="disc_percent_{{$index}}" name="details[{{ $index }}][disc_percent]" step="1" class="form-control text-end" value="{{ number_format($detail->disc_percent,0,'.',',') }}"  />
                                    </td>
                                    <td >
                                        <input type="text" id="disc_nominal_{{$index}}" name="details[{{ $index }}][disc_nominal]"  class="form-control text-end" value="{{ number_format($detail->disc_nominal,0,'.',',') }}"  />
                                    </td>
                                    <td >
                                        <input type="text" id="nominal_{{$index}}" name="details[{{ $index }}][nominal]"  class="form-control text-end nominal" value="{{ number_format($detail->nominal,0,'.',',') }}" readonly />
                                    </td>
                                    <td id="pay-row-{{ $index }}">
                                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <button type="button" onclick="addNewRow()" class="btn btn-primary mt-3">Tambah Barang</button>
                </div>
            </div>



                <button type="submit" class="mb-3 btn btn-primary" @if(!$editable||!in_array('print', $privileges)) disabled  @endif>Update {{__('Purchase Invoice')}}</button>

                <a class=" mb-3 btn btn-secondary" href="{{route('transaction.purchase_invoice')}}">Back</a>
        </form>
        {{-- @if($purchaseOrder->status!='Cancelled'&&$purchaseOrder->status!='Closed')
        <form id="cancel-form" action="{{ route('transaction.purchase_invoice.cancel', $purchaseOrder->id) }}" method="POST" style="display:inline;" >
            @csrf
            @method('POST')
            <input type="hidden" name="reason" id="cancellation-reason">
            <button type="button" class="btn btn-danger mb-3 " onclick="confirmCancel(event,'{{ $purchaseOrder->id }}')"
                @if(Auth::user()->role != 5 && Auth::user()->role != 7)
                    style="display: none"
                @endif
            >Cancel PI</button>
        </form>
        @endif --}}

        @if($editable)
            <form id="delete-form" action="{{ route('transaction.purchase_invoice.destroy', $purchaseOrder->id) }}" method="POST" style="display:inline;" >
                @csrf
                @method('POST')
                <input type="hidden" name="reason" id="deletion-reason">
                <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $purchaseOrder->id }}')"
                    @if(!in_array('delete', $privileges)) disabled @endif
                ><i class="material-icons-outlined">delete</i></button>
            </form>
        @endif
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
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);



        function confirmCancel(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to cancel this purchase invoice?',
            icon: 'warning',
            input: 'text', // This adds an input field
            inputPlaceholder: 'Enter reason for cancellation',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it!',
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
                document.getElementById('cancellation-reason').value = reason;
                document.getElementById('cancel-form').submit();
            }
        });
    }



function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this purchase invoice?',
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




 // SECTION SUPPLIER SEARCH
 const suppliers = @json($suppliers);
let supplierId='';
 let itemIds=[];
 let items = @json($items);
 let goodReceipt = @json($goodReceipt);
 let goodReceiptD = @json($goodReceiptD);
 let itemDetails = @json($itemDetails);
 let rowCount = {{ isset($purchaseOrder) ? count($purchaseOrder->details) : 1 }};
let SO = [];
let reimbursement = true;
$(document).on('click', '.deleteRow', function() {
        const rowCount = $(this).closest('tr');
        const detailRow = rowCount.next('.detail-row');

        // Remove detail row if it exists
        if (detailRow.length) {
            detailRow.remove();
        }

        // Remove the main row
        rowCount.remove();
    });

    function calculateTotals() {
        let total = 0;
        const disc_nominal = document.getElementById('disc_nominal').value.replace(/,/g, '') || 0;
        console.log(disc_nominal);

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



 $(document).ready(function() {
     // Initialize Select2 on the department_code dropdown
     $('#department_code').select({
         tags: true, // Allows the user to create new options
         placeholder: "Select Department",
         allowClear: true
     });
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
            updateNominalValue(rowNumber);
            calculateTotals();
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
        document.getElementById(`price_${rowId}`).value = item.purchase_price.split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        let selectedUnit = unitSelect.value;
        let conversionDetail = itemDetails.find(i => i.item_code === item.item_code && i.unit_conversion === selectedUnit);
        let conversionValue = conversionDetail ? conversionDetail.conversion : 1;

        document.getElementById(`conversion_value_${rowId}`).value = conversionValue;
        document.getElementById(`nominal_${rowId}`).value =
            (item.purchase_price * conversionValue).toString().split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');

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
                <input type="text" name="details[${rowCount}][good_receipt_number]" class="form-control" value="" readonly />
                <input type="hidden" name="details[${rowCount}][purchase_order_number]" class="form-control" value="" readonly />
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




$(document).ready(function() {
    document.getElementById('po-form').addEventListener('submit', function(event) {
        const supplierCode = document.getElementById('supplier_code').value;

        // Check if supplier_code is null or empty
        if (!supplierCode) {
            event.preventDefault(); // Prevent form submission
            Swal.fire({
                title: 'Error!',
                text: 'Please select a supplier before submitting the form.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });
});
    </script>
@endsection
