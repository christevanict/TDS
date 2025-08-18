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
    <x-page-title title="Transaction" pagetitle="{{__('Sales Return')}}" />
    <hr>
    <div class="container content">
        <h2>{{__('Sales Return')}} Transaction</h2>
        <form id="po-form" action="{{ route('transaction.sales_return.store') }}" method="POST">
            @csrf
            <div class="card mb-3">
                <div class="card-header">{{__('Sales Return')}} {{__('Information')}}</div>
                <div class="card-body">
                    <input type="hidden" name="token" id="token" value="{{$token??''}}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">{{__('Search Customer')}}</label>
                                <input type="text" id="search" class="form-control"
                                    placeholder="Search by Customer Code, Name, or Address">
                                <div id="search-results" class="list-group"
                                    style="display:none; position:relative; z-index:1000; width:100%;"></div>
                            </div>
                            <div class="form-group">
                                <label for="customer_code">{{__('Customer Code')}}</label>
                                <input type="text" name="customer_code" id="customer_code" class="form-control" readonly >
                            </div>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly>
                            </div>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {{-- <label for="department_code">Department</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                            <div class="form-group d-none">
                                <label for="search">Search {{__('Sales Invoice')}}</label>
                                <input type="text" id="searchPi"   class="form-control"
                                    placeholder="Search by SI Number or {{__('Document Date')}}" >

                                <input type="hidden" name="sales_invoice_number" id="sales_invoice_number" class="form-control" readonly  required>
                                <div id="search-pi-results" class="list-group"
                                    style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;"></div>
                            </div>
                            <div class="form-group">
                                <label for="notes">{{__('Notes')}}</label>
                                <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                            </div>

                            {{-- <div clas   s="form-group">
                                <label for="disc_nominal">{{__('Discount')}} Nominal</label>
                                <input type="number" step="0.01" name="disc_nominal" id="disc_nominal"
                                    class="form-control" placeholder="Enter Discount Nominal" value="0" required>
                            </div> --}}
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required
                                    value="{{ old('document_date') }}">
                            </div>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control date-picker" required
                                    value="{{ old('due_date') }}">
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


            <div class="mini-modal" id="miniModal">
                The discount is accumulated from all discounts in invoices per Item.
            </div>

            <div class="card mb-3">
                <div class="card-header">{{__('Sales Return')}} Details</div>

                <div class="card-body">
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <th style="min-width: 430px">{{__('Item')}}</th>
                            <th style="min-width: 150px">Qty</th>
                            <th style="min-width: 150px">Unit</th>
                            <th style="min-width: 200px">{{__('Price')}}/ Unit</th>
                            <th style="min-width: 150px">{{__('Discount')}} (%)</th>
                            <th style="min-width: 200px">{{__('Discount')}}</th>
                            <th style="min-width: 200px">Nominal</th>
                            <th>Action</th>
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
                                    <input type="hidden" id="conversion_value_0" name="details[0][conversion_value]" />
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
                                <td>
                                    <input type="text" name="details[0][price]"
                                    oninput="formatNumber(this)" id="price_0" class="form-control text-end" value="0"  required placeholder="{{__('Price')}}">
                                </td>
                                <td>
                                    <input type="text" name="details[0][disc_percent]" max="100" id="disc_percent_0" oninput="formatNumber(this)" class="form-control text-end" value="0" required placeholder="% Discount">
                                    <input type="hidden" name="details[0][disc_header]" id="disc_percent_0" oninput="formatNumber(this)" class="form-control text-end" value="0">
                                </td>
                                <td>
                                    <input type="text" name="details[0][disc_nominal]" oninput="formatNumber(this)" id="disc_nominal_0" class="form-control text-end" value="0" required placeholder="Discount">
                                </td>
                                <td>
                                    <input type="text" name="details[0][nominal]" oninput="formatNumber(this)" readonly id="nominal_0" class="form-control text-end nominal" value="0" required placeholder="Discount" readonly>
                                </td>
                                {{-- <td>
                                    <select class="form-select" id="status" name="details[0][status]">
                                        <option value="Not">Not Ready</option>
                                        <option value="Ready">Ready</option>
                                    </select>
                                </td> --}}
                                <td>
                                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                    <button type="button" id="add-row" class="btn btn-primary mt-3">{{__('Add Item')}}</button>
                </div>
            </div>
            <button type="submit" class="mb-3 btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Sales Return')}}</button>
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
                    title: '{{__('Sales Return')}} Created',
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
                            window.open("{{ route('transaction.sales_return.print', ['id' => ':id']) }}".replace(':id', id), '_blank');
                        }
                    }
                });
            @endif
        });
        $('#addRow').click(function() {
            $('#selectInvoiceModal').modal('show');
        });

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



        // Hide modal when clicking outside

    // Initialize default values for document date and delivery date
 document.getElementById('document_date').valueAsDate = new Date();
 document.getElementById('due_date').valueAsDate = new Date();
