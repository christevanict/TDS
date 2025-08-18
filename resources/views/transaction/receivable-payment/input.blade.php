@extends('layouts.master')

@section('title', 'Input Receivable Payment')

@section('css')
<style>
.nested-table {
    width: calc(100% - 40px); /* Adjust width to fit nicely under the parent row */
    margin: 0; /* Remove margin */
    border: 1px solid #ddd; /* Optional: add border for clarity */
}

.child-row {
    background-color: #f9f9f9; /* Optional: slight background for child rows */
}

.table th, .table td {
    padding: 8px; /* Adjust padding to your needs */
}

table {
    border-collapse: collapse; /* Ensures tables are neatly aligned */
}

.tooltip {
    position: relative;
    display: inline-block;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 120px;
    background-color: black;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 5px 0;

    /* Position the tooltip */
    position: absolute;
    z-index: 1;
    bottom: 125%; /* Position above the button */
    left: 50%;
    margin-left: -60px; /* Center the tooltip */
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}

.invoice-checkbox {
    width: 20px; /* Set a larger width */
    height: 20px; /* Set a larger height */
    transform: scale(1.5); /* Scale the checkbox */
    margin: 0; /* Remove default margin */
    cursor: pointer; /* Change cursor to pointer on hover */
}
</style>
@endsection

@section('content')
<div class="row">
    <x-page-title title="{{__('Receivable Payment')}}" pagetitle="Input {{__('Receivable Payment')}}" />
    <hr>
    <div class="container content">
        <h2>{{__('Receivable Payment')}}</h2>

        <form id="receivable-payment-form" action="{{ route('transaction.receivable_payment.store') }}" method="POST">
            @csrf

            <div class="card mb-3">
                <div class="card-header">{{__('Receivable Payment')}}</div>
                <div class="card-body">
                    <input type="hidden" name="token" id="token" value="{{$token??''}}">
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
                                <input type="text" name="customer_code" id="customer_code" class="form-control" readonly>
                                <input type="hidden" name="category_customer" id="category_customer" class="form-control" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                {{-- <label for="department_code">{{__('Department Code')}}</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <label for="document_date">Tanggal{{__('Receivable Payment')}}</label>
                                <input type="date" name="document_date" id="document_date" class="form-control date-picker" required value="{{ date('Y-m-d') }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="acc_disc">Akun Diskon</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-acc-disc" class="form-control" autocomplete="off" placeholder="Search by Account Number or Account Name" >
                                        <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-disc')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-acc-disc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="acc_disc" id="acc_disc" >
                                </div>
                                <div class="form-group mb-3" id="pay-row">
                                    <button type="button" class="btn btn-info" id="btnPayment">
                                        Detail Metode Pembayaran
                                    </button>
                                    <br>
                                    <h5 >Total Pembayaran: <span id="total-payment-value">0</span></h5>
                                    <input type="hidden" id="payment_details"  />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Table for Receivable Payment Details -->
            <div class="card mb-3">
                <div class="card-header">Detail {{__('Receivable Payment')}}</div>
                <div class="card-body">
                    <h5 class="text-end">Total Pembayaran: <span id="total-value">0</span></h5>
                    <table class="table" id="dynamicTable">
                        <thead>
                            <td>Nomor Dokumen</td>
                            <td>Tanggal Dokumen</td>
                            <td>Jumlah Piutang</td>
                            <td>Nominal Pembayaran</td>
                            <td>Diskon</td>
                            <td>Total Pembayaran</td>
                            <td>Sisa Piutang</td>
                            <td>Action</td>
                        </thead>
                        <tbody id="parentTbody">
                            <!-- Parent rows will be added dynamically here -->
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary" id="addRow">Pilih Dokumen</button>

                </div>
            </div>

            <!-- Template for Detail Row -->
            <div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="detailsModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="detailsModalLabel">Detail Pembayaran</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table details-table">
                                <thead>
                                    <tr>
                                        <th>Metode Pembayaran</th>
                                        <th>Nominal</th>
                                        <th>Nomor BG Check</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="details-body">
                                    <!-- Dynamic details rows will be added here -->
                                </tbody>

                            </table>
                            <button type="button" class="btn btn-primary" id="addDetailRow">Tambah Detail Pembayaran</button>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="saveDetails">Simpan Detail Pembayaran</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="selectInvoiceModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectInvoiceModalLabel">Pilih Faktur</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered" id="invoiceTable">
                                <thead>
                                    <tr>
                                        <th>Pilih</th>
                                        <th>Nomor Faktur</th>
                                        <th>Tanggal Faktur</th>
                                        <th>Sisa Piutang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salesInvoices as $si)
                                    <tr data-customer={{$si->customers->group_customer}}>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" class="invoice-checkbox" data-debt-balance="{{ $si->receivables }}" data-document-date="{{ $si->document_date }}" value="{{ $si->sales_invoice_number }}">
                                        </td>
                                        <td>{{ $si->sales_invoice_number }}</td>
                                        <td>{{ $si->document_date }}</td>
                                        <td>Rp {{ number_format($si->receivables->debt_balance,0,'.',',')}}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="selectInvoicesButton">Pilih</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Receivable Payment')}}</button>
            </div>
        </form>
    </div>
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
                    title: '{{__('Receivable Payment')}} Created',
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
                            window.location.href = "{{ route('transaction.receivable_payment.print', ['id' => ':id']) }}".replace(':id', id);
                        }
                    }
                });
            @endif
        });
        var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);
