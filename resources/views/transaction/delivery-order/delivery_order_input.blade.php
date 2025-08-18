@extends('layouts.master')

@section('title', 'Delivery Order Input')

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
<x-page-title title="Delivery Order" pagetitle="Delivery Order Transaction" />
<hr>
<div class="container content">
    <h2>Delivery Order Input</h2>

    <div id="message-container">
        @if(session('success'))
            <div id="success-message" class="alert alert-success fade show">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div id="error-message" class="alert alert-error fade show">{{ session('error') }}</div>
        @endif
    </div>

    <form id="inbound-form" action="{{ route('transaction.warehouse.delivery_order.store') }}" method="POST">
        @csrf

        <!-- Card for Delivery Order Transaction -->
        <div class="card mb-3">
            <div class="card-header">Delivery Order Transaction</div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column: Supplier Code, Supplier Name, Address -->
                    <div class="col-md-6">
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
                    <!-- Center Column: Notes -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="notes">{{__('Notes')}}</label>
                            <textarea name="notes" id="notes" class="form-control" rows="5"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="document_date">{{__('Document Date')}}</label>
                            <input type="date" id="document_date" name="document_date" class="form-control date-picker" required>
                        </div>
                    </div>


                </div>

            </div>
        </div>

        <!-- Card for Outbound Details -->
        <div class="card mb-3">
            <div class="card-header">Outbound Details</div>
            <div class="card-body">
                <table class="table" id="inbound-details-table">
                    <thead>
                        <tr>
                            <th>{{__('Sales Order Number')}}</th>
                            <th>{{__('Item')}}</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="parentTbody">

                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary mt-3" id="addRow">{{__('Select Document')}}</button>
                {{-- <button type="button" id="add-row" class="btn btn-primary mt-3">{{__('Add Item')}}</button> --}}
            </div>
        </div>

        <div class="modal fade" id="selectInvoiceModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="selectInvoiceModalLabel">Select {{__('Sales Order')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered" id="invoiceTable">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>{{__('Sales Order Number')}}</th>
                                    <th>Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($salesOrders as $po)
                                <tr>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <input type="checkbox" class="invoice-checkbox" value="{{ $po->sales_order_number }}">
                                    </td>
                                    <td>{{ $po->sales_order_number }}</td>
                                    <td>{{ $po->department->department_name }}</td>
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
        <!-- Submit button with margin -->
        <div class="form-group submit-btn mb-3">
            <button type="submit mb-3" class="btn btn-success">Submit Delivery Order</button>
        </div>
    </form>
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

    var rowCount = 0;

    const items = @json($items);
    const customers = @json($customers);
    const itemDetails = @json($itemDetails);
    const salesOrders = @json($salesOrders);
    let filteredItems = [];
    let selectedSupplier='';

    document.getElementById('document_date').valueAsDate = new Date();
    document.getElementById('search').addEventListener('input', function() {
    let rowCount = 0;
    let query = this.value.toLowerCase();
    let resultsContainer = document.getElementById('search-results');
    resultsContainer.innerHTML = ''; // Clear previous results
    resultsContainer.style.display = 'none'; // Hide dropdown by default
    // console.log(customers);
     if (query.length > 0) {
         let filteredSuppliers = customers.filter(s =>


             s.customer_code.toLowerCase().includes(query) || // Match customer_code
             s.customer_name.toLowerCase().includes(query) || // Match customer_name
             s.address.toLowerCase().includes(query) // Match address
         );

         if (filteredSuppliers.length > 0) {
             resultsContainer.style.display = 'block'; // Show dropdown if matches found
             // Populate dropdown with filtered results
             filteredSuppliers.forEach(customer => {
                 let listItem = document.createElement('a');
                 listItem.className = 'list-group-item list-group-item-action';
                 listItem.href = '#';
                 listItem.innerHTML = `
                 <strong>${customer.customer_code}</strong> - ${customer.customer_name} <br>
                 <small>${customer.address}</small>
             `;
                 // Handle selection of a customer
                 listItem.addEventListener('click', function(e) {
                     e.preventDefault();
                     selectedSupplier = customer.customer_code;
                     document.getElementById('search').value = '';
                     document.getElementById('customer_code').value = customer.customer_code;
                     document.getElementById('customer_name').value = customer.customer_name;
                     document.getElementById('address').value = customer.address;
                     resultsContainer.style.display = 'none'; // Hide dropdown after selection
                 });

                 resultsContainer.appendChild(listItem); // Add item to dropdown
             });
         }
     }
 });





    // Function to setup search input for dynamically added rows
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
                            <strong>${item.items.item_name}</strong><br>
                            <small>Unit: ${item.unitn.unit_name}</small>
                        `;

                        // On selecting an item from the dropdown
                        listItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            document.querySelector(`input[name="details[${rowId}][item_code]"]`).value = item.item_code;
                            document.querySelector(`input[name="details[${rowId}][base_unit]"]`).value = item.items.base_unit;
                            document.querySelector(`input[name="details[${rowId}][item_name]"]`).value = item.items.item_name;
                            document.querySelector(`select[name="details[${rowId}][unit]"]`).value = item.unitn.unit;
                            resultsContainer.style.display = 'none'; // Hide dropdown after selection

                            const matchedItems = itemDetails.filter(detail => detail.item_code === item.item_code);

                            // Create a set of allowed `unit_conversion` values from the matched items
                            const allowedUnits = new Set(matchedItems.map(detail => detail.unit_conversion));

                            // Select the dropdown for the specified row
                            const unitSelect = document.querySelector(`select[name="details[${rowId}][unit]"]`);

                            // Iterate over the dropdown options
                            Array.from(unitSelect.options).forEach(option => {
                                // Show or hide the option based on whether its value is in the allowedUnits set
                                option.hidden = !allowedUnits.has(option.value);
                            });

                        });

                        resultsContainer.appendChild(listItem); // Add item to dropdown
                    });
                }
            }
        });
    }
    // setupItemSearch(0);

    // Initialize dropdowns for search functionality
    function initDropdown() {
        $('.item-input').on('click', function(e) {
            e.stopPropagation(); // Prevent click from bubbling up
            $('.dropdown-menu').hide(); // Hide all dropdowns first
            $(this).siblings('.dropdown-menu').toggle(); // Show the clicked dropdown
        });

        // Close dropdown when clicking outside
        $(document).on('click', function() {
            $('.dropdown-menu').hide();
        });

        $('.search-input').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $(this).siblings('.item-list').find('li').filter(function() {
                $(this).toggle($(this).data('name').toLowerCase().indexOf(value) > -1);
            });
        });

        // Event delegation for item list click
        $(document).on('click', '.item-list li', function() {
            var $dropdown = $(this).closest('.dropdown');
            var itemCode = $(this).data('value');
            var itemName = $(this).data('name');
            var baseUnit = $(this).data('baseunit');

            // Update the input fields with selected item's details
            $dropdown.find('.item-input').val(itemCode);
            $dropdown.closest('tr').find('.item-name').val(itemName);
            $dropdown.closest('tr').find('.base-unit').val(baseUnit);

            // Close the dropdown menu after selection
            $dropdown.find('.dropdown-menu').hide();
            // updateUnitDropdowns(); // Update units whenever an item is selected
        });
    }

    $('#addRow').click(function() {
        $('#selectInvoiceModal').modal('show');
    })

    $('#selectInvoicesButton').click(function() {
        const selectedorder = [];
        filteredItems = [];
        $('#invoiceTable .invoice-checkbox:checked').each(function() {
            const requNumber = $(this).val();
            selectedorder.push({
                requNumber: requNumber
            });
        });
        salesOrders.forEach(element => {

            filteredItem = element.details.filter(detail =>
                selectedorder.some(selectedDetail =>
                    detail.sales_order_number === selectedDetail.requNumber && element.customer_code  == selectedSupplier
                )
            );
            filteredItems.push(...filteredItem);
        });

        const groupedItems = groupFilteredItems(filteredItems);

        $('#parentTbody').empty();
        groupedItems.forEach(requisit => {
            console.log(requisit);

            console.log(requisit.qty_left);

            if(requisit.qty_left>0){
                const newRow = `
                <tr>
                    <td>
                        <input type="text" name="details[${rowCount}][sales_order_number]" class="form-control" value="${requisit.sales_order_number}" readonly />
                    </td>
                    <td>
                        <input type="hidden" name="details[${rowCount}][item_code]" class="form-control" value="${requisit.item_id}" readonly />
                        <input type="text" name="details[${rowCount}][item_name]" class="form-control" value="${requisit.item_name}" readonly />
                    </td>
                    <td>
                        <input type="hidden" name="details[${rowCount}][unit]" class="form-control" value="${requisit.unit}" readonly />
                        <input type="hidden" name="details[${rowCount}][base_unit]" class="form-control base-unit" value="${requisit.base_unit}">
                        <input type="text" name="details[${rowCount}][unit_name]" class="form-control" value="${requisit.unit_name}" readonly />
                    </td>
                    <td>
                        <input type="number" name="details[${rowCount}][qty]" class="form-control" value="${requisit.qty_left}" min="1" max="${requisit.qty_left}"/>
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][description]" class="form-control ">
                    </td>
                    <td id="pay-row-${rowCount}">
                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                    </td>
                </tr>
                `;
                // console.log(newRow);

                $('#parentTbody').append(newRow);
                rowCount++;
            }

        });

        $('#selectInvoiceModal').modal('hide');

    });

    function groupFilteredItems(items) {
        const groupedItems = {};

        items.forEach(item => {
            const key = `${item.item_id}-${item.unit}`; // Create a unique key based on item_id and unit

            // If the key doesn't exist, initialize it
            if (!groupedItems[key]) {
                groupedItems[key] = {
                    item_id: item.item_id,
                    sales_order_number:item.sales_order_number,
                    unit: item.unit,
                    item_name: item.items.item_name,
                    base_unit:item.base_unit,
                    unit_name:item.units.unit_name,
                    qty: parseFloat(item.qty),
                    qty_left:parseFloat(item.qty_left)
                };
            }else{
                // Sum the quantities
                groupedItems[key].sales_order_number += ','+item.sales_order_number;
                groupedItems[key].qty += parseFloat(item.qty) ;
            }


        });

        // Convert the grouped items object back to an array
        return Object.values(groupedItems);
    }

    // Add row functionality
    $('#add-row').on('click', function() {
        rowCount++; // Increment the rowCount for each new row
        var row = `<tr>
            <td>
                <input type="text" name="details[${rowCount}][sales_order_number]" class="form-control" value="" readonly />
            </td>
            <td>
                <div class="form-group">
                    <input type="text" class="form-control item-input" name="details[${rowCount}][item_name]" id="item-search-${rowCount}" placeholder="{{__('Search Item')}}">
                    <div id="item-search-results-${rowCount}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                        <!-- Search results will be injected here -->
                    </div>
                    <input type="hidden" name="details[${rowCount}][item_code]" class="form-control base-unit" readonly>
                    <input type="hidden" name="details[${rowCount}][base_unit]" class="form-control base-unit" readonly>
                </div>
            </td>

            <td>
                <select name="details[${rowCount}][unit]" class="form-control unit-dropdown" required>
                    <option value="">Pilih Unit</option>
                    @foreach ($itemUnits as $itemUnit)
                        <option value="{{ $itemUnit->unit }}">
                            {{ $itemUnit->unit_name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="1" name="details[${rowCount}][qty]" class="form-control qty-input" required min="1" value="1">
            </td>
            <td>
                <input type="text" name="details[${rowCount}][description]" class="form-control qty-input">
            </td>
            <td>
                <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
            </td>
        </tr>`;

        $('#inbound-details-table tbody').append(row);
        setupItemSearch(rowCount); // Attach event listener for item search in the new row
        initDropdown(); // Initialize dropdown for the new row
        // updateUnitDropdowns(); // Update unit dropdowns after adding a new row
    });

    // Function to update unit dropdowns (already in your code)
    // ...

    // Event listener for removing rows
    $(document).on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        // updateUnitDropdowns(); // Update units when a row is removed
        rowCount--;
    });

    // Initialize everything on page load
    initDropdown();
    // updateUnitDropdowns();


</script>
@endsection