//  document.getElementById('delivery_date').valueAsDate = new Date();
 document.getElementById('department_code').value = '';
//  document.getElementById('salesOrderNumber').value = '';
 document.getElementById('tax').value = '';

 // Function to format numbers for Indonesian currency
//  function formatNumber(number) {
//      return new Intl.NumberFormat('id-ID').format(number);
//  }

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

 function selectSalesInvoice(number) {

        filteredPIDetails = salesInvoicesD.filter(detail =>
            detail.sales_invoice_number === number
        );


        const datas = [];
        filteredPIDetails.forEach(detail => {
            const temp={
                'item_id':detail.item_id,
                'item_name':detail.items.item_name,
                'price':detail.price,
                'qty':detail.qty_left,
                'unit':detail.unit,
                'unit_name':detail.units.unit_name,
                'notes':detail.description,
                'disc_nominal':detail.disc_nominal,
                'disc_header':detail.disc_header,
                'disc_percent':detail.disc_percent,
            }
            datas.push(temp);
        });
        $('#parentTbody').empty();
        let total=0;
        datas.forEach(requisit => {
            total+=(requisit.qty*requisit.price);
            const currentRow = rowCount;
            const newRow = `
                <tr>
                    <td>
                        <input type="hidden" name="details[${rowCount}][item_id]" class="form-control" value="${requisit.item_id}" readonly />
                        <input type="text" name="details[${rowCount}][item_name]" class="form-control scroll-text" value="${requisit.item_name}" readonly />
                    </td>
                    <td>
                        <input type="hidden" name="details[${rowCount}][unit]" class="form-control" value="${requisit.unit}" readonly />
                        <input type="text" name="details[${rowCount}][unit_name]" class="form-control" value="${requisit.unit_name}" readonly />
                        </td>
                    <td>
                        <input type="number" id=qty_${rowCount} name="details[${rowCount}][qty]" class="form-control" value="${requisit.qty}" min="1" max="${requisit.qty}"/>
                        <input type="hidden" name="details[${rowCount}][disc_header]" class="form-control" value="${requisit.disc_header}" readonly />
                        <input type="hidden" name="details[${rowCount}][disc_percent]" class="form-control" value="${requisit.disc_percent}" readonly />
                        <input type="hidden" name="details[${rowCount}][disc_nominal]" class="form-control" value="${requisit.disc_nominal}" readonly />
                    </td>
                    <td>
                        <input type="text" id="price_${currentRow}" name="details[${rowCount}][price]" class="form-control text-end" value="${requisit.price.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly/>
                    </td>
                    <td>
                        <input type="text" id="disc_total_${currentRow}" class="form-control text-end" value="${(parseFloat(requisit.disc_nominal)+parseFloat(requisit.disc_header)+parseFloat(requisit.disc_percent*requisit.price*requisit.qty/100)).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly />
                    </td>
                    <td>
                        <input type="text" id="nominal_${currentRow}" class="form-control text-end nominal" value="${((requisit.price*requisit.qty)-(parseFloat(requisit.disc_nominal)+parseFloat(requisit.disc_header)+parseFloat(requisit.disc_percent*requisit.price*requisit.qty/100))).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly />
                    </td>
                    <td id="pay-row-${rowCount}">
                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                    </td>
                </tr>
            `;
        $('#parentTbody').append(newRow);
        document.getElementById(`qty_${currentRow}`).addEventListener('input', function() {
            updateNominalValue(currentRow); // Call the function when the event occurs
        });
        calculateTotals();
        addInputListeners();
        rowCount++;
        });

        $('#selectInvoiceModal').modal('hide');

    };


 // SECTION SUPPLIER SEARCH
 const customers = @json($customers);
