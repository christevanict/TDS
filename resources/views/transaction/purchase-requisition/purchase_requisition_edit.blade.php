@extends('layouts.master')

@section('title', 'Edit Purchase Requisition')

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
    <x-page-title title="Transaction" pagetitle="Edit Purchase Requisition" />
    <hr>
    <div class="container content">
        <h2>Edit Purchase Requisition Transaction</h2>
        <form id="po-form" action="{{ route('transaction.purchase_requisition.update', $purchaseRequisition->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card mb-3">
                <div class="card-header">Purchase Requisition {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">{{__('Notes')}}</label>
                                <textarea name="notes" class="form-control" rows="5">{{ old('notes', $purchaseRequisition->notes) }}</textarea>
                            </div>
                            {{-- <div class="form-group">
                                <label for="supplier_code">{{__('Supplier Code')}}</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control" value="{{ $purchaseRequisition->supplier_code }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="supplier_name">{{__('Supplier Name')}}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" value="{{ $purchaseRequisition->suppliers->supplier_name }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" value="{{ $purchaseRequisition->suppliers->address }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="department_code">{{__('Department Code')}}</label>
                                <input type="text" name="department_code" id="department_code" class="form-control" value="{{ $purchaseRequisition->department->department_name }}" readonly>
                            </div> --}}
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase_requisition_number">Purchase Requisition Number</label>
                                <input type="text" id="purchase_requisition_number" name="purchase_requisition_number" class="form-control" value="{{ $purchaseRequisition->purchase_requisition_number }}" required readonly>
                            </div>


                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" value="{{ $purchaseRequisition->document_date }}" required>

                            </div>
                            {{-- <div class="form-group">
                                <label for="disc_nominal">{{__('Discount')}} Nominal</label>
                                <input type="number" step="0.01" name="disc_nominal" id="disc_nominal"
                                    class="form-control" value="{{ $purchaseRequisition->disc_nominal }}" required>
                            </div> --}}
                        </div>

                        {{-- <div class="col-md-6">


                            {{-- <div class="form-group">
                                <label for="delivery_date">{{__('Delivery Date')}}</label>
                                <input type="date" id="delivery_date" name="delivery_date" class="form-control" value="{{ $purchaseRequisition->delivery_date }}" required >
                            </div>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control" required
                                    value="{{ $purchaseRequisition->due_date }}" >
                            </div> --}}
                            {{-- <label for="include" class="form-label">Include</label>
                            <div class="form-group">
                                <input type="radio" name="include" value="yes" id="include_yes" {{ $purchaseRequisition->include == '1' ? 'checked' : '' }} required>
                                <label for="include_yes">Yes</label><br>

                                <input type="radio" name="include" value="no" id="include_no" {{ $purchaseRequisition->include == '0' ? 'checked' : '' }}>
                                <label for="include_no">No</label><br>
                            </div>
                            <div class="form-group">
                                <label for="tax">Tax</label>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="tax" name="tax" required>
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $tax->tax_code }}" {{ $purchaseRequisition->tax == $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" name="company_code" value="{{ $purchaseRequisition->company_code }}" class="form-control" readonly>
                        </div> --}}
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Purchase Requisition Details</div>
                <div class="card-body">
                    <table class="table" id="po-details-table">
                        <thead>
                            <tr>
                                <th>{{__('Item')}}</th>
                                <th>Unit</th>
                                <th>Quantity</th>
                                <th>{{__('Notes')}}</th>
                                {{-- <th>Price</th>
                                <th>{{__('Discount')}} (%)</th>
                                <th>{{__('Discount')}}</th> --}}
                                {{-- <th>Description</th> --}}
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseRequisitionDetails as $index => $detail)
                            <tr class= "trh" data-row-id="{{$index}}">
                                <td>
                                    <div class="form-group">
                                        <input type="hidden" id="item_code_{{$index}}" class="form-control item-input" name="details[{{$index}}][item_id]" placeholder="{{__('Search Item')}}" value="{{$detail->item_id}}">
                                        <input type="text" class="form-control item-input" name="details[0][item_name]" id="item-search-{{$index}}" placeholder="{{__('Search Item')}}" value="{{ $detail->items->item_name }}" readonly>
                                        <div id="item-search-results-{{$index}}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                            <!-- Search results will be injected here -->
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control item-input"  id="unit_0" placeholder="Unit" readonly value="{{$detail->units->unit_name}}">
                                    <input type="hidden" class="form-control item-input" name="details[{{$index}}][unit]" placeholder="{{__('Search Item')}}" value="{{$detail->unit}}">
                                </td>
                                <td>
                                    <input type="number" id="qty_{{$index}}" name="details[{{$index}}][qty]" class="qtyw form-control" value="{{$detail->qty}}"
                                    min="1" max="100000" required placeholder="Quantity">
                                </td>

                                <td>
                                    <input type="text" class="form-control item-input"  id="notes_0" name="details[{{$index}}][notes]" placeholder="Notes" value="{{$detail->notes}}">
                                </td>
                                {{-- <td>
                                    <input type="number" name="details[0][price]" id="price_0" class="form-control"
                                        value="0" required placeholder="Price" readonly>
                                </td>
                                <td>
                                    <input type="number" name="details[0][disc_percent]" id="disc_percent_0"
                                        class="form-control" value="0" max="100" required placeholder="% Discount">
                                </td>
                                <td>
                                    <input type="number" name="details[0][disc_nominal]" id="disc_nominal_0"
                                        class="form-control" value="0" required placeholder="Nominal Discount">
                                </td> --}}
                                {{-- <td>
                                    <input type="text" name="details[0][description]" id="description_0"
                                        class="form-control" value="" required placeholder="Description">
                                </td> --}}
                                <td>
                                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined">delete</i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" id="add-row" class="btn btn-success">Add Row</button>
                </div>
            </div>
            @if($editable)
            <button type="submit" class="mb-3 btn btn-primary">Update Purchase Requisition</button>
            @else
            @endif

            {{-- <button type="submit" class="btn btn-secondary mb-3" onclick="confirm(event,'{{ $purchaseRequisition->id }}')"
                @if(Auth::user()->role != 4 && Auth::user()->role != 5&&Auth::user()->role !=7)
                    style="display: none"
                @endif
                @if(!$editable)
                    style="display: none"
                @endif
            >Generate Purchase Invoice</button> --}}
        </form>
        <form id="back-form" action="{{ route('transaction.purchase_requisition', $purchaseRequisition->id) }}" method="GET" style="display:inline;" >
            @csrf
            @method('GET')
            <button type="submit" class="btn btn-secondary mb-3 mt-2" onclick="back(event,'{{ $purchaseRequisition->id }}')"
                @if($editable)
                    style="display: none"
                @endif>Back</button>
        </form>
        <form id="delete-form" action="{{ route('transaction.purchase_requisition.destroy', $purchaseRequisition->id) }}" method="POST" style="display:inline;" >
            @csrf
            @method('POST')
            <input type="hidden" name="reason" id="deletion-reason">
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $purchaseRequisition->id }}')"
                @if(Auth::user()->role != 5 && Auth::user()->role != 7)
                    style="display: none"
                @endif
                @if(!$editable)
                    style="display: none"
                @endif
            ><i class="material-icons-outlined">delete</i></button>
        </form>

    </div>

@section('scripts')

<script>
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

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

function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this purchase requisition?',
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
    const suppliers = @json($suppliers);
    let items = @json($items);

    let salesOrders = @json($salesOrders);

    // Function to format numbers for Indonesian currency


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
                            e.preventDefault();
                            document.querySelector(`input[name="details[${rowId}][item_id]"]`).value = item.item_code;
                            document.querySelector(`input[name="details[${rowId}][item_name]"]`).value = item.items.item_name;
                            document.querySelector(`input[name="details[${rowId}][unit]"]`).value = item.unitn.unit;
                            document.getElementById(`unit_${rowId}`).value = item.unitn.unit_name;
                            resultsContainer.style.display = 'none'; // Hide dropdown after selection


                        });

                        resultsContainer.appendChild(listItem); // Add item to dropdown
                    });
                }
            }
        });
    }

    // SECTION SUPPLIER SEARCH


    let rowCount = {{ isset($purchaseRequisition) ? count($purchaseRequisitionDetails) : 1 }}; // Initialize row count


    // Supplier search functionality




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
        $('#add-row').on('click', function() {
        const row = `
            <tr class= "trh" data-row-id="${rowCount}">
             <td>
                <div class="form-group">
                    <input type="hidden" class="form-control item-input" name="details[${rowCount}][item_id]" placeholder="{{__('Search Item')}}">
                    <input type="text" class="form-control item-input" name="details[${rowCount}][item_name]" id="item-search-${rowCount}" placeholder="{{__('Search Item')}}">
                    <div id="item-search-results-${rowCount}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                        <!-- Search results will be injected here -->
                    </div>
                </div>
             </td>
            <td>
                <input type="text" class="form-control item-input"  id="unit_${rowCount}" placeholder="Unit" readonly>
                <input type="hidden" class="form-control item-input" name="details[${rowCount}][unit]" placeholder="{{__('Search Item')}}">
            </td>
            <td>
                <input type="number" id="qty_${rowCount}" name="details[${rowCount}][qty]" class="qtyw form-control" value="1" min="1" required placeholder="Quantity">
            </td>
            <td>
                <input type="text" class="form-control item-input"  id="notes_${rowCount}" name="details[${rowCount}][notes]" placeholder="Notes">
            </td>
             <td>
                 <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined">delete</i></button>
             </td>
         </tr>`;
            $('#po-details-table tbody').append(row);

            setupItemSearch(rowCount);
            // document.getElementById(`item_code_${rowCount}`).addEventListener('change', updatePrice);

            rowCount++;



    $('#po-details-table').on('click', '.remove-row', function() {
        $(this).closest('tr').remove();
        rowCount--;
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
    let SO = @json($salesOrderDetail);
    // console.log(SO);

    // console.log(SO);
    // console.log('B',rowCount);
    // for (let i = 0; i < rowCount; i++) {
    //     console.log(i);

    //     setupItemSearch(i);
    //     const itemCode = document.getElementById(`item_code_${i}`).value;
    //     const unit = 'U1';
    //     const supplier_code = document.getElementById('supplier_code').value;
    //     // console.log(SO.find(so=> so.item_id == itemCode && so.unit == unit).qty_left);
    //     // document.getElementById(`item_code_${i}`).addEventListener('change', updatePrice);
    // }



//     function updatePrice() {
//         const rowId = this.closest('tr').getAttribute('data-row-id');
//         // console.log(rowId);

//         const itemCode = document.getElementById(`item_code_${rowId}`).value;
//         const unit = 'U1';
//         const supplier_code = document.getElementById('supplier_code').value;
//         // console.log(itemCode,unit,supplier_code);

//         var itemDetails = @json($itemDetails);
//         var prices = @json($prices);


//         if (itemCode && unit&&itemCode!='REIMBURSE') {
//             // Fetch price for the selected item and unit
//             const itemDetail = itemDetails.find(detail => detail.item_code === itemCode && detail.unit_conversion === unit);
//             const itemSale = itemDetail ? prices.find(sale => sale.barcode === itemDetail.barcode&& sale.supplier==supplier_code) : null;
//             const price = itemSale ? itemSale.purchase_price : 0;

//             // Update the price input field for the correct row
//             document.getElementById(`price_${rowId}`).value = price;

//             const soItem = SO.find(so => so.item_id == itemCode && so.unit == unit);
//             if (soItem) {
//                 document.getElementById(`qty_${rowId}`).max = soItem.qty_left;
//             } else {
//                 // Handle the case where the item is not found, e.g., set max to 0 or handle it accordingly
//                 document.getElementById(`qty_${rowId}`).max = 0; // or some other default value
//             }
//         }
//         console.log(itemCode);

//         if(itemCode && unit&&itemCode=='REIMBURSE'){
//             console.log('masuk');

//                 document.getElementById(`qty_${rowId}`).max = 1;
//             }
//     }

//         document.getElementById(`item_code_0`).addEventListener('change', updatePrice);
//         // document.getElementById(`unit_0`).addEventListener('change', updatePrice);
//         $(document).ready(function() {
//  // When the sales order number is changed

// });

$(document).ready(function() {

let initial = {{ isset($purchaseRequisition) ? count($purchaseRequisition->details) : 1 }};
// console.log(initial);

// const salesOrderNumber = document.getElementById('sales_order_number').value;; // Get the selected sales order number

// Clear the item code dropdown
$('#itemCode').empty().append('<option value="" disabled selected>{{__('Select Item')}}</option>');

});

</script>
@endsection
@endsection
