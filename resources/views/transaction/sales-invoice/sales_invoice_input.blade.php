@extends('layouts.master')

@section('title',  __('Sales Invoice'))
@section('css')
<style>
    #search-result-dest {
        max-height: 200px; /* Set your desired maximum height */
        overflow-y: auto; /* Enable vertical scrolling */
        border: 1px solid #ccc; /* Optional: Add a border */
        background-color: #fff; /* Optional: Set a background color */
        display: none; /* Initially hidden */
    }
    th.sortable {
    cursor: pointer;
    position: relative;
}
th.sortable:hover {
    background-color: #f1f1f1;
}
th.sortable::after {
    content: '';
    margin-left: 5px;
    display: inline-block;
    width: 0;
    height: 0;
    border-left: 5px solid transparent;
    border-right: 5px solid transparent;
}
th.sortable.asc::after {
    border-bottom: 5px solid #000;
}
th.sortable.desc::after {
    border-top: 5px solid #000;
}
</style>
@endsection
@section('content')
<div class="row">
    <x-page-title title="{{__('Sales Invoice')}}" pagetitle="{{__('Sales Invoice')}} Input" />
    <hr>
    <div class="container content">
        <h2>{{__('Sales Invoice')}} Input</h2>


        <form id="si-form" action="{{ isset($salesInvoice) ? route('transaction.sales_invoice.update', $salesInvoice->id) : route('transaction.sales_invoice.store') }}" method="POST">
            @csrf
            @if(isset($salesInvoice)) @method('PUT') @endif
            <div class="card mb-3">
                <div class="card-header">{{__('Sales Invoice')}} {{__('Information')}}</div>
                <div class="card-body">
                    <input type="hidden" id="checkHPP" value="0">
                    <input type="hidden" name="token" id="token" value="{{$token}}">
                    <div class="row">
                        <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">{{__('Search Customer')}}</label>
                                    <input type="text" id="search" class="form-control" placeholder="Search by Customer Code, Name, or Address" autocomplete="off">
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
                            {{-- <div class="form-group">
                                <label for="sales_invoice_number">{{__('Sales Invoice Number')}}</label>
                                <input type="text" name="sales_invoice_number" class="form-control" readonly required value="{{ old('sales_invoice_number', $salesInvoice->sales_invoice_number ?? $sales_invoice_number) }}">
                            </div> --}}
                            <br>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="notes">Note</label>
                                <textarea name="notes" class="form-control" rows="5">{{ old('notes', $salesInvoice->notes ?? '') }}</textarea>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" name="document_date" id="document_date" class="form-control date-picker" required value="{{ old('document_date', $salesInvoice->document_date ?? date('Y-m-d')) }}">
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
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="">Contract Number</label>
                                <input type="text" id="contract_number"  name="contract_number" class="form-control"  value="">
                            </div>
                            <div class="form-group d-none">
                                <label for="disc_nominal">{{__('Discount')}}</label>
                                <input type="text" id="disc_nominal" oninput="formatNumber(this)" name="disc_nominal" class="form-control text-end nominal" required value="0">
                            </div>
                            <div class="form-group d-none">
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
                            <br>
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
                            <td style="min-width: 330px">{{__('Item')}}</td>
                            <td style="min-width: 150px">Qty</td>
                            <td style="min-width: 170px">{{__('Price')}}</td>
                            <td style="min-width: 70px">Disc (%)</td>
                            <td style="min-width: 170px">{{__('Discount')}}</td>
                            <td style="min-width: 170px">Nominal</td>
                            <td style="min-width: 220px">Keterangan</td>
                            <td>Action</td>
                        </thead>
                        <tbody id="parentTbody">
                            <!-- Parent rows will be added dynamically here -->
                        </tbody>
                    </table>
                    </div>
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
                                        <td>{{ $pi->sales_order_number }} @if($pi->is_pbr) <span class="badge
                                        bg-info">
                                        PBR
                                    </span> @endif</td>
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
                            <table class="table table-bordered" id="invoiceTable2">
                                <button id="selectAllBtn" class="btn btn-primary mb-3">Pilih Semua</button>
                                <thead>
                                    <tr>
                                        <th>Pilih</th>
                                        <th class="sortable" data-column="item_code">{{__('Item Code')}}</th>
                                        <th class="sortable" data-column="item_name">{{__('Item Name')}}</th>
                                        <th class="sortable" data-column="jumlah">Jumlah</th>
                                        <th class="sortable" data-column="stock">Stok</th>
                                        <th class="sortable" data-column="nominal">Nominal</th>
                                        <th class="sortable" data-column="sales_order_number">{{__('Sales Order Number')}}</th>
                                        <th class="sortable" data-column="warehouse">{{__('Warehouse')}}</th>
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
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Sales Invoice')}}</button>
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
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>

