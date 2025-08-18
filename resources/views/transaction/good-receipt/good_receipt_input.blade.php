@extends('layouts.master')

@section('title','Input '. __('Good Receipt'))

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
<x-page-title title="{{__('Good Receipt')}}" pagetitle="{{__('Good Receipt')}} Transaction" />
<hr>
<div class="container content">
    <h2>{{__('Good Receipt')}} Input</h2>



    <form id="inbound-form" action="{{ route('transaction.warehouse.good_receipt.store') }}" method="POST">
        @csrf

        <!-- Card for Good Receipt Transaction -->
        <div class="card mb-3">
            <div class="card-header">{{__('Good Receipt')}} Transaction</div>
            <div class="card-body">
                <input type="hidden" name="token" id="token" value="{{$token}}">
                <div class="row">
                    <!-- Left Column: Supplier Code, Supplier Name, Address -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="search">{{__('Search Supplier')}}</label>
                            <input type="text" id="search" class="form-control"
                                placeholder="Search by Vendor Code, Name, or Address" autocomplete="off">
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
                        <div class="form-group">
                            <label for="document_date">No Dokumen Supplier</label>
                            <input type="text" id="vendor_number" name="vendor_number" class="form-control">
                        </div>
                    </div>
                    <!-- Center Column: Notes -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="item_category" class="form-label">{{__('Warehouse')}}</label>
                            <select class="form-select" id="warehouse_code" name="warehouse_code" required>
                                <option value="" disabled selected>Select {{__('Warehouse')}}</option>
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->warehouse_code }}">{{ $wh->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </div>
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

        <!-- Card for Inbound Details -->
        <div class="card mb-3">
            <div class="card-header">{{__('Good Receipt')}} Details</div>
            <div class="card-body">
                <table class="table" id="inbound-details-table">
                    <thead>
                        <tr>
                            <th>{{__('Purchase Order Number')}}</th>
                            <th>{{__('Item')}}</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="parentTbody">

                    </tbody>
                </table>
                <button type="button" class="btn btn-secondary mt-3" id="addRow">{{__('Select Document')}}</button>
                <button type="button" id="add-row" class="btn btn-primary mt-3">{{__('Add Item')}}</button>
            </div>
        </div>

        <div class="modal fade" id="selectInvoiceModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="selectInvoiceModalLabel">Select {{__('Purchase Order')}}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered" id="invoiceTable">
                            <thead>
                                <tr>
                                    <th>Select</th>
                                    <th>{{__('Purchase Order Number')}}</th>
                                    <th>Vendor</th>
                                    <th>Department</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($purchaseOrders as $po)
                                <tr data-supplier-id="{{ $po->supplier_code }}">
                                    <td style="text-align: center; vertical-align: middle;">
                                        <input type="checkbox" class="invoice-checkbox" value="{{ $po->purchase_order_number }}">
                                    </td>
                                    <td>{{ $po->purchase_order_number }}</td>
                                    <td>{{ $po->suppliers->supplier_name }}</td>
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
            <button type="submit mb-3" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit GR</button>
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
@endsection



@section('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {
            // Check if the success message is present
            @if(session('success'))
                // Show SweetAlert confirmation modal
                Swal.fire({
                    title: '{{__('Good Receipt')}} Created',
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
                            window.open("{{ route('transaction.warehouse.good_receipt.print', ['id' => ':id']) }}".replace(':id', id), '_blank');
                        }
                    }
                });
            @endif
        });


