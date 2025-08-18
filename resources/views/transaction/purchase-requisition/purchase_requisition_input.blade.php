@extends('layouts.master')

@section('title', 'Purchase Requisition')

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
    <x-page-title title="Transaction" pagetitle="Purchase Requisition" />
    <hr>
    <div class="container content">
        <h2>Purchase Requisition Transaction</h2>
        <form id="po-form" action="{{ route('transaction.purchase_requisition.store') }}" method="POST">
            @csrf
            <div class="card mb-3">
                <div class="card-header">Purchase Requisition {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="notes">Description</label>
                                <textarea name="notes" class="form-control" rows="5">{{ old('notes') }}</textarea>
                            </div>
                            {{-- <div class="form-group">
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
                            </div> --}}
                            {{-- <div class="form-group">
                                <label for="purchase_requisition_number">Purchase Requisition Number</label>
                                <input type="text" name="purchase_requisition_number" id="purchase_requisition_number"
                                    class="form-control" value="{{ old('purchase_requisition_number', $purchaseOrderNumber) }}"
                                    readonly>
                            </div> --}}

                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required
                                    value="{{ old('document_date') }}" >
                            </div>
                            <div class="form-group">
                                <label for="department_code">Department</label>
                                <input type="hidden" name="department_code" id="department_code" class="form-control date-picker" readonly value="{{ $departments->department_code }}" required>
                                <input type="text" name="department_name" id="department_name" class="form-control" readonly value="{{ $departments->department_name }}" required>
                            </div>


                            {{-- <div class="form-group">
                                <label for="discount">{{__('Discount')}} (%)</label>
                                <input type="number" step="0.01" name="discount" id="discount" class="form-control"
                                    placeholder="Enter discount percentage" value="{{ old('discount') }}" required>
                            </div> --}}
                            {{-- <div class="form-group">
                                <label for="disc_nominal">{{__('Discount')}} Nominal</label>
                                <input type="number" step="0.01" name="disc_nominal" id="disc_nominal"
                                    class="form-control" placeholder="Enter Discount Nominal" value="0" required>
                            </div> --}}
                        </div>
{{--
                        <div class="col-md-4">

                            {{-- <div class="form-group">
                                <label for="delivery_date">{{__('Delivery Date')}}</label>
                                <input type="date" id="delivery_date" name="delivery_date" class="form-control" required
                                    value="{{ old('delivery_date') }}">
                            </div>
                            <div class="form-group">
                                <label for="due_date">{{__('Due Date')}}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control" required
                                    value="{{ old('due_date') }}"> --}}
                            {{-- </div> --}}
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
                            </div>
                            <div class="form-group">
                                <label for="tax">Tax</label>
                                <div class="input-group mb-3">
                                    <select class="form-select" id="tax" name="tax" required>
                                        @foreach ($taxs as $tax)
                                            <option value="{{ $tax->tax_code }}" {{ old('tax') === $tax->tax_code ? 'selected' : '' }}>
                                                {{ $tax->tax_name . ' (' . $tax->tax_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                           {{-- <input type="hidden" name="company_code" value="{{ $company->company_code }}"
                                class="form-control" readonly>
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
                            <tr class= "trh" data-row-id="0">
                                <td>
                                    <div class="form-group">
                                        <input type="hidden" class="form-control item-input" name="details[0][item_id]" placeholder="{{__('Search Item')}}">
                                        <input type="text" class="form-control item-input" name="details[0][item_name]" id="item-search-0" placeholder="{{__('Search Item')}}">
                                        <div id="item-search-results-0" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                            <!-- Search results will be injected here -->
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control item-input"  id="unit_0" placeholder="Unit" readonly>
                                    <input type="hidden" class="form-control item-input" name="details[0][unit]" placeholder="{{__('Search Item')}}">
                                </td>
                                <td>
                                    <input type="number" id="qty_0" name="details[0][qty]" class="qtyw form-control" value="1"
                                    min="1" max="100000" required placeholder="Quantity">
                                </td>

                                <td>
                                    <input type="text" class="form-control item-input"  id="notes_0" name="details[0][notes]" placeholder="Notes">
                                </td>
                                {{-- <td>
                                    <input type="number" name="details[0][price]" id="price_0" class="form-control"
                                        value="0" required placeholder="{{__('Price')}}" readonly>
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
                        </tbody>
                    </table>
                    <button type="button" id="add-row" class="btn btn-success">Add Row</button>
                </div>
            </div>
            <button type="submit" class="mb-3 btn btn-primary">Submit Purchase Requisition</button>
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
function setupItemSearch(rowId) {
        // Add event listener for the new row's search input
        document.getElementById(`item-search-${rowId}`).addEventListener('input', function() {
            let query = this.value.toLowerCase();
            let resultsContainer = document.getElementById(`item-search-results-${rowId}`);
            resultsContainer.innerHTML = ''; // Clear previous results
            resultsContainer.style.display = 'none'; // Hide dropdown by default
            // console.log(query);

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
    setupItemSearch(0);
    // Initialize default values for document date and delivery date
 document.getElementById('document_date').valueAsDate = new Date();

//  document.getElementById('delivery_date').valueAsDate = new Date();
//  document.getElementById('department_code').value = '';
//  document.getElementById('salesOrderNumber').value = '';
//  document.getElementById('tax').value = '';

 // Function to format numbers for Indonesian currency
 function formatNumber(number) {
     return new Intl.NumberFormat('id-ID').format(number);
 }

 // SECTION SUPPLIER SEARCH
 const suppliers = @json($suppliers);
 let items = @json($items);
 let rowCount = 1; // Initialize row count
let SO = [];
let reimbursement = true;
 // Supplier search functionality
//  document.getElementById('search').addEventListener('input', function() {
//     let rowCount = 0;
//      let query = this.value.toLowerCase();
//      let resultsContainer = document.getElementById('search-results');
//      resultsContainer.innerHTML = ''; // Clear previous results
//      resultsContainer.style.display = 'none'; // Hide dropdown by default

//      if (query.length > 0) {
//          let filteredSuppliers = suppliers.filter(s =>
//              s.supplier_code.toLowerCase().includes(query) || // Match supplier_code
//              s.supplier_name.toLowerCase().includes(query) || // Match supplier_name
//              s.address.toLowerCase().includes(query) // Match address
//          );

//          if (filteredSuppliers.length > 0) {
//              resultsContainer.style.display = 'block'; // Show dropdown if matches found
//              // Populate dropdown with filtered results
//              filteredSuppliers.forEach(supplier => {
//                  let listItem = document.createElement('a');
//                  listItem.className = 'list-group-item list-group-item-action';
//                  listItem.href = '#';
//                  listItem.innerHTML = `
//                  <strong>${supplier.supplier_code}</strong> - ${supplier.supplier_name} <br>
//                  <small>${supplier.address}</small>
//              `;
//                  // Handle selection of a supplier
//                  listItem.addEventListener('click', function(e) {
//                      e.preventDefault();
//                      document.getElementById('search').value = '';
//                      document.getElementById('supplier_code').value = supplier.supplier_code;
//                      document.getElementById('supplier_name').value = supplier.supplier_name;
//                      document.getElementById('address').value = supplier.address;
//                      resultsContainer.style.display = 'none'; // Hide dropdown after selection
//                  });


//                  resultsContainer.appendChild(listItem); // Add item to dropdown
//              });
//          }
//      }
//  });

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

//

        // document.getElementById(`item_code_0`).addEventListener('change', updatePrice);
        // document.getElementById(`unit_0`).addEventListener('change', updatePrice);

        // console.log(SO);

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

         // Append the new row to the table body
         $('#po-details-table tbody').append(row);

        //  document.getElementById(`item_code_${rowCount}`).addEventListener('change', updatePrice);
        //  console.log('a');
        setupItemSearch(rowCount);
        // document.getElementById(`unit_${rowCount}`).addEventListener('change', updatePrice);
         // Increment row count for the next row
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