document.addEventListener('DOMContentLoaded', function () {
            // Check if the success message is present
            @if(session('success'))
                // Show SweetAlert confirmation modal
                Swal.fire({
                    title: '{{__('Sales Invoice')}} Created',
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
                            window.open("{{ route('sales_invoice.print', ['id' => ':id']) }}".replace(':id', id), '_blank');
                        }
                    }
                });
            @endif
        });
let rowCount = 0;
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);
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
        const conversion = document.getElementById(`conversion_value_${row}`).value.replace(/,/g, '') || 0;

        // const disc_percent = parseFloat(document.getElementById(`disc_percent_${row}`).value.replace(/,/g, '')) || 0;
        // const disc_nominal = parseFloat(document.getElementById(`disc_nominal_${row}`).value.replace(/,/g, '')) || 0;

        const nominalInput = document.getElementById(`nominal_${row}`);
        const nominalValue = ((qty * price)-((qty * price)*disc_percent/100)-disc_nominal)+"";

        let formattedValue = nominalValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        nominalInput.value = formattedValue; // Update nominal value
        calculateTotals();
    }
    const customers = @json($customers);
    let customerId='';
    let items = @json($items);
    let itemDetails = @json($itemDetails);
    let salesOrder = @json($salesOrder);
    let dp_code = @json($department_TDS);

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
                datas.sort((a, b) => b.stock - a.stock);

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
        let sortColumn = 'stock'; // Default sort column
        let sortDirection = 'desc'; // Default sort direction

        // Function to sort data
        function sortData(data, column, direction) {
            return data.sort((a, b) => {
                let valueA = a[column];
                let valueB = b[column];

                // Handle numeric columns
                if (['qty', 'stock', 'price'].includes(column)) {
                    valueA = Number(valueA) || 0;
                    valueB = Number(valueB) || 0;
                    return direction === 'asc' ? valueA - valueB : valueB - valueA;
                }

                // Handle string columns
                valueA = valueA ? valueA.toString().toLowerCase() : '';
                valueB = valueB ? valueB.toString().toLowerCase() : '';
                if (valueA < valueB) return direction === 'asc' ? -1 : 1;
                if (valueA > valueB) return direction === 'asc' ? 1 : -1;
                return 0;
            });
        }

        // Function to render table
        function renderTable(data) {
            tbody.empty();
            data.forEach((item, index) => {
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
            });
        }

        // Initial render with default sorting
        renderTable(sortData([...items], sortColumn, sortDirection));

        // Sorting handler for table headers
        $('.sortable').off('click').on('click', function() {
            const column = $(this).data('column');
            if (sortColumn === column) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortDirection = 'asc';
            }
            sortColumn = column;

            // Update sort indicators
            $('.sortable').removeClass('asc desc');
            $(this).addClass(sortDirection);

            // Re-render table with sorted data
            renderTable(sortData([...items], sortColumn, sortDirection));
        });

        // Retain existing select all functionality
        let allSelected = false;
        $('#selectAllBtn').off('click').on('click', function(event) {
            event.preventDefault();
            const totalItems = $('.item-checkbox').length;
            const newState = !allSelected;

            if (newState && totalItems > 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Barang melebihi batas',
                    text: 'Hanya 10 barang pertama yang akan dipilih',
                    confirmButtonText: 'OK'
                });
            }

            allSelected = newState;
            $('.item-checkbox').each(function(index) {
                const isStockAvailable = Number(items[$(this).data('index')].stock) > 0;
                if (isStockAvailable && index < 10) {
                    $(this).prop('checked', allSelected);
                } else if (newState) {
                    $(this).prop('checked', false);
                }
            });
            $(this).text(allSelected ? 'Deselect All' : 'Select All');
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
            details.sort((a, b) => Number(b.conversion) - Number(a.conversion));

            details.forEach(element => {
                if(element.department_code==dp_code){
                    units.push(element.unit_conversion);
                }
            });

            // If item wasn't found in existing rows, add new row
            if (!rowUpdated) {
                const currentRow = rowCount;
                const newRow = `
                    <tr>
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
                        <small>${customer.address??''} - ${customer.city??''}</small>`;
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
        document.getElementById('category_customer').value = customer.category_customer || '';
        document.getElementById('search-results').style.display = 'none';

        // Show all rows if no customer is selected
        if (!customerId) {
            $('#invoiceTable tbody tr').show();
        } else {
            // Hide rows that do not match the selected customer
            $('#invoiceTable tbody tr').each(function () {
                const customerIds = $(this).data('customer-id');

                let customerOrigin = customers.find(c => c.customer_code == customerIds);

                if (customerOrigin && customer.group_customer === customerOrigin.group_customer) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('#search')) {
            document.getElementById('search-results').style.display = 'none';
            document.getElementById('search').value=''; }});


        function updateCustomerInfo() {
            const customerSelect = document.getElementById('customer_code');
            const selectedOption = customerSelect.options[customerSelect.selectedIndex];

            // Get customer name and address from the selected option
            const customerName = selectedOption.getAttribute('data-customer-name');
            const address = selectedOption.getAttribute('data-address');

            // Set the values in the readonly fields
            document.getElementById('customer_name').value = customerName;
            document.getElementById('address').value = address;
        }
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
                <div class="input-group">
                    <input type="hidden" id="item_code_${rowCount}" class="form-control item-input" name="details[${rowCount}][item_id]" placeholder="{{__('Search Item')}}">
                    <input type="text" class="form-control item-input" name="details[${rowCount}][item_name]" id="item-search-${rowCount}" placeholder="{{__('Search Item')}}" autocomplete="off">
                    <div id="item-search-results-${rowCount}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                        <!-- Search results will be injected here -->
                    </div>
                </div>
            </td>
            <td>
                <input type="number" id="qty_${rowCount}" name="details[${rowCount}][qty]" class="form-control" value="1" min="1"  required placeholder="Quantity">
                <input type="hidden" id="unit_${rowCount}" name = "details[${rowCount}][unit]" />
                <input type="hidden" id="conversion_value_${rowCount}" name = "details[${rowCount}][base_qty]" value="1" />
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
                <textarea required placeholder="Description" class="form-control" rows="2" cols="50" name="details[${rowCount}][description]" id="description_${rowCount}">
                    </textarea>
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
        document.getElementById(`item-search-${currentRow}`).focus();
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

        let units = [];
        item.item_details.forEach(element => {
            if(element.department_code==item.department_code){
                units.push(element.unit_conversion);
            }
        });

        const unitSelect = document.getElementById(`unit_${rowId}`);

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

    $("#si-form").on("submit", function(e) {
        e.preventDefault(); // Prevent default submission until all checks pass

        const documentDate = document.getElementById('document_date').value;

        // Check document_date with AJAX
        $.ajax({
            url: '{{ route("checkDateToPeriode") }}',
            type: 'POST',
            data: {
                date: documentDate,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response!=true) { // Check response.success (from your controller)
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Tidak bisa input tanggal pada periode non aktif!',
                    });
                    return; // Stop further execution
                }

                // Proceed with other validations only if date is valid
                let isValid = true;

                // Check row count
                var rowCount = $("#parentTbody tr").length;
                if (rowCount === 0) {
                    isValid = false;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Oops...',
                        text: 'Ada barang yang belum dipilih!',
                    });
                    return; // Stop further execution
                }

                // Check stock if checkHPP is not 1
                let formData = new FormData(document.getElementById('si-form'));
                let aa = $("#checkHPP").val() != 1;

                if(isValid){
                    $("#si-form")[0].submit();
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to validate date. Please try again.',
                });
                console.log(xhr);
            }
        });
    });
</script>
@endsection

@endsection
