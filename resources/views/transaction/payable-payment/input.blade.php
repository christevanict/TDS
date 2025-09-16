@extends('layouts.master')

@section('title', 'Input Pelunasan Hutang')

@section('css')
<style>
.nested-table {
    width: calc(100% - 40px);
    margin: 0;
    border: 1px solid #ddd;
}

.child-row {
    background-color: #f9f9f9;
}

.table th, .table td {
    padding: 8px;
}

.invoice-checkbox {
    width: 20px;
    height: 20px;
    transform: scale(1.5);
    margin: 0;
    cursor: pointer;
}

table {
    border-collapse: collapse;
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
    position: absolute;
    z-index: 1;
    bottom: 125%;
    left: 50%;
    margin-left: -60px;
    opacity: 0;
    transition: opacity 0.3s;
}

.tooltip:hover .tooltiptext {
    visibility: visible;
    opacity: 1;
}
</style>
@endsection

@section('content')
<div class="row">
    <x-page-title title="{{__('Payable Payment')}}" pagetitle="Input {{__('Payable Payment')}}" />
    <hr>
    <div class="container content">
        <h2>Input {{__('Payable Payment')}}</h2>

        <form id="payable-payment-form" action="{{ route('transaction.payable_payment.store') }}" method="POST">
            @csrf

            <div class="card mb-3">
                <div class="card-header">{{__('Payable Payment')}} Information</div>
                <div class="card-body">
                    <input type="hidden" name="token" id="token" value="{{ $token ?? '' }}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="search">{{__('Search Supplier')}}</label>
                                <input type="text" id="search" class="form-control" placeholder="Search by Supplier Code, Name, or Address" autocomplete="off">
                                <div id="search-results" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="supplier_code">{{__('Supplier Code')}}</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control" readonly>
                                <input type="hidden" name="category_customer" id="category_customer" class="form-control" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="supplier_name">{{__('Supplier Name')}}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <label for="document_date">Tanggal {{__('Payable Payment')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" required value="{{ date('Y-m-d') }}" id="document_date">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="acc_disc">Akun Diskon</label>
                                <div class="input-group">
                                    <input type="text" id="search-acc-disc" autocomplete="off" class="form-control" placeholder="Search by Account Number or Account Name">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-disc')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-disc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                </div>
                                <input type="hidden" name="acc_disc" id="acc_disc">
                            </div>
                            <br>
                            <div class="form-group mb-3" id="pay-row">
                                <button type="button" class="btn btn-info" id="btnPayment">
                                    Detail Metode Pembayaran
                                </button>
                                <br>
                                <h5>Total Pembayaran: <span id="total-payment-value">0</span></h5>
                                <input type="hidden" id="payment_details" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Detail {{__('Payable Payment')}}</div>
                <div class="card-body">
                    <h5 class="text-end">Total Pembayaran: <span id="total-value">0</span></h5>
                    <h5 class="text-end">Total Pembayaran Setelah Diskon: <span id="total-value-after-discount"></span></h5>
                    <table class="table" id="dynamicTable">
                        <thead>
                            <tr>
                                <td>Nomor Dokumen</td>
                                <td>Tanggal Dokumen</td>
                                <td>Jumlah Hutang</td>
                                <td>Nominal Pembayaran</td>
                                <td>Diskon</td>
                                <td>Total Pembayaran</td>
                                <td>Sisa Hutang</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody id="parentTbody">
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-primary" id="addRow">Pilih Dokumen</button>
                </div>
            </div>

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
                                        <th>Sisa Hutang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($debts as $pi)
                                    <tr data-supplier="{{ $pi->supplier_code }}">
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" class="invoice-checkbox" data-debt-balance="{{ $pi }}" data-document-date="{{ $pi->document_date }}" value="{{ $pi->document_number }}">
                                        </td>
                                        <td>{{ $pi->document_number }}</td>
                                        <td>{{ $pi->document_date }}</td>
                                        <td>Rp {{ number_format($pi->debt_balance, 0, '.', ',') }}</td>
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
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Payable Payment')}}</button>
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
        @if(session('success'))
            Swal.fire({
                title: '{{__('Payable Payment')}} Created',
                text: 'Do you want to print it?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    var id = "{{ session('id') }}";
                    if (id) {
                        window.location.href = "{{ route('transaction.payable_payment.print', ['id' => ':id']) }}".replace(':id', id);
                    }
                }
            });
        @endif
    });

    var now = new Date(),
        maxDate = now.toISOString().substring(0, 10);
    $('#document_date').prop('max', maxDate);
    let supplierId = '';

    const coas = @json($coas);
    function setupSearch(inputId, resultsContainerId, inputHid) {
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
        document.getElementById('acc_disc').value = '';
    }
    function updateActiveItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndex);
        });
        if (activeIndex >= 0) {
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    setupSearch('search-acc-disc', 'search-result-acc-disc', 'acc_disc');

    const suppliers = @json($suppliers);
    document.getElementById('search').addEventListener('input', function () {
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        if (query.length > 0) {
            let filteredSuppliers = suppliers.filter(c =>
                c.supplier_code.toLowerCase().includes(query) ||
                c.supplier_name.toLowerCase().includes(query) ||
                c.address.toLowerCase().includes(query));
            if (filteredSuppliers.length > 0) {
                resultsContainer.style.display = 'block';
                filteredSuppliers.forEach(customer => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.innerHTML = `
                        <strong>${customer.supplier_code}</strong> -
                        ${customer.supplier_name} <br>
                        <small>${customer.address}</small>`;
                    listItem.addEventListener('click', function(e) {
                        e.preventDefault();
                        document.getElementById('supplier_code').value = customer.supplier_code;
                        document.getElementById('supplier_name').value = customer.supplier_name;
                        document.getElementById('address').value = customer.address;
                        resultsContainer.style.display = 'none';
                        supplierId = customer.supplier_code;
                        if (supplierId === "") {
                            $('#invoiceTable tbody tr').show();
                        } else {
                            $('#invoiceTable tbody tr').each(function () {
                                const selectedSuppliers = $(this).data('supplier');
                                if (selectedSuppliers == supplierId) {
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
        let totalNominalPayment = 0;
        let totalDiscount = 0;
        document.querySelectorAll('#parentTbody tr').forEach(function (row, index) {
            const nominalPayment = parseFloat(document.getElementById(`nominal_payment_${index}`).value.replace(/,/g, '')) || 0;
            const discount = parseFloat(document.getElementById(`discount_${index}`).value.replace(/,/g, '')) || 0;
            totalNominalPayment += nominalPayment;
            totalDiscount += discount;
        });
        const formattedTotal = totalNominalPayment.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const formattedTotalAfterDiscount = (totalNominalPayment - totalDiscount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('total-value').innerText = formattedTotal;
        document.getElementById('total-value-after-discount').innerText = formattedTotalAfterDiscount;
    }

    calculateTotals();

    function updateNominalValue(row) {
        const balance = parseFloat(document.getElementById(`balance_${row}`).value.replace(/,/g, '')) || 0;
        const nominal = parseFloat(document.getElementById(`nominal_${row}`).value.replace(/,/g, '')) || 0;
        const disc_nominal = parseFloat(document.getElementById(`discount_${row}`).value.replace(/,/g, '')) || 0;


        const nominalValue = parseFloat(nominal+disc_nominal).toFixed(2)+"";
        const nominalInput = document.getElementById(`nominal_payment_${row}`);
        const remainBalanceInput = document.getElementById(`remaining_balance_${row}`);

        let formattedValue = nominalValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

        nominalInput.value = formattedValue; // Update nominal value
        remainBalanceInput.value = parseFloat(balance - nominalValue).toFixed(2).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        calculateTotals();
    }

    document.addEventListener('click', function(event) {
        if (!event.target.closest('#search')) {
            document.getElementById('search-results').style.display = 'none';
            document.getElementById('search').value = '';
        }
    });

    function formatNumber(input) {
        const cursorPosition = input.selectionStart;
        const originalValue = input.value;
        let value = input.value.replace(/[^0-9.,-]/g, '');
        const hasNegative = value.startsWith('-');
        value = value.replace(/-/g, ''); // Remove all minus signs
        if (hasNegative) {
            value = '-' + value; // Re-add single minus sign at start if present
        }
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }
        let [integerPart, decimalPart = ''] = value.split('.');
        const isNegative = integerPart.startsWith('-');
        integerPart = integerPart.replace(/-/g, ''); // Remove negative sign for formatting
        integerPart = integerPart.replace(/,/g, '');
        const formattedInteger = integerPart ? Number(integerPart).toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }) : '';
        let formattedValue = decimalPart ? `${formattedInteger}.${decimalPart}` : formattedInteger;
        if (isNegative && (formattedInteger || decimalPart)) {
            formattedValue = '-' + formattedValue; // Add negative sign if valid number
        } else if (isNegative && !formattedInteger && !decimalPart && originalValue === '-') {
            formattedValue = '-'; // Handle case where input is just '-'
        }
        const originalSeparators = (originalValue.slice(0, cursorPosition).match(/,/g) || []).length;
        const newSeparators = (formattedValue.slice(0, cursorPosition).match(/,/g) || []).length;
        let newCursorPosition = cursorPosition + (newSeparators - originalSeparators);
        if (originalValue[cursorPosition - 1] === '.' && !formattedValue.includes('.')) {
            input.value = (isNegative ? '-' : '') + formattedInteger + '.';
            newCursorPosition = input.value.length;
        } else {
            input.value = formattedValue;
            if (originalValue[cursorPosition - 1] === '.' && formattedValue.includes('.')) {
                newCursorPosition = formattedValue.indexOf('.') + 1;
            }
        }
        if (isNegative && cursorPosition <= 1 && originalValue.startsWith('-')) {
            newCursorPosition = Math.max(1, newCursorPosition); // Prevent cursor before negative sign
        }
        newCursorPosition = Math.min(Math.max(newCursorPosition, 0), input.value.length);
        input.setSelectionRange(newCursorPosition, newCursorPosition);
    }

    let rowCount = 0;

    $('#addRow').click(function() {
        $('#selectInvoiceModal').modal('show');
    });

    $('#btnPayment').click(function() {
        $('#detailsModal').modal('show');
    });

    $('#detailsModal').on('show.bs.modal', function () {
        const detailsBody = $(this).find('.details-body');
        detailsBody.empty();
        const detailsJson = $('input[id="payment_details"]').val();
        const existingDetails = detailsJson ? JSON.parse(detailsJson) : [];
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
                        <input type="text" oninput="formatNumber(this)" class="form-control text-end" name="payment_details[${index}][payment_nominal]" value="${detail.payment_nominal.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
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
        const detailsBody = $('#detailsModal').find('.details-body');
        const childRowCount = detailsBody.find('tr').length;
        const newRow = `
            <tr>
                <td>
                    <select class="form-control" name="payment_details[${childRowCount}][payment_method]">
                        @foreach ($paymentMethods as $method)
                            <option value="{{ $method->payment_method_code }}">{{ $method->payment_name }} ({{ $method->payment_method_code }})</option>
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
        const detailsBody = $('#detailsModal').find('.details-body');
        const detailsArray = [];
        let total = 0;

        $(`#pay-row input[type="hidden"]`).filter(function() {
            return !$(this).attr('id');
        }).remove();

        detailsBody.find('tr').each(function(index) {
            const paymentMethod = $(this).find('select[name^="payment_details"] option:selected').val();
            const paymentNominal = $(this).find('input[name$="[payment_nominal]"]').val().replace(/,/g, '');
            const bgCheckNumber = $(this).find('input[name$="[bg_check_number]"]').val();
            total += parseFloat(paymentNominal) || 0;

            const detail = {
                payment_method: paymentMethod,
                payment_nominal: paymentNominal,
                bg_check_number: bgCheckNumber,
            };
            detailsArray.push(detail);

            const hiddenInput = `
                <input type="hidden" name="payment_details[${index}][payment]" value='${JSON.stringify(detail)}' />
            `;
            $(`#pay-row`).append(hiddenInput);
        });

        $('#total-payment-value').text(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('input[id="payment_details"]').val(JSON.stringify(detailsArray));
        $('#detailsModal').modal('hide');
    });

    $('#selectInvoicesButton').click(function() {
        const selectedInvoices = [];
        $('#invoiceTable .invoice-checkbox:checked').each(function() {
            const invoiceNumber = $(this).val();
            const documentDate = $(this).data('document-date').substring(0, 10);
            const debtBalance = $(this).data('debt-balance').debt_balance.toString();

            selectedInvoices.push({
                invoiceNumber: invoiceNumber,
                documentDate: documentDate,
                debtBalance: debtBalance
            });
        });

        $('#parentTbody').empty();
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
                        <input type="text" name="details[${rowCount}][debt_balance]" id="balance_${rowCount}" class="form-control text-end" value="${invoice.debtBalance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly />
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)"  id="nominal_${rowCount}" class="form-control text-end" value="${invoice.debtBalance.replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" />
                    </td>
                    <td>
                        <input type="text" id="discount_${rowCount}"  oninput="formatNumber(this)" name="details[${rowCount}][discount]"  class="form-control text-end" placeholder="Discount" value ="0"/>
                    </td>
                    <td>
                        <input type="text" id="nominal_payment_${rowCount}" name="details[${rowCount}][nominal_payment]" max="${invoice.debtBalance}" class="form-control text-end nominal" value="${invoice.debtBalance.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly/>
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
                updateNominalValue(currentRow);
            });
            document.getElementById(`discount_${currentRow}`).addEventListener('input', function() {
                updateNominalValue(currentRow);
            });
            calculateTotals();
            rowCount++;
        });

        $('#selectInvoiceModal').modal('hide');
    });

    $(document).on('click', '.deleteRow', function() {
        $(this).closest('tr').remove();
        calculateTotals();
    });

    $(document).on('click', '.deleteDetail', function() {
        $(this).closest('tr').remove();
    });

    document.getElementById('payable-payment-form').addEventListener('submit', function(event) {
        event.preventDefault();
        let isValid = true;
        const detailsJson = $(this).find(`input[id="payment_details"]`).val();
        const existingDetails = detailsJson ? JSON.parse(detailsJson) : [];
        let selectedPaymentMethods = [];

        if (existingDetails.length > 0) {
            existingDetails.forEach((detail, detailIndex) => {
                const paymentMethod = detail.payment_method;
                if (paymentMethod === "BG") {
                    const bgCheckNumber = detail.bg_check_number;
                    if (!bgCheckNumber) {
                        isValid = false;
                        Swal.fire({
                            title: 'Error!',
                            text: `BG Check Number must be filled when using payment method "${paymentMethod}".`,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                }

                if (paymentMethod) {
                    if (selectedPaymentMethods.includes(paymentMethod)) {
                        isValid = false;
                        Swal.fire({
                            title: 'Error!',
                            text: `Payment Method "${paymentMethod}" is selected multiple times.`,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                    selectedPaymentMethods.push(paymentMethod);
                }
            });
        }

        let hasDiscount = false;
        let totalNominalPayment = 0;
        $('#parentTbody tr').each(function(index) {
            const discountNominal = $(this).find(`input[name="details[${index}][discount]"]`).val().replace(/,/g, '');
            const nominal = parseFloat($(this).find(`input[name="details[${index}][nominal_payment]"]`).val().replace(/,/g, '')) || 0;
            if (parseFloat(discountNominal) > 0) {
                hasDiscount = true;
            }
            totalNominalPayment += nominal;
        });

        const accDisc = document.getElementById('acc_disc').value;
        if (hasDiscount && !accDisc) {
            isValid = false;
            Swal.fire({
                title: 'Error!',
                text: `Account Discount must be selected when a discount is applied.`,
                icon: 'warning',
                confirmButtonText: 'OK'
            });
        }

        const nominalPayment = parseFloat(document.getElementById('total-payment-value').innerText.replace(/,/g, '')) || 0;
        let sumPaymentDetails = parseFloat(document.getElementById('total-value-after-discount').innerText.replace(/,/g, '')) || 0;

        if (nominalPayment!=sumPaymentDetails) {
            isValid = false; // Set flag to false
            Swal.fire({
                title: 'Error!',
                text: 'Jumlah pembayaran belum sama dengan total pembayaran setelah diskon',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        }

        if (!isValid) {
            event.preventDefault();
        }else{
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
                    document.getElementById('payable-payment-form').submit();
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
