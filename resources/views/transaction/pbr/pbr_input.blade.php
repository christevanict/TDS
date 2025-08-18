@extends('layouts.master')

@section('title',  __('Sales PBR'))
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
    <x-page-title title="{{__('Sales PBR')}}" pagetitle="{{__('Sales PBR')}} Input" />
    <hr>
    <div class="container content">
        <h2>{{__('Sales PBR')}} Input</h2>

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

        <form id="si-form" action="{{ isset($salesInvoice) ? route('transaction.pbr.update', $salesInvoice->id) : route('transaction.pbr.store') }}" method="POST">
            @csrf
            @if(isset($salesInvoice)) @method('PUT') @endif
            <div class="card mb-3">
                <div class="card-header">{{__('Sales PBR')}} {{__('Information')}}</div>
                <div class="card-body">
                    <input type="hidden" id="checkHPP" value="0">
                    <input type="hidden" name="token" id="token" value="{{$token}}">
                    <div class="row">
                        <div class="col-md-6">
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
                                <input type="text" name="customer_code" id="customer_code" class="form-control" readonly required>
                                <input type="hidden" name="category_customer" id="category_customer" class="form-control" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" readonly required>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly>
                            </div>
                            <br>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Note</label>
                                <textarea name="notes" class="form-control" rows="5">{{ old('notes', '') }}</textarea>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" name="document_date" id="document_date" class="form-control date-picker" required value="{{ old('document_date', $salesInvoice->document_date ?? date('Y-m-d')) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="department_code">Departemen</label>
                                <input type="text" name="department_name" id="department_name" class="form-control" value="{{$dpName}}" readonly>
                                <input type="hidden" name="department_code" id="department_code" class="form-control" value="{{$dp_code}}" >
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Card for {{__('Sales PBR')}} Details -->
            <div class="card mb-3">
                <div class="card-header">{{__('Sales PBR')}} Details</div>
                <div class="card-body">
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <td style="min-width: 220px">{{__('Sales Order Number')}}</td>
                            <td style="min-width: 270px">{{__('Warehouse')}}</td>
                            <td style="min-width: 330px">{{__('Item')}}</td>
                            <td style="min-width: 80px">Unit</td>
                            <td style="min-width: 150px">Qty</td>
                            <td style="min-width: 170px">{{__('Price')}}</td>
                            <td style="min-width: 70px">Disc (%)</td>
                            <td style="min-width: 170px">{{__('Discount')}}</td>
                            <td style="min-width: 170px">Nominal</td>
                            <td>Action</td>
                        </thead>
                        <tbody id="parentTbody">
                            <!-- Parent rows will be added dynamically here -->
                        </tbody>
                    </table>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="addRow">{{__('Select Document')}}</button>
                    <button type="button" id="add-row" class="btn btn-primary mt-3">Tambah Barang</button>
                </div>
            </div>

            <div class="modal fade" id="selectInvoiceModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectInvoiceModalLabel">Pilih {{__('Sales Order')}}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered" id="invoiceTable">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>{{__('Sales Order Number')}}</th>
                                        <th>{{__('Customer')}}</th>
                                        <th>Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salesOrder as $pi)
                                    <tr data-customer-id="{{ $pi->customer_code }}">
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" class="invoice-checkbox" value="{{ $pi->sales_order_number }}">
                                        </td>
                                        <td>{{ $pi->sales_order_number }}</td>
                                        <td>{{ $pi->customers->customer_name }}</td>
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

            <div class="modal fade modal-lg" id="itemModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectInvoiceModalLabel">Pilih {{__('Item')}}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered" id="invoiceTable">
                                <button id="selectAllBtn" class="btn btn-primary mb-3">Pilih Semua</button>
                                <thead>
                                    <tr>
                                        <th>Pilih</th>
                                        <th>{{__('Item Code')}}</th>
                                        <th>{{__('Item Name')}}</th>
                                        <th>Jumlah</th>
                                        <th>Stok</th>
                                        <th>Nominal</th>
                                        <th>{{__('Sales Order Number')}}</th>
                                        <th>{{__('Warehouse')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="chooseItem">Select</button>
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Sales PBR')}}</button>
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
</div>

@section('scripts')
<script>

document.addEventListener('DOMContentLoaded', function () {
            // Check if the success message is present
            @if(session('success'))
                // Show SweetAlert confirmation modal
                Swal.fire({
                    title: '{{__('Sales PBR')}} Created',
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
                            window.location.href = "{{ route('pbr.print', ['id' => ':id']) }}".replace(':id', id);
                        }
                    }
                });
            @endif
        });
let rowCount = 0;
const customers = @json($customers);
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

    document.getElementById('search').addEventListener('input', function () {
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        if (query.length > 0) {
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
                    customerId = customer.customer_code;
                    document.getElementById('customer_name').value = customer.customer_name;
                    document.getElementById('address').value = customer.address;
                    document.getElementById('category_customer').value = customer.category_customer;
                    resultsContainer.style.display = 'none';

                    // Show all rows if no customer is selected
                    if (customerId === "") {
                        $('#invoiceTable tbody tr').show();
                    } else {
                        // Hide rows that do not match the selected customer
                        $('#invoiceTable tbody tr').each(function () {
                            const customerIds = $(this).data('customer-id');
                            let customerOrigin  = customers.find((c)=>c.customer_code ==customerIds);

                            if (customer.group_customer == customerOrigin.group_customer) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    }

                });
                resultsContainer.appendChild(listItem);
            });
        }
    }
    });
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
    let customerId='';
    let items = @json($items);
    let itemDetails = @json($itemDetails);
    let salesOrder = @json($salesOrder);
    let dp_code = @json($dp_code);

    $('#selectInvoicesButton').click(function() {

    const selectedRequisition = [];
        $('#invoiceTable .invoice-checkbox:checked').each(function() {
            const requNumber = $(this).val();

            selectedRequisition.push({
                requNumber: requNumber
            });
        });

        filteredSO = salesOrder.filter((so)=>
            selectedRequisition.some(selectedDetail =>
                so.sales_order_number === selectedDetail.requNumber
            )
        );
        let salesOrderD=[];
        filteredSO.forEach(element => {
            element.details.forEach(e => {
                salesOrderD.push(e)
            });
        });


        filteredPRdetails = salesOrderD.filter(detail =>
            selectedRequisition.some(selectedDetail =>
                detail.sales_order_number === selectedDetail.requNumber
            )
        );

        const datas = [];
        filteredPRdetails.forEach(detail => {
                const a={
                    'id':detail.id,
                    'item_id':detail.item_id,
                    'item_name':detail.items.item_name,
                    'warehouse_code':detail.items.warehouses?detail.items.warehouses.warehouse_code:'',
                    'warehouse_name':detail.items.warehouses?detail.items.warehouses.warehouse_name:'',
                    'sales_order_number':detail.sales_order_number,
                    'price':detail.price,
                    'base_qty':detail.base_qty,
                    'qty':detail.qty_left,
                    'unit':detail.unit,
                    'unit_name':detail.units.unit_name,
                    'notes':detail.notes,
                }
                datas.push(a);
        });

        datas.sort((a, b) => {
            // First, compare by warehouse_name
            if (a.warehouse_name < b.warehouse_name) return -1;
            if (a.warehouse_name > b.warehouse_name) return 1;

            // If warehouse_name is the same, compare by sales_order_number
            if (a.sales_order_number < b.sales_order_number) return -1;
            if (a.sales_order_number > b.sales_order_number) return 1;

            return 0; // If both are the same, keep the order unchanged
        });
        // console.log('Data: ', datas);


        let formData = new FormData();
        formData.append('details', JSON.stringify(datas));
        $.ajax({
            url: "{{ route('getStockByDatePerItem') }}",
            headers: {
                "X-CSRF-TOKEN": "{{csrf_token()}}"
            },
            type: "POST",
            data:formData,
            success: function (rs) {
                datas.forEach(function(item) {
                    // Find matching stock item by item_id
                    const stockItem = rs.find(stock => stock.item_id === item.item_id);

                    // If found, add the stock property to the item
                    if (stockItem) {
                        item.stock = stockItem.stock+"";
                    } else {
                        // If no stock data found for this item, set default value
                        item.stock = 0+"";
                    }
                });
                window.selectedItemsData = datas;
                populateItemModal(datas);
                $('#selectInvoiceModal').modal('hide');
                $('#itemModal').modal('show');

            },
            error: function (xhr, ajaxOptions, thrownError) {
                //let data = JSON.parse(xhr.responseText);
                console.log(xhr, ajaxOptions, thrownError);

            },
            cache: false,
            contentType: false,
            processData: false,
        });

    });



    function populateItemModal(items) {
        const tbody = $('#itemModal tbody');
        tbody.empty();

        items.forEach((item, index) => {
            const row = `
                <tr>
                    <td style="text-align: center; vertical-align: middle;">
                        <input type="checkbox" class="item-checkbox" data-index="${index}">
                    </td>
                    <td>${item.item_id}</td>
                    <td>${item.item_name}</td>
                    <td>${item.qty}</td>
                    <td>${item.stock}</td>
                    <td>${item.price.split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</td>
                    <td>${item.sales_order_number}</td>
                    <td>${item.warehouse_name}</td>
                </tr>
            `;
            tbody.append(row);
            let allSelected = false; // Track the state of selection
            $('#selectAllBtn').off('click').on('click', function(event) {
                event.preventDefault(); // Prevent form submission

                const totalItems = $('.item-checkbox').length; // Total number of checkboxes
                const newState = !allSelected; // The intended new state (true = select, false = deselect)

                // If trying to select all and count > 10, show warning and cancel
                if (newState && totalItems > 10) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Barang melebihi batas',
                        text: 'Tidak bisa memilih lebih dari 10 barang',
                        confirmButtonText: 'OK'
                    });
                    return; // Exit the function without changing selection
                }

                // Proceed with toggling selection
                allSelected = newState;
                $('.item-checkbox').prop('checked', allSelected);
                $(this).text(allSelected ? 'Deselect All' : 'Select All');
            });
        });
    }

    // Add search functionality for itemModal
    $('#itemModal').on('shown.bs.modal', function () {
        // Add search bar if not already present
        if (!$(this).find('.search-container').length) {
            $(this).find('.modal-body').prepend(`
                <div class="search-container mb-3">
                    <input type="text" class="form-control" id="item-search" placeholder="Search items...">
                </div>
            `);
        }

        $('#item-search').off('input').on('input', function() {
            const searchTerm = $(this).val().toLowerCase();
            $('#itemModal tbody tr').each(function() {
                const itemName = $(this).find('td:eq(2)').text().toLowerCase();
                const itemCode = $(this).find('td:eq(1)').text().toLowerCase();
                const salesOrder = $(this).find('td:eq(3)').text().toLowerCase();

                if (itemName.includes(searchTerm) || itemCode.includes(searchTerm) || salesOrder.includes(searchTerm)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    });

    // Handle item selection from itemModal
    // Replace the existing $('#chooseItem').click handler
    $('#chooseItem').click(function() {
        const selectedIndices = [];
        $('#itemModal .item-checkbox:checked').each(function() {
            selectedIndices.push($(this).data('index'));
        });

        const selectedItems = window.selectedItemsData.filter((_, index) =>
            selectedIndices.includes(index)
        );

        const itemsToGroup = [];
        const itemsToKeepSeparate = [];

        selectedItems.forEach(item => {
            // Assuming nominal is a property of item; adjust if it's named differently
            if (parseFloat(item.nominal || 0) === 0) {
                itemsToKeepSeparate.push(item); // Keep these items as-is
            } else {
                itemsToGroup.push(item); // These will be grouped
            }
        });

        // Group items by item_id and sum quantities
        // Group items with nominal > 0 by item_id and sum quantities
        const groupedItems = {};
        itemsToGroup.forEach(item => {
            if (!groupedItems[item.item_id]) {
                groupedItems[item.item_id] = {
                    ...item,
                    qty: 0,
                    base_qty: item.base_qty, // Keep base_qty from first occurrence
                    sales_order_numbers: new Set() // To track unique sales order numbers
                };
            }
            groupedItems[item.item_id].qty += parseFloat(item.qty);
            groupedItems[item.item_id].sales_order_numbers.add(item.sales_order_number);
        });

        // Convert grouped items back to array
        const groupedItemsArray = Object.values(groupedItems).map(item => ({
            ...item,
            sales_order_number: Array.from(item.sales_order_numbers).join(', ') // Combine sales order numbers
        }));

        // Combine grouped items with items that weren't grouped (nominal = 0)
        const combinedItems = [...groupedItemsArray, ...itemsToKeepSeparate];



        // Check existing rows and combine if item_id matches
        const existingRows = $('#parentTbody tr');
        combinedItems.forEach(requisit => {
            let rowUpdated = false;

            // Check if item already exists in table
            existingRows.each(function() {
                const existingItemId = $(this).find('input[name$="[item_id]"]').val();
                if (existingItemId === requisit.item_id) {
                    const qtyInput = $(this).find('input[name$="[qty]"]');
                    const currentQty = parseFloat(qtyInput.val()) || 0;
                    const newQty = currentQty + parseFloat(requisit.qty);
                    qtyInput.val(newQty);
                    qtyInput.attr('max', newQty); // Update max attribute

                    const price = parseFloat($(this).find('input[name$="[price]"]').val().replace(/,/g, '')) || 0;
                    const discPercent = parseFloat($(this).find('input[name$="[disc_percent]"]').val().replace(/,/g, '')) || 0;
                    const discNominal = parseFloat($(this).find('input[name$="[disc_nominal]"]').val().replace(/,/g, '')) || 0;

                    const nominalInput = $(this).find('input[name$="[nominal]"]');
                    const newNominal = ((newQty * price) - ((newQty * price) * discPercent / 100) - discNominal).toString();
                    nominalInput.val(newNominal.replace(/\B(?=(\d{3})+(?!\d))/g, ','));

                    // Update sales order numbers
                    const salesOrderInput = $(this).find('input[name$="[sales_order_number]"]');
                    const existingSalesOrders = new Set(salesOrderInput.val().split(', '));
                    requisit.sales_order_numbers.forEach(num => existingSalesOrders.add(num));
                    salesOrderInput.val(Array.from(existingSalesOrders).join(', '));

                    rowUpdated = true;
                    return false; // Break the each loop
                }
            });
            let units = [];
            let details = itemDetails.filter((e)=>requisit.item_id==e.item_code);

            details.forEach(element => {
                if(details.department_code==dp_code){
                    units.push(element.unit_conversion);
                }
            });

            // If item wasn't found in existing rows, add new row
            if (!rowUpdated) {
                const currentRow = rowCount;
                const newRow = `
                    <tr>
                        <td>
                            <input type="text" name="details[${rowCount}][sales_order_number]" class="form-control" value="${requisit.sales_order_number}" readonly />
                            <input type="hidden" name="details[${rowCount}][so_id]" class="form-control" value="${requisit.id}" readonly />
                        </td>
                        <td>
                            <input type="hidden" name="details[${rowCount}][warehouse_code]" value="${requisit.warehouse_code}" />
                            <input type="text" name="details[${rowCount}][warehouse_name]" class="form-control" value="${requisit.warehouse_name}" readonly />
                        </td>
                        <td>
                            <input type="hidden" id="item_code_${rowCount}" name="details[${rowCount}][item_id]" class="form-control" value="${requisit.item_id}" readonly />
                            <input type="text" name="details[${rowCount}][item_name]" class="form-control" value="${requisit.item_name}" readonly />
                        </td>
                        <td>
                            <select id="unit_${rowCount}" name="details[${rowCount}][unit]" class="form-control unit-dropdown">
                                @foreach ($itemUnits as $unit)
                                    <option value="{{$unit->unit}}" >{{$unit->unit_name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="hidden" id="conversion_value_${rowCount}" name="details[${rowCount}][base_qty]" value="${requisit.base_qty}" readonly />
                            <input type="number" id="qty_${rowCount}" name="details[${rowCount}][qty]" class="form-control" value="${requisit.qty}" min="1" max="${requisit.qty}"/>
                        </td>
                        <td>
                            <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][price]" id="price_${rowCount}" class="form-control text-end" value="${requisit.price.split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
                        </td>
                        <td>
                            <input type="text" oninput="formatNumber(this)" id="disc_percent_${rowCount}" name="details[${rowCount}][disc_percent]" class="form-control text-end" value="0" />
                        </td>
                        <td>
                            <input type="text" oninput="formatNumber(this)" id="disc_nominal_${rowCount}" name="details[${rowCount}][disc_nominal]" class="form-control text-end" value="0" />
                        </td>
                        <td>
                            <input type="text" name="details[${rowCount}][nominal]" id="nominal_${rowCount}" class="form-control text-end nominal" value="${(requisit.price * requisit.qty * requisit.base_qty).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
                        </td>
                        <td id="pay-row-${rowCount}">
                            <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                        </td>
                    </tr>
                `;
                $('#parentTbody').append(newRow);
                const unitSelect = document.getElementById(`unit_${currentRow}`);
                if (unitSelect && units.length > 0) {
                    Array.from(unitSelect.options).forEach(option => {
                        option.style.display = units.includes(option.value) ? 'block' : 'none';
                        option.selected = (option.value === requisit.unit);
                    });

                    // Set default value to first available unit
                    const firstAvailableOption = Array.from(unitSelect.options).find(option =>
                        units.includes(option.value)
                    );
                    if (firstAvailableOption) {
                        unitSelect.value = firstAvailableOption.value;
                    }
                }
                document.getElementById(`unit_${currentRow}`).value = requisit.unit;
                setupUnitChangeListener(currentRow);
                // Add event listeners for the new row
                document.getElementById(`qty_${currentRow}`).addEventListener('input', function() {
                    updateNominalValue(currentRow);
                });
                document.getElementById(`price_${currentRow}`).addEventListener('input', function() {
                    updateNominalValue(currentRow);
                });
                document.getElementById(`disc_percent_${currentRow}`).addEventListener('input', function() {
                    updateNominalValue(currentRow);
                });
                document.getElementById(`disc_nominal_${currentRow}`).addEventListener('input', function() {
                    updateNominalValue(currentRow);
                });

                rowCount++;
            }
        });

        calculateTotals();
        addInputListeners();
        $('#itemModal').modal('hide');
    });

    // document.getElementById('si-form').addEventListener('submit', function (e) {
    //     // Get all rows from the dynamicTable tbody
    //     const rows = document.querySelectorAll('#dynamicTable tbody tr');

    //     // Initialize a variable to hold the first warehouse_code value
    //     let initialWarehouseCode = null;

    //     // Flag to track if all values are the same
    //     let allValuesMatch = true;

    //     if (rows.length > 10) {
    //         e.preventDefault(); // Prevent form submission
    //         Swal.fire({
    //             title: 'Warning!',
    //             text: 'The maximum number of items is 10.',
    //             icon: 'warning',
    //             confirmButtonText: 'OK'
    //         });
    //         return; // Stop further validation
    //     }

    //     rows.forEach((row, index) => {
    //         // Get the value of the warehouse_code input in the current row
    //         const warehouseCodeInput = row.querySelector('input[name^="details"][name$="[warehouse_code]"]');

    //         if (warehouseCodeInput) {
    //             const currentValue = warehouseCodeInput.value;

    //             // If this is the first row, store the value
    //             if (index === 0) {
    //                 initialWarehouseCode = currentValue;
    //             } else {
    //                 // Compare current value with the initial value
    //                 if (currentValue !== initialWarehouseCode) {
    //                     allValuesMatch = false;
    //                 }
    //             }
    //         }
    //     });

    //     if (!allValuesMatch) {
    //         // Prevent form submission
    //         e.preventDefault();

    //         // Show SweetAlert warning
    //         Swal.fire({
    //             title: 'Warning!',
    //             text: 'All items must be in the same warehouse. .',
    //             icon: 'warning',
    //             confirmButtonText: 'OK'
    //         });
    //     }
    // });




    document.addEventListener('click', function(event) {
        if (!event.target.closest('#search')) {
            document.getElementById('search-results').style.display = 'none';
            document.getElementById('search').value=''; }});


    // Initialize row count

    document.addEventListener('DOMContentLoaded', function () {

        // Function to update price based on item and unit selections

        $('#addRow').click(function() {
            $('#selectInvoiceModal').modal('show');
        });

        // Event delegation for row removal
        document.querySelector('#dynamicTable').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                rowCount--; // Decrement row count
            }
        });

        // Initialize the first row
    });

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
                <input type="hidden" id="conversion_value_${rowCount}" name = "details[${rowCount}][base_qty]" />
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
        setupItemSearch(currentRow);
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

    // Add CSS for active item (add this once in your main script)
    const style = document.createElement('style');
    style.textContent = `
        .list-group-item.active {
            background-color: blue;
            border-color: #dee2e6;
        }
    `;

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
    function updatePrice() {
        const rowId = this.closest('tr').getAttribute('data-row-id');
        const itemCode = document.getElementById(`item_code_${rowId}`).value;

        const itemDetail = itemDetails.find(detail => detail.item_code === itemCode);
            // console.log(itemDetail);
    }

    document.getElementById('add-row').addEventListener('click', addNewRow);

    {{--$("#si-form").on("submit",function(e) {
        let formData = new FormData(this);
        let aa = true;
        if($("#checkHPP").val() == 1 ||$("#checkHPP").val() == "1"){
            aa = false;
        }
        if(aa){
            e.preventDefault();
            $.ajax({
                url: "{{ route('getStockByDate') }}",
                type: "POST",
                data: formData,
                success: function (rs) {
                    if(rs.length > 0){
                        let itName = "";
                        rs.forEach((a) => itName += "<li>" + a.item_name + " dengan sisa stok: " + a.stock + "</li>");
                        itName = "<ul>"+itName+"</ul>";
                        Swal.fire({
                            title: 'Tidak bisa disimpan!',
                            html: "Item berikut qty tidak mencukupi<br/>"+itName,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }else{
                        console.log()
                        $("#checkHPP").val(1);
                        $("#si-form").submit();
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
