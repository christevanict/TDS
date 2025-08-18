@extends('layouts.master')

@section('title', 'Reimburse')

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
    <x-page-title title="Transaction" pagetitle="Reimburse" />
    <hr>
    <div class="container content">
        <h2>Reimburse Transaction</h2>
        <form id="po-form" action="{{ route('transaction.reimburse.store') }}" method="POST">
            @csrf
            <div class="card mb-3">
                <div class="card-header">Reimburse {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
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

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sales_order_number">{{__('Sales Order Number')}}</label>
                                <select id="salesOrderNumber" class="form-select" name="sales_order_number"  required>
                                    <option value="" disabled selected></option>
                                    @foreach ($salesOrders as $salesOrder)
                                        <option value="{{ $salesOrder->sales_order_number }}" {{ $selectedSalesOrderNumber === $salesOrder->sales_order_number ? 'selected' : '' }} data-reimburse="{{$salesOrder->status_reimburse}}">
                                            {{ $salesOrder->sales_order_number }}
                                        </option>
                                    @endforeach
                                </select>

                            </div>
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required
                                    value="{{ old('document_date') }}" readonly>
                            </div>
                            <div class="form-group">
                                <label for="document_date">Receivable {{__('Due Date')}}</label>
                                <input type="date" id="due_date" name="due_date" class="form-control date-picker" required
                                    value="" readonly>
                            </div>

                            <div class="form-group">
                                <label for="document_date">Total Reimbursement</label>
                                <input type="number" id="total" name="total" class="form-control" required
                                    value="{{ old('document_date') }}" readonly>
                            </div>

                            <input type="hidden" name="company_code" value="{{ $company->company_code }}"
                                class="form-control" readonly>
                        </div>


                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Reimburse Details</div>
                <div class="card-body">
                    <table class="table" id="po-details-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th>{{__('Price')}}</th>
                                <th>{{__('Account Number')}}</th>
                                <th>{{__('Sales Invoice Number')}}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
            <button type="submit" class="mb-3 btn btn-primary">Submit Reimburse</button>
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
 document.getElementById('document_date').valueAsDate = new Date();
 document.getElementById('due_date').valueAsDate = new Date();
 document.getElementById('salesOrderNumber').value = '';

 // Function to format numbers for Indonesian currency
 function formatNumber(number) {
     return new Intl.NumberFormat('id-ID').format(number);
 }




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
 // When the sales order number is changed
    $('#salesOrderNumber').on('change', function() {
        const salesOrderNumber = $(this).val(); // Get the selected sales order number

        // Clear the item code dropdown
        $('#itemCode').empty().append('<option value="" disabled selected>{{__('Select Item')}}</option>');

        if (salesOrderNumber) {
            $.ajax({
                url: '{{ route('transaction.reimburse.fetch_items') }}',
                method: 'GET',
                data: { sales_order_number: salesOrderNumber },
                success: function(data) {
                    console.log(data);

                    let items  = data.item;
                    let total= 0;
                    items.forEach((element,rowCount) => {
                        total+=parseFloat(element.price);
                        const row = `
                        <tr class= "trh" data-row-id="${rowCount}">
                            <td>
                                <input type="text" id="item_${rowCount}" name="details[${rowCount}][item_description]" class="qtyw form-control" readonly value="${element.description}">
                            </td>
                            <td>
                                <input type="number" id="qty_${rowCount}" name="details[${rowCount}][price]" class="qtyw form-control" readonly value="${element.price}">
                            </td>
                            <td>
                                <select class="form-select" id="account_number_${rowCount}" name="details[${rowCount}][account_number]">
                                    @foreach ($coas as $coa)
                                        <option value={{$coa->account_number}}>{{$coa->account_number.' '.$coa->account_name}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="details[${rowCount}][sales_invoice_number]" id="price_${rowCount}" class="form-control" required>
                            </td>

                        </tr>`;

                        // Append the new row to the table body
                        $('#po-details-table tbody').append(row);
                    });
                    document.getElementById('total').value =total;
                    let so = data.so;
                    document.getElementById('customer_code').value = so.customers.customer_code;
                    document.getElementById('customer_name').value = so.customers.customer_name;
                    document.getElementById('address').value = so.customers.address;
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