let customerId='';
 let itemIds=[];
 let items = @json($items);
 let itemDetails = @json($itemDetails);
 let prices = @json($prices);
 let salesInvoices = @json($salesInvoices);
 let salesInvoicesD = @json($salesInvoicesD);
 let rowCount = 1; // Initialize row count
let SO = [];
let reimbursement = true;




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

    function formatDate(value) {
        let date = new Date(value);
        const day = date.toLocaleString('default', { day: '2-digit' });
        const month = date.toLocaleString('default', { month: 'short' });
        const year = date.toLocaleString('default', { year: 'numeric' });
        return day + '-' + month + '-' + year;
    }
 document.getElementById('searchPi').addEventListener('input', function() {
    let rowCount = 0;
    let query = this.value.toLowerCase();
    let resultsContainer = document.getElementById('search-pi-results');
    resultsContainer.innerHTML = ''; // Clear previous results
    resultsContainer.style.display = 'none'; // Hide dropdown by default


    if (query.length > 0) {
        let filteredSalesInvoices = salesInvoices.filter(s =>
            s.customer_code==customerId &&
             s.sales_invoice_number.toLowerCase().includes(query) || // Match customer_code
             s.document_date.toLowerCase().includes(query)  // Match customer_name

        );


        if (filteredSalesInvoices.length > 0) {
             resultsContainer.style.display = 'block'; // Show dropdown if matches found
             // Populate dropdown with filtered results
            filteredSalesInvoices.forEach(invoice => {
                let listItem = document.createElement('a');
                listItem.className = 'list-group-item list-group-item-action';
                listItem.href = '#';
                listItem.innerHTML = `
                <strong>${invoice.sales_invoice_number}</strong> <br>
                <small>${formatDate(invoice.document_date)}</small>
            `;
                 // Handle selection of a customer
                listItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.getElementById('searchPi').value = invoice.sales_invoice_number;
                    document.getElementById('sales_invoice_number').value = invoice.sales_invoice_number;

                    selectSalesInvoice(invoice.sales_invoice_number);
                    resultsContainer.style.display = 'none';
                });



                 resultsContainer.appendChild(listItem); // Add item to dropdown
            });
        }
    }
});

 $(document).ready(function() {
     let rowCount = 1; // Initialize row count for additional rows

     // Sembunyikan item_id pada awalnya
     $('#item_id_container').hide(); // Pastikan Anda memiliki elemen dengan ID ini

     // Function to handle sales order number change without auto-submit
     $('#sales_order_number').on('change', function() {
         const salesOrderNumber = $(this).val();

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
         placeholder: "PilihDepartment",
         allowClear: true
     });
 });

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
            var conversionValue = itemDetails.find((i)=>i.item_code == itemCode&&i.unit_conversion ==selectedOption.value).conversion;
            document.getElementById(hiddenInputId).value = conversionValue;
            updateNominalValue(rowNumber);
            calculateTotals();
        });
    }

    setupItemSearch(0);
    setupUnitChangeListener(0);
    document.getElementById(`qty_0`).addEventListener('input', function() {
            updateNominalValue(0); // Call the function when the event occurs
        });

        document.getElementById(`price_0`).addEventListener('input', function() {
            updateNominalValue(0); // Call the function when the event occurs
        });
        document.getElementById(`disc_percent_0`).addEventListener('input', function() {
            updateNominalValue(0); // Call the function when the event occurs
        });
        document.getElementById(`disc_nominal_0`).addEventListener('input', function() {
            updateNominalValue(0); // Call the function when the event occurs
        });

    document.getElementById('add-row').addEventListener('click', addNewRow);
    function addNewRow() {
        const detailsTableBody = document.querySelector('#dynamicTable tbody');
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
                <input type="hidden" id="conversion_value_${rowCount}" name = "details[${rowCount}][conversion_value]" value="1"/>
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
        document.getElementById(`item-search-${currentRow}`).focus();
        rowCount++; // Increment row count for the next row
    }



$(document).ready(function() {
    document.getElementById('po-form').addEventListener('submit', function(event) {
        const customerCode = document.getElementById('customer_code').value;

        // Check if customer_code is null or empty
        if (!customerCode) {
            event.preventDefault(); // Prevent form submission
            Swal.fire({
                title: 'Error!',
                text: 'Please select a customer before submitting the form.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }
    });

});
    </script>
@endsection
