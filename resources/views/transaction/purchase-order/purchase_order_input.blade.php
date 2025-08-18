@extends('layouts.master')

@section('title', __('Purchase Order'))

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
    <x-page-title title="Transaction" pagetitle="{{__('Purchase Order')}}" />
    <hr>
    <div class="container content">
        <h2>{{__('Purchase Order')}} Transaction</h2>
        <form id="po-form" action="{{ route('transaction.purchase_order.store') }}" method="POST">
            @csrf
            <div class="card mb-3">
                <div class="card-header">{{__('Purchase Order')}} {{__('Information')}}</div>
                <div class="card-body">
                    <input type="hidden" name="token" id="token" value="{{$token}}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">{{__('Search Supplier')}}</label>
                                <input type="text" id="search" class="form-control"
                                    placeholder="Search by Vendor Code, Name, or Address">
                                <div id="search-results" class="list-group"
                                    style="display:none; position:relative; z-index:1000; width:100%;"></div>
                            </div>
                            <div class="form-group">
                                <label for="supplier_code">{{__('Supplier Code')}}</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control" readonly >
                            </div>
                            <div class="form-group">
                                <label for="supplier_name">{{__('Supplier Name')}}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly>
                            </div>
                            {{-- <div class="form-group">
                                <label for="purchase_order_number">Purchase Order Number</label>
                                <input type="text" name="purchase_order_number" id="purchase_order_number"
                                    class="form-control" value="{{ old('purchase_order_number', $purchaseOrderNumber) }}"
                                    readonly>
                            </div> --}}

                        </div>

                        <div class="col-md-4">
                            {{-- <label for="purchase_requisition_number">Purchase Requisition Numbers</label>
                            <div class="input-group mb-3">
                                <select class="form-select" id="purchase_requisition_number" name="purchase_requisition_number" required>
                                    @foreach ($purchaseRequisition as $requ)
                                        <option value="{{$requ->purchase_requisition_number}}">{{$requ->purchase_requisition_number}} [{{$requ->purchase_requisition_number}}]</option>
                                    @endforeach
                                </select>
                            </div> --}}
                            <div class="form-group">
                                {{-- <label for="department_code">Department</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">{{__('Notes')}}</label>
                                <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                            </div>
                            {{-- <div class="form-group">
                                <label for="discount">{{__('Discount')}} (%)</label>
                                <input type="number" step="0.01" name="discount" id="discount" class="form-control"
                                    placeholder="Enter discount percentage" value="{{ old('discount') }}" required>
                            </div> --}}
                            <div class="form-group">
                                <label for="disc_nominal">{{__('Discount')}} Nominal</label>
                                <input type="text" oninput="formatNumber(this)" name="disc_nominal" id="disc_nominal"
                                    class="form-control text-end nominal" placeholder="Enter Discount Nominal" value="0" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required
                                    value="{{ old('document_date') }}">
                            </div>
                            <div class="form-group">
                                <label for="delivery_date">{{__('Delivery Date')}}</label>
                                <input type="date" id="delivery_date" name="delivery_date" class="form-control date-picker" required
                                    value="{{ old('delivery_date') }}">
                            </div>

                            {{-- <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control" required
                                    value="{{ old('due_date') }}">
                            </div> --}}

                            {{-- <div class="form-group">
                                <label for="currency_code">Currency</label>
                                <select class="form-select" id="currency_code" name="currency_code" required>
                                    @foreach ($currencies as $curr)
                                        <option value="{{ $curr->currency_code }}" {{ old('currency_code') === $curr->currency_code ? 'selected' : '' }}>
                                            {{ $curr->currency_code }} ({{ $curr->currency_name }})
                                        </option>
                                    @endforeach
                                </select>
                            </div> --}}

                            {{-- <label for="exampleInputEmail1" class="form-label">Include</label>
                            <div class="form-group">
                                <input type="radio" name="include" value="yes" id="include_yes" {{ old('include') === 'yes' ? 'checked' : '' }} required>
                                <label for="include_yes">Yes</label><br>

                                <input type="radio" name="include" value="no" id="include_no" {{ old('include') === 'no' ? 'checked' : '' }}>
                                <label for="include_no">No</label><br>
                            </div> --}}
                            <div class="form-group">
                                <label for="tax">{{__('Tax')}}</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="PPN / VAT" readonly>
                                    <select hidden class="form-select" id="tax" name="tax">
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $tax->tax_code }}" {{ old('tax') === $tax->tax_code ? 'selected' : '' }}>
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
                                            <option value="{{ $tax->tax_code }}" {{ old('tax') === $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="company_code" value="{{ $company->company_code }}"
                                class="form-control" readonly>
                        </div>
                    </div>
                </div>
            </div>


            <div class="card mb-3">
                <div class="card-header">{{__('Purchase Order')}} Details</div>
                <div class="card-body">
                    <h5 class="text-end d-none">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="po-details-table">
                        <thead>
                            <td style="min-width: 430px">{{__('Item')}}</td>
                            <td style="min-width: 150px">Qty</td>
                            <td style="min-width: 150px">Unit</td>
                            {{-- <td>Action</td> --}}
                        </thead>
                        <tbody id="parentTbody">
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
                                    <input type="text"  name="details[0][qty]" id="qty_0" class="form-control" value="1" min="1" required placeholder="Quantity">
                                </td>
                                <td>
                                    <select id="unit_0" name="details[0][unit]" class="form-control unit-dropdown">
                                        @foreach ($itemUnits as $unit)
                                            <option value="{{$unit->unit}}">{{$unit->unit_name}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                {{-- <td>
                                    <div class="input-group mb-3">
                                        <select class="form-select" id="unit_0" name="details[0][unit]" required>
                                            <option></option>
                                            @foreach ($itemUnits as $unit)
                                                <option data-company="{{$unit->company_code}}" value="{{$unit->unit}}">{{$unit->unit_name.' ('.$unit->unit.')'}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td> --}}
                                <td class="d-none">
                                    <input type="text" name="details[0][price]"
                                    oninput="formatNumber(this)" id="price_0" class="form-control text-end" value="0"  required placeholder="{{__('Price')}}">
                                </td>
                                <td class="d-none">
                                    <input type="text" name="details[0][disc_percent]" max="100" id="disc_percent_0" oninput="formatNumber(this)" class="form-control text-end" value="0" required placeholder="% Discount">
                                </td>
                                <td class="d-none">
                                    <input type="text" name="details[0][disc_nominal]" oninput="formatNumber(this)" id="disc_nominal_0" class="form-control text-end" value="0" required placeholder="Discount">
                                </td>
                                <td class="d-none">
                                    <input type="text" name="details[0][nominal]" oninput="formatNumber(this)" readonly id="nominal_0" class="form-control text-end nominal" value="0" required placeholder="Discount">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="addRow">Add Row</button>
                </div>
            </div>

            <div class="modal fade" id="selectInvoiceModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectInvoiceModalLabel">Select Purchase Requisition</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered" id="invoiceTable">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>Purchase Requisition Number</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($purchaseRequisition as $pi)
                                    <tr>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" class="invoice-checkbox" value="{{ $pi->purchase_requisition_number }}">
                                        </td>
                                        <td>{{ $pi->purchase_requisition_number }}</td>
                                        <td>{{ $pi->department->department_name }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="selectInvoicesButton">Select</button>
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="submit" class="mb-3 btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Purchase Order')}}</button>
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
@endsection



@section('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {
            // Check if the success message is present
            @if(session('success'))
                // Show SweetAlert confirmation modal
                Swal.fire({
                    title: '{{__('Purchase Order')}} Created',
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
                            window.location.href = "{{ route('transaction.purchase_order.print', ['id' => ':id']) }}".replace(':id', id);
                        }
                    }
                });
            @endif
        });

var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

document.getElementById('po-form').addEventListener('submit', function(event) {
    let isValid = true;
    let rows = document.querySelectorAll('#po-details-table tbody tr');

    // Check if there are no rows in the tbody
    if (rows.length === 0) {
        isValid = false; // Set isValid to false
        event.preventDefault(); // Prevent form submission

        // Show SweetAlert warning
        Swal.fire({
            icon: 'warning',
            title: 'No Items Found',
            text: 'Please add at least one item to the table before submitting the form.',
            confirmButtonText: 'OK'
        });
    }
})

// document.getElementById('purchase_requisition_number').value = '';
function setupItemSearch(rowId) {
        // Add event listener for the new row's search input
        document.getElementById(`item-search-${rowId}`).addEventListener('input', function() {
            let query = this.value.toLowerCase();
            let resultsContainer = document.getElementById(`item-search-results-${rowId}`);
            resultsContainer.innerHTML = ''; // Clear previous results
            resultsContainer.style.display = 'none'; // Hide dropdown by default

            if (query.length > 0) {
                // Assuming `items` is an array of item data
                let filteredItems = items.filter(item =>
                    item.item_code.toLowerCase().includes(query) ||
                    item.items.item_name.toLowerCase().includes(query)
                );

                if (filteredItems.length > 0) {
                    resultsContainer.style.display = 'block'; // Show dropdown if matches found

                    // Populate dropdown with filtered results

                    filteredItems.forEach(item => {

                        let listItem = document.createElement('a');
                        listItem.className = 'list-group-item list-group-item-action';
                        listItem.href = '#';
                        listItem.innerHTML = `
                            <small><strong>${item.items.item_name}</strong> [${item.unitn.unit_name}]</small> <br>
                            <small>(${item.item_code})</small>
                        `;

                        // On selecting an item from the dropdown
                        listItem.addEventListener('click', function(e) {
                            let units = [];


                            item.item_details.forEach(element => {
                                if(element.department_code==item.department_code){
                                    units.push(element.unit_conversion);
                                }
                            });
                            e.preventDefault();
                            document.querySelector(`input[name="details[${rowId}][item_id]"]`).value = item.item_code;
                            document.querySelector(`input[name="details[${rowId}][item_name]"]`).value = item.items.item_name;
                            document.getElementById(`unit_${rowId}`).value = item.unitn.unit_name;

                            const unitSelect = document.getElementById(`unit_${rowId}`);
                            Array.from(unitSelect.options).forEach(option => {
                                if (!units.includes(option.value)) {
                                    option.style.display = "none";
                                }
                            });

                            unitSelect.value = item.unit;
                            // if(document.getElementById('purchase_requisition_number').value != '') {
                            //     document.querySelector(`input[name="details[${rowId}][qty]"]`).max = itemIds.find(itemIdObj => itemIdObj.item_id === item.item_code && itemIdObj.unit === item.unit).max;
                            // }
                            calculateTotals();
                            addInputListeners();

                            resultsContainer.style.display = 'none'; // Hide dropdown after selection
                        });

                        resultsContainer.appendChild(listItem); // Add item to dropdown
                    });
                }
            }
        });
    }
    setupItemSearch(0);

//     function setupPRSearch(rowId) {
//         // Add event listener for the new row's search input
//         document.getElementById(`purchase-req-${rowId}`).addEventListener('input', function() {
//             let query = this.value.toLowerCase();
//             let resultsContainer = document.getElementById(`purchase-req-results-${rowId}`);
//             resultsContainer.innerHTML = ''; // Clear previous results
//             resultsContainer.style.display = 'none'; // Hide dropdown by default

//             if (query.length > 0) {
//                 // Assuming `items` is an array of item data
//                 let filteredItems = purchaseRequisition.filter(purchaseRequ =>
//                     purchaseRequ.purchase_requisition_number.toLowerCase().includes(query) ||
//                     purchaseRequ.department.department_name.toLowerCase().includes(query)
//                 );

//                 if (filteredItems.length > 0) {
//                     resultsContainer.style.display = 'block'; // Show dropdown if matches found

//                     // Populate dropdown with filtered results

//                     filteredItems.forEach(purchaseRequ => {
//                         console.log(purchaseRequ);

//                         let listItem = document.createElement('a');
//                         listItem.className = 'list-group-item list-group-item-action';
//                         listItem.href = '#';
//                         listItem.innerHTML = `
//                             <small><strong>${purchaseRequ.purchase_requisition_number}</strong> [${purchaseRequ.department.department_name}]</small>
//                         `;

//                         // On selecting an item from the dropdown
//                         listItem.addEventListener('click', function(e) {
//                             e.preventDefault();


//                             resultsContainer.style.display = 'none'; // Hide dropdown after selection


//                         });

//                         resultsContainer.appendChild(listItem); // Add item to dropdown
//                     });
//                 }
//             }
//         });
//     }
//     setupPRSearch(0);
    // Initialize default values for document date and delivery date
 document.getElementById('document_date').valueAsDate = new Date();
 document.getElementById('delivery_date').valueAsDate = new Date();
//  document.getElementById('due_date').valueAsDate = new Date();
//  document.getElementById('delivery_date').valueAsDate = new Date();
 document.getElementById('department_code').value = '';
//  document.getElementById('salesOrderNumber').value = '';

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
        // const disc_percent = parseFloat(document.getElementById(`disc_percent_${row}`).value.replace(/,/g, '')) || 0;
        // const disc_nominal = parseFloat(document.getElementById(`disc_nominal_${row}`).value.replace(/,/g, '')) || 0;

        const nominalInput = document.getElementById(`nominal_${row}`);
        const nominalValue = ((qty * price)-((qty * price)*disc_percent/100)-disc_nominal)+"";

        let formattedValue = nominalValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        nominalInput.value = formattedValue; // Update nominal value
        calculateTotals();
    }


    $('#selectInvoicesButton').click(function() {
    const selectedRequisition = [];
        $('#invoiceTable .invoice-checkbox:checked').each(function() {
            const requNumber = $(this).val();

            selectedRequisition.push({
                requNumber: requNumber
            });
        });


        filteredPRdetails = purchaseRequisitionD.filter(detail =>
            selectedRequisition.some(selectedDetail =>
                detail.purchase_requisition_number === selectedDetail.requNumber
            )
        );



        filteredPrices = prices.filter(detail =>
        filteredPRdetails.some(selectedDetail =>
            detail.item_code === selectedDetail.item_id && detail.unit === selectedDetail.unit && detail.supplier === supplierId
        ));

    // console.log(supplierId);
    // console.log(filteredPRdetails);
    // console.log(filteredPrices);
        const datas = [];
        filteredPrices.forEach(prices => {
            filteredPRdetails.forEach(detail => {

                if(prices.item_code === detail.item_id && prices.unit === detail.unit && prices.supplier === supplierId){

                    const a={
                        'item_id':detail.item_id,
                        'item_name':detail.items.item_name,
                        'purchase_requisition_number':detail.purchase_requisition_number,
                        'price':prices.purchase_price,
                        'qty':detail.qty_left,
                        'unit':detail.unit,
                        'unit_name':detail.units.unit_name,
                        'notes':detail.notes,
                    }
                    datas.push(a);
                }
            });
        });
        // console.log('Data: ', datas);


        // console.log(prices);
        // console.log(filteredPrices);

        $('#parentTbody').empty();

        datas.forEach(requisit => {
            const currentRow = rowCount;
            const newRow = `
                    <td>
                        <input type="text" name="details[${rowCount}][purchase_requisition_number]" class="form-control" value="${requisit.purchase_requisition_number}" readonly />
                    </td>
                    <td>
                        <input type="hidden" name="details[${rowCount}][item_id]" class="form-control" value="${requisit.item_id}" readonly />
                        <input type="text" name="details[${rowCount}][item_name]" class="form-control" value="${requisit.item_name}" readonly />
                    </td>
                    <td>
                        <input type="hidden" name="details[${rowCount}][unit]" class="form-control" value="${requisit.unit}" readonly />
                        <input type="text" name="details[${rowCount}][unit_name]" class="form-control" value="${requisit.unit_name}" readonly />
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][qty]" class="form-control" id="qty_${rowCount}" value="${requisit.qty}" min="1" max="${requisit.qty}" readonly/>
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][price]" id="price_${rowCount}" class="form-control text-end " value="${requisit.price.split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
                    </td>
                    <td >
                        <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][disc_percent]" id="disc_percent_${rowCount}" step="1" class="form-control text-end" value="0"   />
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][disc_nominal]" id="disc_nominal_${rowCount}" class="form-control text-end" value="0"  />
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][nominal]" id="nominal_${rowCount}" class="form-control text-end nominal" value="${(requisit.price*requisit.qty).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}"   />
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][notes]" class="form-control" value="${requisit.notes??''}" />
                    </td>
            `;
            $('#parentTbody').append(newRow);
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
            rowCount++;
        });

        $('#selectInvoiceModal').modal('hide');

    });


 // SECTION SUPPLIER SEARCH
 const suppliers = @json($suppliers);
let supplierId='';
 let itemIds=[];
 let items = @json($items);
 let prices = @json($prices);
 let purchaseRequisition = @json($purchaseRequisition);
 let purchaseRequisitionD = @json($purchaseRequisitionD);
 let rowCount = 1; // Initialize row count
let SO = [];
let reimbursement = true;
 // Supplier search functionality\

//  document.getElementById('purchase_requisition_number').addEventListener('change', function() {
//     let purchaseReq = document.getElementById('purchase_requisition_number').value;
//     itemIds = purchaseRequisitionD.filter(function(purchaseReqs){
//         return purchaseReqs.purchase_requisition_number == purchaseReq;
//     }).map(function(purchaseReqs) {
//         return {'item_id':purchaseReqs.item_id,
//                 'max':purchaseReqs.qty_left,
//                 'unit':purchaseReqs.unit
//         }; // Correctly access item_id from the current object
//     });

//     // supplierIds = purchaseRequisition.filter(function(purchaseReqss){
//     //     return purchaseReqss.purchase_requisition_number == purchaseReq;
//     // }).map(function(purchaseReqss) {
//     //     return {'supplier_code':purchaseReqss.suppliers.supplier_code,
//     //             'supplier_name':purchaseReqss.suppliers.supplier_name,
//     //             'address':purchaseReqss.suppliers.address
//     //     }; // Correctly access item_id from the current object
//     // });

//     // document.getElementById('supplier_code').value = supplierIds[0].supplier_code;
//     // document.getElementById('supplier_name').value = supplierIds[0].supplier_name;
//     // document.getElementById('address').value = supplierIds[0].address;

//     items = items.filter(item =>
//         itemIds.some(itemId =>
//             item.item_code === itemId.item_id && item.unit === itemId.unit
//         )
//     );
//     // console.log(items);



//  });
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

    let activeIndexCust = -1; // Track the active supplier in the dropdown

    document.getElementById('search').addEventListener('input', function () {
        activeIndexCust = -1; // Reset active index on new input
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = ''; // Clear previous results
        resultsContainer.style.display = 'none'; // Hide dropdown by default

        if (query.length > 0) {
            let filteredSuppliers = suppliers.filter(s =>
                s.supplier_code.toLowerCase().includes(query) ||
                s.supplier_name.toLowerCase().includes(query) ||
                s.address.toLowerCase().includes(query)
            );

            if (filteredSuppliers.length > 0) {
                resultsContainer.style.display = 'block'; // Show dropdown if matches found
                filteredSuppliers.forEach((supplier, index) => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.dataset.index = index; // Store index for reference
                    listItem.innerHTML = `
                        <strong>${supplier.supplier_code}</strong> - ${supplier.supplier_name} <br>
                        <small>${supplier.address}</small>
                    `;
                    listItem.addEventListener('click', function (e) {
                        e.preventDefault();
                        selectSupplier(supplier);
                    });
                    resultsContainer.appendChild(listItem); // Add item to dropdown
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
                updateActiveSupplier(items);
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeIndexCust > -1) {
                activeIndexCust--;
                updateActiveSupplier(items);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndexCust >= 0 && items[activeIndexCust]) {
                items[activeIndexCust].click(); // Trigger click event
            }
        }
    });

    // Helper function to update active supplier
    function updateActiveSupplier(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndexCust);
        });
        if (activeIndexCust >= 0 && items[activeIndexCust]) {
            items[activeIndexCust].scrollIntoView({ block: 'nearest' });
        }
    }

    // Helper function to handle supplier selection
    function selectSupplier(supplier) {
        let supplierId = supplier.supplier_code;
        document.getElementById('search').value = '';
        document.getElementById('supplier_code').value = supplier.supplier_code;
        document.getElementById('supplier_name').value = supplier.supplier_name;
        document.getElementById('address').value = supplier.address;
        document.getElementById('search-results').style.display = 'none'; // Hide dropdown after selection

        if (!supplierId) {
            $('#invoiceTable tbody tr').show();
        } else {
            // Hide rows that do not match the selected supplier
            $('#invoiceTable tbody tr').each(function () {
                const selectedSuppliers = $(this).data('supplier-id');
                if (selectedSuppliers == supplierId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

 $(document).ready(function() {
     let rowCount = 1; // Initialize row count for additional rows

     // Sembunyikan item_id pada awalnya
     $('#item_id_container').hide(); // Pastikan Anda memiliki elemen dengan ID ini

     // Function to handle sales order number change without auto-submit
     $('#sales_order_number').on('change', function() {
         const salesOrderNumber = $(this).val();
        //  console.log('a');

         // Fetch items related to the selected sales order number
         if (salesOrderNumber) {
             fetchItems(salesOrderNumber); // Custom function to fetch items
         }
     });

     // Function to add new row


     // Event delegation for remove-row button
     $('#po-details-table').on('click', '.remove-row', function() {
         $(this).closest('tr').remove();
     });
 });

 $(document).ready(function() {
     // Initialize Select2 on the department_code dropdown
     $('#department_code').select({
         tags: true, // Allows the user to create new options
         placeholder: "Select Department",
         allowClear: true
     });
 });

 function updatePrice() {
            const rowId = this.closest('tr').getAttribute('data-row-id');

    // console.log(rowId);

            const itemCode = document.getElementById(`item_code_${rowId}`).value;
            // console.log(itemCode);

            const unit = 'U1';
            const supplier_code = document.getElementById('supplier_code').value;

            var itemDetails = @json($itemDetails);
            var prices = @json($prices);


            if (itemCode && unit&&itemCode!='REIMBURSE') {
                // Fetch price for the selected item and unit
                const itemDetail = itemDetails.find(detail => detail.item_code === itemCode && detail.unit_conversion === unit);
                const itemSale = itemDetail ? prices.find(sale => sale.barcode === itemDetail.barcode&& sale.supplier==supplier_code) : null;
                const price = itemSale ? itemSale.purchase_price : 0;

                // Update the price input field for the correct row
                document.getElementById(`price_${rowId}`).value = price;
                // console.log();
                // console.log(SO.find(so=> so.item_id == itemCode && so.unit == unit).qty_left);

                document.getElementById(`qty_${rowId}`).max = SO.find(so=> so.item_id == itemCode && so.unit == unit).qty_left;
            }
            if(itemCode=='REIMBURSE'){
                document.getElementById(`qty_${rowId}`).max = 1;
                document.getElementById(`disc_nominal_${rowId}`).max = 0;
                document.getElementById(`disc_percent_${rowId}`).max = 0;
            }
        }

        // document.getElementById(`item_code_0`).addEventListener('change', updatePrice);
        // document.getElementById(`unit_0`).addEventListener('change', updatePrice);

        // console.log(SO);


        $('#addRow').on('click', function() {
            const detailsTableBody = document.querySelector('#po-details-table tbody');
            const newRow = document.createElement('tr');
            newRow.setAttribute('data-row-id', rowCount);
            const currentRow = rowCount;
            newRow.innerHTML = `
            <td>
                <div class="form-group">
                    <input type="hidden" id="item_code_${currentRow}" class="form-control item-input" name="details[${currentRow}][item_id]" placeholder="{{__('Search Item')}}">
                    <input type="text" class="form-control item-input" name="details[${currentRow}][item_name]" id="item-search-${currentRow}" placeholder="{{__('Search Item')}}">
                    <div id="item-search-results-${currentRow}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                        <!-- Search results will be injected here -->
                    </div>
                </div>
            </td>
            <td>
                <input type="number" id="qty_${currentRow}" name="details[${currentRow}][qty]" class="qtyw form-control" value="1" min="1" required placeholder="Quantity">
            </td>
            <td>
                <select id="unit_${rowCount}" name="details[${rowCount}][unit]" class="form-control unit-dropdown">
                    @foreach ($itemUnits as $unit)
                        <option value="{{$unit->unit}}">{{$unit->unit_name}}</option>
                    @endforeach
                </select>
            </td>
            <td class="d-none">
                <input type="text" name="details[${rowCount}][price]" oninput="formatNumber(this)" class="form-control price-input text-end" id="price_${rowCount}" value="0" required placeholder="{{__('Price')}}">
            </td>
            <td class="d-none">
                <input type="text" name="details[${rowCount}][disc_percent]" oninput="formatNumber(this)" id="disc_percent_${rowCount}" class="form-control text-end" value="0" required placeholder="% Discount">
            </td>
            <td class="d-none">
                <input type="text" name="details[${rowCount}][disc_nominal]" oninput="formatNumber(this)" id="disc_nominal_${rowCount}" class="form-control text-end" value="0" required placeholder="Discount">
            </td>
            <td class="d-none">
                <input type="text" name="details[${currentRow}][nominal]" oninput="formatNumber(this)" readonly id="nominal_${currentRow}" class="form-control text-end nominal" value="0" required placeholder="Discount">
            </td>
            <td>
                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
            </td>
        `;

         // Append the new row to the table body
        detailsTableBody.appendChild(newRow);

        //  document.getElementById(`item_code_${rowCount}`).addEventListener('change', updatePrice);
        //  console.log('a');
        setupItemSearch(rowCount);

        document.getElementById(`item_code_${currentRow}`).addEventListener('change', updatePrice);
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

         // Increment row count for the next row
        document.getElementById(`item-search-${currentRow}`).focus();
        rowCount++;

    });


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
 // When the sales order number is changed
    $('#salesOrderNumber').on('change', function() {
        const salesOrderNumber = $(this).val(); // Get the selected sales order number

        // Clear the item code dropdown
        $('#itemCode').empty().append('<option value="" disabled selected>{{__('Select Item')}}</option>');

        if (salesOrderNumber) {
            $.ajax({
                url: '{{ route('transaction.fetch_items') }}',
                method: 'GET',
                data: { sales_order_number: salesOrderNumber },
                success: function(data) {
                    // console.log(data);

                    // console.log(rowCount);

                    // Populate itemCode dropdown with received items
                    for (let i = 0; i <= rowCount+1; i++) {

                    $(`#item_code_${i}`).empty();
                    $(`#item_code_${i}`).prop('selectedIndex', -1);
                    $(`#item_code_${i}`).append(`
                            <option value="" selected disabled></option>`)
                    data.item.forEach(item => {
                            $(`#item_code_${i}`).append(`
                            <option value="${item.item_code}">
                                ${item.item_name}
                            </option>
                            `);
                    });
                    if(data.status_reimburse=='Not'){
                        reimbursement = true;
                        $(`#item_code_${i}`).append(`
                        <option value="REIMBURSE">
                                Reimburse
                            </option>
                            `);
                    }else{
                        reimbursement=false;
                    }

                    }
                    const reim = {
                        'item_code':"REIMBURSE",
                        'item_name':"Reimburse",
                    }
                    if(reimbursement){
                        data.item.push(reim);
                    }
                    SO = data.so;
                    items = data.item;
                },
                error: function(xhr) {
                    console.error('Error fetching items:', xhr);
                }
            });
        }
    });
});
    </script>
@endsection
