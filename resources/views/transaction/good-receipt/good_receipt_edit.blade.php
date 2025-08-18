@extends('layouts.master')

@section('title','Edit '. __('Good Receipt'))

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
    <form id="print-form" action="{{ route('transaction.warehouse.good_receipt.print', $goodReceipt->id) }}" target="_blank" method="GET" style="display:inline;">
        <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
            Print GR</button>
    </form>
    <div id="message-container">
        @if(session('success'))
            <div id="success-message" class="alert alert-success fade show">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div id="error-message" class="alert alert-error fade show">{{ session('error') }}</div>
        @endif
    </div>

    <form id="inbound-form" action="{{ route('transaction.warehouse.good_receipt.update',$goodReceipt->id) }}" method="POST">
        @csrf

        <!-- Card for {{__('Good Receipt')}} Transaction -->
        <div class="card mb-3">
            <div class="card-header">{{__('Good Receipt')}} Transaction</div>
            <div class="card-body">
                <div class="row">
                    <!-- Left Column: Supplier Code, Supplier Name, Address -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="supplier_code">{{__('Supplier Code')}}</label>
                            <input type="text" name="supplier_code" id="supplier_code" class="form-control" readonly value="{{$goodReceipt->supplier_code}}">
                        </div>
                        <div class="form-group">
                            <label for="supplier_name">{{__('Supplier Name')}}</label>
                            <input type="text" name="supplier_name" id="supplier_name" class="form-control" readonly value="{{$goodReceipt->supplier->supplier_name}}">
                        </div>
                        <div class="form-group">
                            <label for="address">{{__('Address')}}</label>
                            <input type="text" name="address" id="address" class="form-control" readonly value="{{$goodReceipt->supplier->address}}">
                        </div>
                        <div class="form-group">
                            <label for="document_date">No Dokumen Supplier</label>
                            <input type="text" id="vendor_number" name="vendor_number" class="form-control" value="{{$goodReceipt->vendor_number??''}}">
                        </div>
                    </div>
                    <!-- Center Column: Notes -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="item_category" class="form-label">{{__('Warehouse')}}</label>
                            <select class="form-select" id="warehouse_code" name="warehouse_code" required>
                                <option value="" disabled selected>Pilih {{__('Warehouse')}}</option>
                                @foreach ($warehouses as $wh)
                                    <option value="{{ $wh->warehouse_code }}"
                                        @if ($goodReceipt->warehouse_code==$wh->warehouse_code)
                                            selected
                                        @endif
                                        >{{ $wh->warehouse_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">{{__('Notes')}}</label>
                            <textarea name="notes" id="notes" class="form-control" rows="5">{{$goodReceipt->notes}}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="document_date">{{__('Document Date')}}</label>
                            <input type="date" id="document_date" name="document_date" class="form-control date-picker" required value="{{$goodReceipt->document_date}}">
                        </div>
                    </div>


                </div>

            </div>
        </div>

        <!-- Card for Inbound Details -->
        <div class="card mb-3">
            <div class="card-header">Inbound Details</div>
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
                        @foreach ($goodReceipt->goodReceiptDetails as $index =>$detail)

                        <tr>
                            <td>
                                <input type="text" name="details[{{$index}}][purchase_order_number]" class="form-control" value="{{$detail->purchase_order_number}}" readonly />
                            </td>
                            <td>
                                <input type="hidden" name="details[{{$index}}][item_code]" class="form-control" value="{{$detail->item_id}}" readonly />
                                <input type="text" name="details[{{$index}}][item_name]" class="form-control" value="{{$detail->items->item_name}}" readonly />
                            </td>
                            <td>
                                <input type="hidden" name="details[{{$index}}][base_unit]" class="form-control base-unit" value="{{$detail->base_unit}}">
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
                                <input type="number" step="1" name="details[{{$index}}][qty]" class="form-control qty-input" required min="1" value="{{$detail->qty}}" max="{{ ($poDetails->where('purchase_order_number', $detail->purchase_order_number)->first()?$poDetails->where('purchase_order_number', $detail->purchase_order_number)->where('item_id', $detail->item_id)->where('unit', $detail->unit)->sum('qty_left'):9999999) + $detail->qty }}">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                            </td>
                        </tr>

                        @endforeach
                    </tbody>
                </table>
                <button type="button" id="add-row" class="btn btn-primary">{{__('Add Item')}}</button>
            </div>
        </div>

        <!-- Submit button with margin -->
        @if($editable)
        <div class="form-group submit-btn">
            <button type="submit" class="btn btn-primary mb-3" @if(!in_array('update', $privileges)) disabled @endif>Update {{__('Good Receipt')}}</button>
        </div>
        @endif
        <a class="btn btn-secondary mb-3 mt-2" href="{{route('transaction.warehouse.good_receipt')}}">Back</a>
    </form>

    @if($goodReceipt->status!='Cancelled'&&$goodReceipt->status!='Closed')
    <form id="cancel-form" action="{{ route('transaction.good_receipt.cancel', $goodReceipt->id) }}" method="POST" >
        @csrf
        @method('POST')
        <input type="hidden" name="reason" id="cancellation-reason">
        <button type="button" class="btn btn-danger mb-3 " onclick="confirmCancel(event,'{{ $goodReceipt->id }}')"
            @if(!in_array('delete', $privileges)) disabled @endif
        >Cancel GR</button>
    </form>
    @endif

    <form id="delete-form" action="{{route('transaction.warehouse.good_receipt.destroy',$goodReceipt->id)}}" method="POST">
        @csrf
        @method('POST')
        <input type="hidden" name="reason" id="deletion-reason">
        <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event)"
        @if(!in_array('delete', $privileges)) disabled @endif
        ><i class="material-icons-outlined">delete</i></button>
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

function confirmCancel(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to cancel this good receipt?',
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

    function confirmDelete(event) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this good receipt?',
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
    var rowCount = 0;

    const grDetails = @json($goodReceipt->goodReceiptDetails);
    rowCount = grDetails.length;
    const items = @json($items);
    const itemDetails = @json($itemDetails);
    // document.getElementById('document_date').valueAsDate = new Date();





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

    // Add row functionality
    $('#add-row').on('click', function() {
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
        rowCount++;
        // updateUnitDropdowns(); // Update unit dropdowns after adding a new row
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


        if(isPo){
            if(qtyUnwanted>(qtyTotal*(0.2))){
                isValid=false;
                Swal.fire({
                    title: 'Warning!',
                    text: `The quantity of unwanted items is over the limit (20% of the total quantity)`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
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