let customerId='';

    const coas = @json($coas);
    function setupSearch(inputId, resultsContainerId,inputHid) {
        const inputElement = document.getElementById(inputId);
        const resultsContainer = document.getElementById(resultsContainerId);
        inputElement.addEventListener('input', function () {
            activeIndex = -1;
            let query = this.value.toLowerCase();
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            if (query.length > 0) {
                let filteredResults = coas.filter(item =>
                    item.account_number.toLowerCase().includes(query) ||
                    item.account_name.toLowerCase().includes(query)
                );
                if (filteredResults.length > 0) {
                    resultsContainer.style.display = 'block';
                    filteredResults.forEach(item => {
                        let listItem = document.createElement('a');
                        listItem.className = 'list-group-item list-group-item-action';
                        listItem.href = '#';
                        listItem.innerHTML = `
                            <strong>${item.account_number}</strong> -
                            ${item.account_name} <br>`;
                        listItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            inputElement.value = item.account_number + ' - ' + item.account_name;
                            inputElement.readOnly = true;
                            document.getElementById(inputHid).value = item.account_number;
                            resultsContainer.style.display = 'none';
                        });
                        resultsContainer.appendChild(listItem);
                    });
                }
            }
        });
        // Keydown event listener for navigation
        inputElement.addEventListener('keydown', function(e) {
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
    function clearInput(inputId) {
        document.getElementById(inputId).value = '';
        document.getElementById(inputId).readOnly = false;
    }
    function updateActiveItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndex);
        });
        if (activeIndex >= 0) {
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    }
    setupSearch('search-acc-disc', 'search-result-acc-disc','acc_disc');

    const customers = @json($customers);

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
                        <small>${customer.address}</small>`;
                    listItem.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('customer_code').value = customer.customer_code;
                        document.getElementById('customer_name').value = customer.customer_name;
                        document.getElementById('address').value = customer.address;
                        resultsContainer.style.display = 'none';
                        customerId = customer.customer_code;
                        if (customerId === "") {
                            $('#invoiceTable tbody tr').show();
                        } else {
                            // Hide rows that do not match the selected customer
                            $('#invoiceTable tbody tr').each(function () {
                                const selectedSuppliers = $(this).data('customer');
                                if (selectedSuppliers == customerId) {
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

    function updateNominalValue(row) {
        const balance = parseFloat(document.getElementById(`balance_${row}`).value.replace(/,/g, '')) || 0;
        const nominal = parseFloat(document.getElementById(`nominal_${row}`).value.replace(/,/g, '')) || 0;
        const disc_nominal = parseFloat(document.getElementById(`discount_${row}`).value.replace(/,/g, '')) || 0;


        const nominalValue = (nominal+disc_nominal)+"";
        const nominalInput = document.getElementById(`nominal_payment_${row}`);
        const remainBalanceInput = document.getElementById(`remaining_balance_${row}`);

        let formattedValue = nominalValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        nominalInput.value = formattedValue; // Update nominal value
        remainBalanceInput.value = (balance - nominalValue).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        calculateTotals();
    }


    function addInputListeners() {
        document.querySelectorAll('.nominal').forEach(function (input) {
            input.addEventListener('change', function () {
                calculateTotals(); // Calculate totals when any input changes
            });
        });
    }
    addInputListeners();

    document.addEventListener('click', function(event) {
        if (!event.target.closest('#search')) {
            document.getElementById('search-results').style.display = 'none';
            document.getElementById('search').value='';
        }
    });

    // document.getElementById('department_code').value = '';

    function formatNumber(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }

    function updateCustomerInfo() {
        const customerSelect = document.getElementById('customer_code');
        const selectedOption = customerSelect.options[customerSelect.selectedIndex];

        // Get Customer Name and address from the selected option
        const customerName = selectedOption.getAttribute('data-customer-name');
        const address = selectedOption.getAttribute('data-address');

        // Set the values in the readonly fields
        document.getElementById('customer_name').value = customerName;
        document.getElementById('address').value = address;
    }

    let rowCount = 0;
    // Initialize row count // Tracks main pay details rows
    $('#addRow').click(function() {
        $('#selectInvoiceModal').modal('show');
    });
    $('#btnPayment').click(function() {
        $('#detailsModal').modal('show');
    });

    // Delete Row
    $(document).on('click', '.deleteRow', function() {
        const rowCount = $(this).closest('tr');
        const rowToDelete = rowCount.prev('tr');
        const detailRow = rowCount.next('.detail-row');

        // Remove detail row if it exists
        if (detailRow.length) {
            detailRow.remove();
        }

        // Remove the main row
        rowToDelete.remove();
        rowCount.remove();
    });

    // Add Details
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

    $(document).on('click', '.addDetails', function() {
        const parentRowIndex = $(this).data('parent-row');
        $('#detailsModal').data('parent-row', parentRowIndex).modal('show');
    });

    $('#detailsModal').on('show.bs.modal', function () {
        const parentRowIndex = $(this).data('parent-row');
        const detailsBody = $(this).find('.details-body');
        detailsBody.empty(); // Clear existing rows

        // Get the hidden input value that contains the details JSON
        const detailsJson = $('input[id="payment_details"]').val();

        // Parse the JSON string into an object
        const existingDetails = detailsJson ? JSON.parse(detailsJson) : [];
        // Populate the modal with existing details
        existingDetails.forEach((detail, index) => {
            const detailRow = `
                <tr>
                    <td>
                        <select class="form-control" name="payment_details[${index}][payment_method]">
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method->payment_method_code }}" ${detail.payment_method === '{{ $method->payment_method_code }}' ? 'selected' : ''}>
                                    {{ $method->payment_name }} ({{ $method->payment_method_code }})
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)"  placeholder="Nominal" class="form-control" name="payment_details[${index}][payment_nominal]" value="${detail.payment_nominal.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
                    </td>
                    <td>
                        <input type="number" min=0 placeholder="BG Check Number" class="form-control" name="payment_details[${index}][bg_check_number]" value="${detail.bg_check_number}" />
                    </td>
                    <td>
                        <button class="btn btn-danger deleteDetail"><i class="material-icons-outlined remove-row">remove</i></button>
                    </td>
                </tr>
            `;
            detailsBody.append(detailRow);
        });
    });

    $('#addDetailRow').click(function() {
        const parentRowIndex = $('#detailsModal').data('parent-row');
        const detailsBody = $('#detailsModal').find('.details-body');
        let childRowCount = $(this).data('child-row-count') || 0;

        const newRow = `
            <tr>
                <td>
                    <select class="form-control" name="payment_details[${childRowCount}][payment_method]">
                            @foreach ($paymentMethods as $meth)
                                <option value="{{$meth->payment_method_code}}">{{$meth->payment_name.' ('.$meth->payment_method_code.')'}}</option>
                            @endforeach
                        </select>
                </td>
                <td>
                    <input type="text" oninput="formatNumber(this)" placeholder="Nominal" class="form-control text-end" name="payment_details[${childRowCount}][payment_nominal]" />
                </td>
                <td>
                    <input type="number" min=0 placeholder="BG Check Number" class="form-control" name="payment_details[${childRowCount}][bg_check_number]" />
                </td>
                <td>
                    <button class="btn btn-danger deleteDetail"><i class="material-icons-outlined remove-row">remove</i></button>
                </td>
            </tr>
        `;
        detailsBody.append(newRow);
    });

    $('#saveDetails').click(function() {
        const parentRowIndex = $('#detailsModal').data('parent-row');
        const detailsBody = $('#detailsModal').find('.details-body');

        // Prepare an array to hold the details
        const detailsArray = [];

        // Remove hidden inputs with a specific name, excluding those with an ID
        $(`#pay-row input[type="hidden"]`).filter(function() {
            return !$(this).attr('id'); // Exclude inputs that have an ID
        }).remove();
        let total = 0;

        // Loop through each detail row and collect the data
        detailsBody.find('tr').each(function(index) {
            const paymentMethod = $(this).find('select[name^="payment_details"] option:selected').val();
            const payment_nominal = $(this).find('input[name^="payment_details"]')[0].value.replace(/,/g, '');
            const bgCheckNumber = $(this).find('input[name^="payment_details"]')[1].value;
            total+=parseFloat(payment_nominal);
            const detail= {
                payment_method: paymentMethod,
                payment_nominal: payment_nominal,
                bg_check_number: bgCheckNumber,
            }
            // Push the collected data into the array
            detailsArray.push(detail);


            const hiddenInput = `
                <input type="hidden" name="payment_details[${index}][payment]" value='${JSON.stringify(detail)}' />
            `;

            // Append the hidden input to the specified <td>
            $(`#pay-row`).append(hiddenInput)
        });

        $('#total-payment-value').text((total+"").replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        // Set the collected details back to the main form (hidden input)
        $('input[id="payment_details"]').val(JSON.stringify(detailsArray));


        // Close the modal
        $('#detailsModal').modal('hide');
    });

    $('#selectInvoicesButton').click(function() {
    const selectedInvoices = [];
        $('#invoiceTable .invoice-checkbox:checked').each(function() {
            const invoiceNumber = $(this).val();
            const documentDate = $(this).data('document-date').substring(0,10);
            const debtBalance = $(this).data('debt-balance').debt_balance.substring(0, $(this).data('debt-balance').debt_balance.indexOf('.'));

            selectedInvoices.push({
                invoiceNumber: invoiceNumber,
                documentDate: documentDate,
                debtBalance: debtBalance
            });
        });

        $('#parentTbody').empty();
        // Add selected invoices to the payment details table
        selectedInvoices.forEach(invoice => {
            const currentRow = rowCount;
            const newRow = `
                <tr>
                    <td style="min-width:250px;">
                        <input type="text" name="details[${rowCount}][document_number]" class="form-control" value="${invoice.invoiceNumber}" readonly />
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][document_date]" class="form-control" value="${invoice.documentDate}" readonly />
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][debt_balance]" id="balance_${rowCount}" class="form-control text-end" value="${invoice.debtBalance.toString().split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly />
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)"  id="nominal_${rowCount}" class="form-control text-end" value="${invoice.debtBalance.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
                    </td>
                    <td>
                        <input type="text" id="discount_${rowCount}"  oninput="formatNumber(this)" name="details[${rowCount}][discount]"  class="form-control text-end" placeholder="Discount" value ="0"/>
                    </td>
                    <td>
                        <input type="text" id="nominal_payment_${rowCount}" name="details[${rowCount}][nominal_payment]" max="${invoice.debtBalance}" class="form-control text-end nominal" value="${invoice.debtBalance.toString().split('.')[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly/>
                    </td>
                    <td>
                        <input type="text" id="remaining_balance_${rowCount}" class="form-control text-end" value="0" readonly />
                    </td>
                    <td id="pay-row-${rowCount}">
                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                    </td>
                </tr>
            `;
        $('#parentTbody').append(newRow);
        document.getElementById(`nominal_${currentRow}`).addEventListener('input', function() {
            updateNominalValue(currentRow); // Call the function when the event occurs
        });
        document.getElementById(`discount_${currentRow}`).addEventListener('input', function() {
            updateNominalValue(currentRow); // Call the function when the event occurs
        });
        calculateTotals();
        addInputListeners();
        rowCount++; // Increment the row count for the next entry
        });

        $('#selectInvoiceModal').modal('hide'); // Close the modal after selection
    });

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

    $(document).on('click', '.deleteDetail', function() {

        $(this).closest('tr').remove();// Remove the detail row
    });

    document.getElementById('receivable-payment-form').addEventListener('submit', function(event) {
        event.preventDefault();
        let isValid = true; // Flag to check if all rows are valid
        let accountDiscSelected = true; // Flag for account disc selection

        const detailsJson = $(this).find(`input[id="payment_details"]`).val();
        const existingDetails = detailsJson ? JSON.parse(detailsJson) : [];

        // Check for unique payment methods and validate BG Check Number
        let selectedPaymentMethods = [];
        existingDetails.forEach((detail, detailIndex) => { // Sum up payment_nominal

            const paymentMethod = detail.payment_method;

            // Check if the payment method is "BG Check"
            if (paymentMethod == "BG") { // Adjust this condition based on your actual BG identifier
                // Find the BG Check Number input for the current detail index
                const bgCheckNumber = $(this).find(`input[name="payment_details][${detailIndex}][bg_check_number]"]`).val();
                if (!bgCheckNumber) {
                    isValid = false; // Set flag to false if BG Check Number is not filled
                    Swal.fire({
                        title: 'Error!',
                        text: `BG Check Number must be filled when using payment method "${paymentMethod}".`,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false; // Break the loop
                }
            }

            // Check for duplicate payment methods
            if (paymentMethod) {
                if (selectedPaymentMethods.includes(paymentMethod)) {
                    isValid = false; // Set flag to false if duplicate payment method found
                    Swal.fire({
                        title: 'Error!',
                        text: `Payment Method "${paymentMethod}" is selected multiple times`,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false; // Break the loop
                }
                selectedPaymentMethods.push(paymentMethod); // Add to the array of selected payment methods
            }
            let disc = false;
            $('#parentTbody tr').each(function(index) {
                let discountNominal = $(this).find(`input[name="details[${index}][discount]"]`).val();
                if(parseFloat(discountNominal)>0){
                    disc = true;
                }
            });

            let accDisc = document.getElementById('acc_disc').value;
            if(disc&&accDisc==''){
                isValid = false;
                Swal.fire({
                    title: 'Error!',
                    text: `Account Discount not selected yet`,
                    icon: 'warning',
                    confirmButtonText: 'OK'
                });
            }
            const nominalPayment = parseFloat(document.getElementById('total-value')) || 0;
            let sumPaymentDetails = parseFloat(document.getElementById('total-payment-value')) || 0;

            // Check if the nominal payment matches the sum of payment details
            if (nominalPayment !== sumPaymentDetails) {
                isValid = false; // Set flag to false if there's a mismatch
                Swal.fire({
                    title: 'Error!',
                    text: `Nominal Payment for row ${index + 1} must be equal to the sum of Payment Details.`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false; // Break the loop
            }

            // Check if the discount is filled and account disc is selected
        });

        // If account disc is required but not selected, show an error
        if (!accountDiscSelected) {
            isValid = false; // Set flag to false
            Swal.fire({
                title: 'Error!',
                text: 'Please select an Account Disc when a discount is filled.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }

        // If any row is invalid, prevent form submission
        if (!isValid) {
            event.preventDefault();// Prevent form submission
        }else{
            // Perform date validation via AJAX
            const documentDate = document.getElementById('document_date').value; // Assuming the date input has this ID
            $.ajax({
                url: '{{ route("checkDateToPeriode") }}',
                type: 'POST',
                data: {
                    date: documentDate,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response != true) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Date',
                            text: 'Tidak bisa input tanggal pada periode !',
                        });
                        return; // Stop further execution
                    }

                    // All validations passed, submit form
                    document.getElementById('receivable-payment-form').submit();
                },
                error: function(xhr) {
                    console.log(xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to validate date. Please try again.',
                    });
                }
            });
        }
    });
</script>

@endsection

@endsection
