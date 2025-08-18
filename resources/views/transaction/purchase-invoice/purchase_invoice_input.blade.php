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
        <h2>{{__('Purchase Invoice')}} Transaction</h2>
        <form id="po-form" action="{{ route('transaction.purchase_invoice.store') }}" method="POST">
            @csrf
            <div class="card mb-3">
                <div class="card-header">{{__('Purchase Invoice')}} {{__('Information')}}</div>
                <div class="card-body">
                    <input type="hidden" id="checkHPP" value="0">
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
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {{-- <label for="department_code">Department</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <label for="notes">{{__('Notes')}}</label>
                                <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="disc_nominal">{{__('Discount')}} Nominal</label>
                                <input type="text" oninput="formatNumber(this)" name="disc_nominal" id="disc_nominal"
                                    class="form-control text-end nominal" placeholder="Enter Discount Nominal" value="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="hidden" id="document_date" name="document_date" class="form-control " required
                                    value="{{ old('document_date') }}" readonly>
                                    <input type="text" class="form-control  " name="" id="doc_date_show" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="delivery_date">{{__('Delivery Date')}}</label>
                                    <input type="hidden" id="delivery_date" name="delivery_date" class="form-control " required
                                    value="{{ old('delivery_date') }}" readonly>
                                    <input type="text" class="form-control  " name="" id="del_date_show" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="due_date">{{__('Due Date')}} Hutang</label>
                                    <input type="hidden" id="due_date" name="due_date" class="form-control  " required
                                    value="{{ old('due_date') }}" readonly>
                                    <input type="text" class="form-control  " name="" id="due_date_show" readonly>
                            </div>

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
                            <!-- Parent rows will be added dynamically here -->
                        </tbody>
                    </table>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="addRow">{{__('Select Document')}}</button>
                    <button type="button" onclick="addNewRow()" class="btn btn-primary mt-3 d-none">Tambah Barang</button>
                </div>
            </div>

            <div class="modal fade" id="selectInvoiceModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectInvoiceModalLabel">Select {{__('Good Receipt')}}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered" id="invoiceTable">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>{{__('Good Receipt Number')}}</th>
                                        <th>Vendor</th>
                                        <th>No. Dokumen Vendor</th>
                                        <th>Departemen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($goodReceipt as $pi)
                                    <tr data-supplier-id="{{ $pi->supplier_code }}">
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="radio" name="selectedInvoice" class="invoice-checkbox" value="{{ $pi->good_receipt_number }}">
                                        </td>
                                        <td>{{ $pi->good_receipt_number }}</td>
                                        <td>{{ $pi->supplier->supplier_name }}</td>
                                        <td>{{ $pi->vendor_number }}</td>
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
            <button type="submit" class="mb-3 btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Purchase Invoice')}}</button>
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
                    title: '{{__('Purchase Invoice')}} Created',
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
                            window.open("{{ route('transaction.purchase_invoice.print', ['id' => ':id']) }}".replace(':id', id), '_blank');
                        }
                    }
                });
            @endif
        });

         // SECTION SUPPLIER SEARCH
    const suppliers = @json($suppliers);
    let supplierId='';
    let itemIds=[];
    let items = @json($items);
    let prices = @json($prices);
    const goodReceipt = @json($goodReceipt);
    let goodReceiptD = @json($goodReceiptD);
    let rowCount = 1; // Initialize row count
    let SO = [];
    let reimbursement = true;

    $('#addRow').click(function() {
        $('#selectInvoiceModal').modal('show');
    });

    // Initialize default values for document date and delivery date
    // document.getElementById('document_date').valueAsDate = new Date();
    // document.getElementById('delivery_date').valueAsDate = new Date();
    // document.getElementById('due_date').valueAsDate = new Date();
    //  document.getElementById('delivery_date').valueAsDate = new Date();
    document.getElementById('department_code').value = '';
    //  document.getElementById('salesOrderNumber').value = '';
    document.getElementById('tax').value = '';


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
    let itemDetails = @json($itemDetails);
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
        const nominalValue = ((qty * price*parseFloat(conversion))-((qty * price*parseFloat(conversion))*disc_percent/100)-disc_nominal)+"";

        let formattedValue = nominalValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        nominalInput.value = formattedValue; // Update nominal value
        calculateTotals();
    }


    $('#selectInvoicesButton').click(function() {
    const selectedGood = [];

        $('#invoiceTable .invoice-checkbox:checked').each(function() {
            const requNumber = $(this).val();

            selectedGood.push({
                requNumber: requNumber
            });


            let selectedGoodReceipt = goodReceipt.find((e)=>e.good_receipt_number == requNumber);

            document.getElementById('document_date').value = selectedGoodReceipt.document_date;
            document.getElementById('due_date').value = selectedGoodReceipt.document_date;
            document.getElementById('delivery_date').value = selectedGoodReceipt.document_date;

            const dates = new Date(selectedGoodReceipt.document_date);
            const formattedDate = dates.toLocaleDateString('en-GB', {
            day: '2-digit', month: 'short', year: 'numeric'
            }).replace(/ /g, ' ');
            document.getElementById('doc_date_show').value = formattedDate;
            document.getElementById('del_date_show').value = formattedDate;
            document.getElementById('due_date_show').value = formattedDate;
        });

        // console.log(goodReceiptD);


        filteredPRdetails = goodReceiptD.filter(detail =>
            selectedGood.some(selectedDetail =>
                detail.good_receipt_number === selectedDetail.requNumber
            )
        );

        filteredPrices = prices.filter(detail =>
        filteredPRdetails.some(selectedDetail =>
            detail.item_code === selectedDetail.item_id
        ));
        const datas = [];
        filteredPrices.forEach(prices => {
            filteredPRdetails.forEach(detail => {

                if(prices.item_code === detail.item_id){

                    const a={
                        'id': detail.id,
                        'item_id':detail.item_id,
                        'item_name':detail.items.item_name,
                        'good_receipt_number':detail.good_receipt_number,
                        'purchase_order_number':detail.purchase_order_number,
                        'price':prices.purchase_price,
                        'qty':detail.qty,
                        'base_qty':detail.base_qty,
                        'unit':detail.unit,
                        'unit_name':detail.units.unit_name,
                        'notes':detail.description,
                    }

                    datas.push(a);
                }

            });
        });
        datas.sort((a, b) => a.id - b.id);

        $('#parentTbody').empty();

        datas.forEach(requisit => {
            const currentRow = rowCount;
            const newRow = `
                <tr>
                    <td>
                        <input type="text" name="details[${rowCount}][good_receipt_number]" class="form-control" value="${requisit.good_receipt_number}" readonly />
                        <input type="hidden" name="details[${rowCount}][purchase_order_number]" class="form-control" value="${requisit.purchase_order_number}" readonly />
                    </td>
                    <td>
                        <input type="hidden" id="item_code_${rowCount}" name="details[${rowCount}][item_id]" class="form-control" value="${requisit.item_id}" readonly />
                        <input type="text" name="details[${rowCount}][item_name]" class="form-control" value="${requisit.item_name}" readonly />
                    </td>
                    <td>
                        <input type="hidden" name="details[${rowCount}][unit]" class="form-control" value="${requisit.unit}" readonly />
                        <input type="text" name="details[${rowCount}][unit_name]" class="form-control" value="${requisit.unit_name}" readonly />
                    </td>
                    <td>
                        <input type="hidden" id="conversion_value_${rowCount}" name="details[${rowCount}][base_qty]" value="${requisit.base_qty}" readonly />
                        <input type="number" id="qty_${rowCount}" name="details[${rowCount}][qty]" class="form-control" value="${requisit.qty}" min="1" max="${requisit.qty}" readonly/>
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)" id="price_${rowCount}" name="details[${rowCount}][price]" class="form-control text-end" value="${requisit.price.split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
                    </td>
                    <td >
                        <input type="text" oninput="formatNumber(this)" id="disc_percent_${rowCount}" name="details[${rowCount}][disc_percent]" class="form-control text-end" value="0"   />
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)" id="disc_nominal_${rowCount}" name="details[${rowCount}][disc_nominal]" class="form-control text-end" value="0"  />
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)" id="nominal_${rowCount}" name="details[${rowCount}][nominal]" class="form-control text-end nominal" value="${(requisit.qty*requisit.price*requisit.base_qty).toString().split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')}"  readonly/>
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

            let formData = new FormData(this);
            let aa = true;
            if($("#checkHPP").val() == 1 ||$("#checkHPP").val() == "1"){
                aa = false;
            }
            if(aa){
                e.preventDefault();
                $.ajax({
                    url: "{{ route('checkUnit') }}",
                    type: "POST",
                    data: formData,
                    success: function (rs) {
                        if (rs.length > 0) {
                            let errorHtml = "";
                            rs.forEach((a) => {
                                errorHtml += "<li>" + a.item_name + "</li>";
                            });
                            errorHtml = "<ul>" + errorHtml + "</ul>";
                            Swal.fire({
                                title: 'Tidak bisa disimpan!',
                                html: "Harap ganti unit item berikut, karena sudah tidak aktif:<br/>" + errorHtml,
                                icon: 'warning',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            $("#checkHPP").val(1);
                            $("#si-form").submit();
                        }
                    },
                    error: function (xhr, ajaxOptions, thrownError) {
                        let errorMessage = "Terjadi kesalahan saat memeriksa stok dan unit.";
                        try {
                            let data = JSON.parse(xhr.responseText);
                            if (data.message) {
                                errorMessage = data.message;
                            }
                        } catch (e) {
                            // Use default message if parsing fails
                        }
                        Swal.fire({
                            title: 'Error!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    },
                    cache: false,
                    contentType: false,
                    processData: false,
                });
            }
        });
    });
</script>
@endsection