var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

    var rowCount = 0;

    const items = @json($items);
    const suppliers = @json($suppliers);
    const itemDetails = @json($itemDetails);
    const purchaseOrders = @json($purchaseOrders);
    let filteredItems = [];
    let selectedSupplier='';

    document.getElementById('document_date').valueAsDate = new Date();

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
        selectedSupplier = supplier.supplier_code;
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




    // Function to setup search input for dynamically added rows
    function setupItemSearch(rowId) {
        const searchInput = document.getElementById(`item-search-${rowId}`);
        const resultsContainer = document.getElementById(`item-search-results-${rowId}`);
        let selectedIndex = -1; // Track the currently selected item

        searchInput.addEventListener('input', function() {
            let query = this.value.toLowerCase();
            resultsContainer.innerHTML = ''; // Clear previous results
            resultsContainer.style.display = 'none'; // Hide dropdown by default
            selectedIndex = -1; // Reset selected index

            if (query.length > 0) {
                let filteredItems = items.filter(item =>
                    item.item_code.toLowerCase().includes(query) ||
                    item.items.item_name.toLowerCase().includes(query)
                );

                if (filteredItems.length > 0) {
                    resultsContainer.style.display = 'block'; // Show dropdown if matches found

                    filteredItems.forEach((item, index) => {
                        let listItem = document.createElement('a');
                        listItem.className = 'list-group-item list-group-item-action';
                        listItem.href = '#';
                        listItem.innerHTML = `
                            <strong>${item.items.item_name}</strong><br>
                            <small>Unit: ${item.unitn.unit_name}</small>
                        `;

                        listItem.addEventListener('click', function(e) {
                            e.preventDefault();

                            selectItem(item, rowId, resultsContainer);
                        });

                        resultsContainer.appendChild(listItem);
                    });
                }
            }
        });

        // Add keydown event listener for arrow and enter keys
        searchInput.addEventListener('keydown', function(e) {
            const items = resultsContainer.querySelectorAll('.list-group-item');

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (selectedIndex < items.length - 1) {
                    selectedIndex++;
                    updateSelection(items, selectedIndex);
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (selectedIndex > 0) {
                    selectedIndex--;
                    updateSelection(items, selectedIndex);
                }
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    items[selectedIndex].click();
                }
            }
        });

        // Function to update visual selection
        function updateSelection(items, index) {
            items.forEach((item, i) => {
                item.classList.toggle('active', i === index);
            });
            // Scroll selected item into view
            if (items[index]) {
                items[index].scrollIntoView({ block: 'nearest' });
            }
        }

        // Function to handle item selection
        function selectItem(item, rowId, resultsContainer) {
            document.querySelector(`input[name="details[${rowId}][item_code]"]`).value = item.item_code;
            document.querySelector(`input[name="details[${rowId}][base_unit]"]`).value = item.items.base_unit;
            document.querySelector(`input[name="details[${rowId}][item_name]"]`).value = item.items.item_name;
            document.querySelector(`select[name="details[${rowId}][unit]"]`).value = item.unitn.unit;
            resultsContainer.style.display = 'none'; // Hide dropdown after selection

            const matchedItems = itemDetails.filter(detail => detail.item_code === item.item_code);
            const allowedUnits = new Set(matchedItems.map(detail => detail.unit_conversion));
            const unitSelect = document.querySelector(`select[name="details[${rowId}][unit]"]`);

            Array.from(unitSelect.options).forEach(option => {
                option.hidden = !allowedUnits.has(option.value);
            });

            searchInput.focus(); // Return focus to input
        }
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
        purchaseOrders.forEach(element => {

            filteredItem = element.details.filter(detail =>
                selectedorder.some(selectedDetail =>
                    detail.purchase_order_number === selectedDetail.requNumber && element.supplier_code  == selectedSupplier
                )
            );
            console.log(filteredItem);

            filteredItems.push(...filteredItem);
        });

        const groupedItems = groupFilteredItems(filteredItems);

        $('#parentTbody').empty();
        groupedItems.forEach(requisit => {

            if(requisit.qty_left>0){
                const newRow = `
                <tr>
                    <td>
                        <input type="text" name="details[${rowCount}][purchase_order_number]" class="form-control" value="${requisit.purchase_order_number}" readonly />
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
                        <input type="number" name="details[${rowCount}][qty]" class="form-control" value="${requisit.qty_left}" min="1"/>
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
                    purchase_order_number:item.purchase_order_number,
                    unit: item.unit,
                    item_name: item.items.item_name,
                    base_unit:item.base_unit,
                    unit_name:item.units.unit_name,
                    qty: parseFloat(item.qty),
                    qty_left:parseFloat(item.qty_left)
                };
            }else{
                // Sum the quantities
                groupedItems[key].purchase_order_number += ','+item.purchase_order_number;
                groupedItems[key].qty += parseFloat(item.qty) ;
            }


        });

        // Convert the grouped items object back to an array
        return Object.values(groupedItems);
    }

    // Add row functionality
    $('#add-row').on('click', function() {
        let currentRow = rowCount;
         // Increment the rowCount for each new row
        var row = `<tr>
            <td>
                <input type="text" name="details[${rowCount}][purchase_order_number]" class="form-control" value="" readonly />
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
                    <option value="">Select Unit</option>
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
                <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
            </td>
        </tr>`;

        $('#inbound-details-table tbody').append(row);
        setupItemSearch(rowCount); // Attach event listener for item search in the new row
        initDropdown(); // Initialize dropdown for the new row
        document.getElementById(`item-search-${currentRow}`).focus();
        // new row
        rowCount++;
    });

    document.getElementById('inbound-form').addEventListener('submit', function(event) {
        let isValid = true;
        let qtyTotal = 0;
        let qtyUnwanted = 0;
        let isPo = false;
        $('#parentTbody tr').each(function(index) {
            let qty = $(this).find(`input[name="details[${index}][qty]"]`).val();
            let poNumber = $(this).find(`input[name="details[${index}][purchase_order_number]"]`).val();
            qtyTotal+=parseFloat(qty);
            if(!poNumber){
                qtyUnwanted+=parseFloat(qty);
            }else{
                isPo = true;
                console.log('a');

            }
        });
        console.log(isPo);



        if(isPo){
            if(qtyUnwanted>(qtyTotal*(0.2))){
                isValid=false;
                Swal.fire({
                    title: 'Warning!',
                    text: `Jumlah maksimal barang yang dapat diterima adalah 20% dari jumlah total barang lain`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
        }else{
            $('#parentTbody tr').each(function(index) {
                let qty = $(this).find(`input[name="details[${index}][qty]"]`).val();
                // if(parseFloat(qty)>75){
                //     isValid=false;
                //     Swal.fire({
                //         title: 'Warning!',
                //         text: `Jumlah barang tidak dapat melebihi 75`,
                //         icon: 'warning',
                //         confirmButtonText: 'OK'
                //     });
                // }
            });
        }

        if(!isValid){
            event.preventDefault();
        }
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
