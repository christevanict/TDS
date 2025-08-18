@extends('layouts.master')

@section('title', 'Edit '. __('Purchase Order')

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
    <x-page-title title="Transaction" pagetitle="Edit {{__('Purchase Order')}}" />
    <hr>
    <div class="container content">
        <h2>Edit {{__('Purchase Order')}} Transaction</h2>
        <form id="print-form" action="{{ route('transaction.purchase_order.print', $purchaseOrder->id) }}" target="_blank" method="GET" style="display:inline;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
                Print PO</button>
        </form>
        <form id="print-form" action="{{ route('transaction.purchase_order.print.netto', $purchaseOrder->id) }}" target="_blank" method="GET" style="display:none;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
                Print PO Netto</button>
        </form>
        <form id="po-form" action="{{ route('transaction.purchase_order.update', $purchaseOrder->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card mb-3">
                <div class="card-header">{{__('Purchase Order')}} {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="supplier_code">{{__('Purchase Order Number')}}</label>
                                <input type="text" name="purchase_order_number" id="purchase_order_number" class="form-control" value="{{ $purchaseOrder->purchase_order_number }}" readonly >
                            </div>
                            <div class="form-group">
                                <label for="supplier_code">{{__('Supplier Code')}}</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control" value="{{ $purchaseOrder->supplier_code }}" readonly >
                            </div>
                            <div class="form-group">
                                <label for="supplier_name">{{__('Supplier Name')}}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" value="{{ $purchaseOrder->suppliers->supplier_name }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" value="{{ $purchaseOrder->suppliers->address }}" readonly>
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
                                <textarea name="notes" class="form-control" rows="4" value="{{ $purchaseOrder->notes }}">{{ old('notes', $purchaseOrder->notes) }}</textarea>
                            </div>
                            {{-- <div class="form-group">
                                <label for="discount">{{__('Discount')}} (%)</label>
                                <input type="number" step="0.01" name="discount" id="discount" class="form-control"
                                    placeholder="Enter discount percentage" value="{{ old('discount') }}" required>
                            </div> --}}
                            <div class="form-group">
                                <label for="disc_nominal">{{__('Discount')}} Nominal</label>
                                <input type="text" oninput="formatNumber(this)"  name="disc_nominal" id="disc_nominal"
                                    class="form-control text-end nominal" placeholder="Enter Discount Nominal" value="{{ number_format($purchaseOrder->disc_nominal,0,'.'.'.') }}" required>
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
                                <input type="date" id="delivery_date" name="delivery_date" class="form-control date-picker" required
                                value="{{ $purchaseOrder->delivery_date }}">
                            </div>

                            {{-- <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control date-picker" required
                                value="{{ $purchaseOrder->due_date }}">
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
                                <input type="radio" name="include" value="yes" id="include_yes"  {{ old('include', $purchaseOrder->include) == true ? 'checked' : '' }} required>
                                <label for="include_yes">Yes</label><br>

                                <input type="radio" name="include" value="no" id="include_no" {{ old('include', $purchaseOrder->include) == false ? 'checked' : '' }}>
                                <label for="include_no">No</label><br>
                            </div> --}}
                            <div class="form-group">
                                <label for="tax">{{__('Tax')}}</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" value="PPN / VAT" readonly>
                                    <select hidden class="form-select" id="tax" name="tax">
                                        @foreach ($taxs as $tax)
                                            <option value="{{$purchaseOrder->tax}}" {{ old('tax', $purchaseOrder->tax) == $tax->tax_code ? 'selected' : '' }}>{{$tax->tax_name.' ('.$tax->tax_code.')'}}</option>
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
                                            <option value="{{ $purchaseOrder->tax_revenue_tariff }}" {{ old('tax', $purchaseOrder->tax_revenue_tariff) === $tax->tax_code ? 'selected' : '' }}>
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
                    <h5 class="text-end">Total sebelum pajak: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <td style="min-width: 430px">{{__('Item')}}</td>
                            <td style="min-width: 150px">Unit</td>
                            <td style="min-width: 150px">Qty</td>
                            <td style="min-width: 150px">Qty Unreceived</td>
                            <td>Action</td>
                        </thead>
                        <tbody id="parentTbody">
                            @foreach ($purchaseOrderDetails as $index => $detail)
                                <tr>
                                    <td>
                                        <input type="hidden" name="details[{{ $index }}][item_id]" class="form-control" value="{{ $detail->item_id }}" readonly />
                                        <input type="text" name="details[{{ $index }}][item_name]" class="form-control" value="{{ $detail->items->item_name }}" readonly />
                                    </td>
                                    <td>
                                        <input type="hidden" name="details[{{ $index }}][unit]" class="form-control" value="{{ $detail->unit }}" readonly />
                                        <input type="text" name="details[{{ $index }}][unit_name]" class="form-control" value="{{ $detail->units->unit_name }}" readonly />
                                    </td>
                                    <td>
                                        <input type="number" id="qty_{{$index}}" name="details[{{ $index }}][qty]" class="form-control" value="{{ $detail->qty }}" min="1" readonly/>
                                    </td>
                                    <td>
                                        <input type="number" name="details[{{ $index }}][qty_left]" class="form-control" value="{{ $detail->qty_left }}" min="1" readonly/>
                                    </td>
                                    <td id="pay-row-{{ $index }}">
                                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    <button type="button" class="btn btn-secondary mt-3" id="addRow"
                        style="display: none"
                    >{{__('Select Document')}}</button>
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
                            <table class="table id="invoiceTable">
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
            <button type="submit" class="mb-3 btn btn-primary"  @if(!$editable)
                disabled
            @endif @if(!in_array('print', $privileges)) disabled @endif>Update {{__('Purchase Order')}}</button>
            {{-- <button type="submit" class="btn btn-dark mb-3" onclick="confirm(event,'{{ $purchaseOrder->id }}')"
                @if(Auth::user()->role != 4 && Auth::user()->role != 5&&Auth::user()->role !=7)
                    style="display: none"
                @endif
                @if(!$editable)
                    style="display: none"
                @endif
            >Generate Purchase Invoice</button> --}}

        </form>

        <a href="{{route('transaction.purchase_order')}}" class="btn btn-secondary mb-3">Back</a>

        {{-- <form id="print-form" target="_blank" action="{{ route('transaction.purchase_order.print', $purchaseOrder->id) }}" method="POST" style="display:inline;">
            <button type="submit" class="btn btn-dark mb-3">
                Print</button>
        </form> --}}

        @if($purchaseOrder->status!='Cancelled'&&$purchaseOrder->status!='Closed')
        <form id="cancel-form" action="{{ route('transaction.purchase_order.cancel', $purchaseOrder->id) }}" method="POST" >
            @csrf
            @method('POST')
            <input type="hidden" name="reason" id="cancellation-reason">
            <button type="button" class="btn btn-danger mb-3 " onclick="confirmCancel(event,'{{ $purchaseOrder->id }}')"
                @if(!in_array('delete', $privileges)) disabled @endif
            >Cancel PO</button>
        </form>
        @endif

        <form id="delete-form" action="{{ route('transaction.purchase_order.destroy', $purchaseOrder->id) }}" method="POST" " >
            @csrf
            @method('POST')
            <input type="hidden" name="reason" id="deletion-reason">
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $purchaseOrder->id }}')"
                @if(!in_array('delete', $privileges)) disabled @endif
                @if(!$editable)
                    disabled
                @endif

            ><i class="material-icons-outlined">delete</i></button>
        </form>

    </div>

@section('scripts')

<script>

var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

        function confirmCancel(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to cancel this purchase order?',
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
            text: 'Do you want to delete this purchase order?',
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

    function back(event, id) {
        event.preventDefault(); // Prevent form submission
        document.getElementById('back-form').submit();
    }

    $('#addRow').click(function() {
        $('#selectInvoiceModal').modal('show');
    });

const suppliers = @json($suppliers);
let supplierId=document.getElementById('supplier_code').value;
 let itemIds=[];
 let items = @json($items);
 let prices = @json($prices);
 let purchaseRequisition = @json($purchaseRequisition);
 let purchaseRequisitionD = @json($purchaseRequisitionD);
 let rowCount = {{ isset($purchaseOrder) ? count($purchaseOrder->details) : 1 }};
let SO = [];
let reimbursement = true;

function calculateTotals() {
        let total = 0;
        const disc_nominal = document.getElementById('disc_nominal').value.replace(/,/g, '') || 0;

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
        // const disc_percent = parseFloat(document.getElementById(`disc_percent_${row}`).value.replace(/,/g, '')) || 0;
        // const disc_nominal = parseFloat(document.getElementById(`disc_nominal_${row}`).value.replace(/,/g, '')) || 0;

        const nominalInput = document.getElementById(`nominal_${row}`);
        const nominalValue = ((qty * price)-((qty * price)*disc_percent/100)-disc_nominal)+"";

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
            const newRow = `
                <tr>
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
                        <input type="number" name="details[${rowCount}][qty]" class="form-control" value="${requisit.qty}" min="1" max="${requisit.qty}" readonly />
                    </td>
                    <td>
                        <input type="number" name="details[${rowCount}][qty_left]" class="form-control" value="${requisit.qty_left}" min="1" max="${requisit.qty_left}" readonly/>
                    </td>
                    <td>
                        <input type="number" name="details[${rowCount}][price]" class="form-control" value="${requisit.price}" readonly />
                    </td>
                    <td>
                        <input type="number" name="details[${rowCount}][disc_percent]" step="1" class="form-control" value="0"  />
                    </td>
                    <td>
                        <input type="number" name="details[${rowCount}][disc_nominal]" step="1000" class="form-control" value="0"  />
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][notes]" class="form-control" value="${requisit.notes}" />
                    </td>
                    <td id="pay-row-${rowCount}">
                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                    </td>
                </tr>
            `;
        $('#parentTbody').append(newRow);
        rowCount++;
        });

        $('#selectInvoiceModal').modal('hide');

    });

function confirm(event, id) {
    event.preventDefault(); // Prevent form submission
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0c6efd',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Generate Invoice!'
    }).then((result) => {
        if (result.isConfirmed) {

            document.getElementById('po-form').action = `/TDS/transaction/purchase-order/generate/${id}`;
            document.getElementById('po-form').submit();
        }
    });
    }

function back(event, id) {
    event.preventDefault(); // Prevent form submission
    document.getElementById('back-form').submit();
    }


    // Function to format numbers for Indonesian currency


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



    $('#po-details-table').on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        rowCount--;
    });

</script>
@endsection
@endsection
