@extends('layouts.master')

@section('title', 'Edit Pelunasan Piutang')

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

    .save-button-container {
        display: flex;
        justify-content: flex-start;
        margin-top: 20px;
    }
</style>
@endsection

@section('content')
<div class="row">
    <x-page-title title="{{__('Receivable Payment')}}" pagetitle="Edit {{__('Receivable Payment')}}" />
    <hr>
    <div class="container content">
        <h2>Edit {{__('Receivable Payment')}}</h2>
        @if (!$editable)
        <h7 style="color: red">Alasan tidak bisa edit</h7>
            <ul>
                <li>Sudah di Closing</li>
            </ul>
        @endif
        <form id="print-form" target="_blank" action="{{ route('transaction.receivable_payment.print', $receivable->id) }}" method="GET" style="display:inline;">
            <button type="submit" class="btn btn-dark mb-3" @if(!in_array('print', $privileges)) disabled @endif>
                Print {{__('Receivable Payment')}}</button>
        </form>

        @if (!$receivable)
            <div class="alert alert-danger">
                <strong>Error:</strong> {{__('Receivable Payment')}} tidak ditemukan.
                <a href="{{ route('transaction.receivable_payment.index') }}" class="btn btn-secondary">Go Back</a>
            </div>
            @return;
        @endif

        <form id="receivable-payment-form" action="{{ route('transaction.receivable_payment.update', $receivable->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-3">
                <div class="card-header">{{__('Receivable Payment')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_code">{{__('Customer Code')}}</label>
                                <input type="text" name="customer_code" id="customer_code" class="form-control" value="{{ $receivable->customer_code }}" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $receivable->customer->customer_name }}" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" value="{{ $receivable->customer->address }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            <div class="form-group">
                                <label for="document_date">Tanggal {{__('Receivable Payment')}}</label>
                                <input type="date" id="document_date" name="document_date" class="form-control date-picker" readonly value="{{ $receivable->receivable_payment_date }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="acc_disc">Akun Diskon</label>
                                <div class="input-group">
                                    <input type="text" id="search-acc-disc" class="form-control" autocomplete="off" placeholder="Search by Account Number or Account Name" readonly value="{{ $receivable->acc_disc ? $receivable->acc_disc . ' - ' . $coas->firstWhere('account_number', $receivable->acc_disc)->account_name : '' }}">
                                    <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-disc')"><i class="material-icons-outlined">edit</i></button>
                                </div>
                                <div id="search-result-acc-disc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                </div>
                                <input type="hidden" name="acc_disc" id="acc_disc" value="{{ $receivable->acc_disc }}">
                            </div>
                            <div class="form-group mb-3" id="pay-row">
                                <button type="button" class="btn btn-info" id="btnPayment">
                                    Detail Metode Pembayaran
                                </button>
                                <br>
                                <h5>Total Pembayaran: <span id="total-payment-value">{{ number_format($receivable->total_debt, 0, '.', ',') }}</span></h5>
                                <input type="hidden" id="payment_details" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Detail {{__('Receivable Payment')}}</div>
                <div class="card-body">
                    <h5 class="text-end">Total Pembayaran: <span id="total-value">{{ number_format($receivable->total_debt, 0, '.', ',') }}</span></h5>
                    <h5 class="text-end">Total Pembayaran Setelah Diskon: <span id="total-value-after-discount">{{ number_format($receivable->total_debt, 0, '.', ',') }}</span></h5>
                    <table class="table" id="dynamicTable">
                        <thead>
                            <tr>
                                <td>Nomor Dokumen</td>
                                <td>Tanggal Dokumen</td>
                                <td>Jumlah Piutang</td>
                                <td>Nominal Pembayaran</td>
                                <td>Diskon</td>
                                <td>Total Pembayaran</td>
                                <td>Sisa Piutang</td>
                                <td>Action</td>
                            </tr>
                        </thead>
                        <tbody id="parentTbody">
                            @foreach ($receivable_details as $index => $detail)
                            <tr>
                                <td style="min-width:250px;">
                                    <input type="text" name="details[{{ $index }}][document_number]" class="form-control" value="{{ $detail->document_number }}" readonly />
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $index }}][document_date]" class="form-control" value="{{ substr($detail->document_date, 0, 10) }}" readonly />
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $index }}][document_payment]" id="balance_{{ $index }}" class="form-control text-end" value="{{ number_format($detail->receivables->debt_balance+$detail->document_payment, 0, '.', ',') }}" readonly />
                                </td>
                                <td>
                                    <input type="text"  id="nominal_{{ $index }}" oninput="formatNumber(this)" class="form-control text-end nominal" value="{{ number_format(($detail->nominal - $detail->discount), 0, '.', ',') }}" />
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $index }}][discount]" id="discount_{{ $index }}" oninput="formatNumber(this)" class="form-control text-end nominal" value="{{ number_format($detail->discount, 0, '.', ',') }}" />
                                </td>
                                <td>
                                    <input type="text" name="details[{{ $index }}][nominal_payment]" id="nominal_payment_{{ $index }}" class="form-control text-end" value="{{ number_format($detail->nominal, 0, '.', ',') }}" readonly />
                                </td>
                                <td>
                                    <input type="text"  id="remaining_balance_{{ $index }}" class="form-control text-end" value="{{ number_format($detail->document_payment - $detail->nominal, 0, '.', ',') }}" readonly />
                                </td>
                                <td id="pay-row-{{$index}}">
                                    <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                                </td>
                            </tr>
                            @endforeach
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
                                        <th>Sisa Piutang</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($receivables as $si)
                                    <tr data-customer={{$si->customer_code}}>
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" class="invoice-checkbox" data-debt-balance="{{ $si }}" data-document-date="{{ $si->document_date }}" value="{{ $si->document_number }}">
                                        </td>
                                        <td>{{ $si->document_number }}</td>
                                        <td>{{ $si->document_date }}</td>
                                        <td>Rp {{ number_format($si->debt_balance,0,'.',',')}}</td>
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

            <div class="save-button-container">
                @if(in_array('update', $privileges)&&$editable)
                <button type="submit" class="btn btn-success mb-3">Update {{__('Receivable Payment')}}</button>
                @endif
                <a class="mb-3 btn btn-secondary" href="{{ route('transaction.receivable_payment') }}">Back</a>
            </div>
        </form>
        <form id="delete-form" action="{{ route('transaction.receivable_payment.destroy', $receivable->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('POST')
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $receivable->id }}')"
                @if(!in_array('delete', $privileges) ||!$editable) disabled @endif
            ><i class="material-icons-outlined">delete</i></button>
        </form>
    </div>
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
    function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this {{__('Receivable Payment')}}?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            confirmButtonColor: '#0c6efd',
            cancelButtonColor: '#d33',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form').submit();
            }
        });
    }

    const coas = @json($coas);
    let rowCount = {{ isset($receivable_details) ? count($receivable_details) : 1 }};
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
    function calculateTotals() {
        let totalNominalPayment = 0;
        let totalDiscount = 0;
        document.querySelectorAll('#parentTbody tr').forEach(function (row, index) {
            const nominalPayment = parseFloat(document.getElementById(`nominal_payment_${index}`).value.replace(/,/g, '')) || 0;
            const discount = parseFloat(document.getElementById(`discount_${index}`).value.replace(/,/g, '')) || 0;
            console.log(discount);
            console.log(nominalPayment);

            totalNominalPayment += nominalPayment;
            totalDiscount += discount;
        });
        const formattedTotal = totalNominalPayment.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const formattedTotalAfterDiscount = (totalNominalPayment - totalDiscount).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('total-value').innerText = formattedTotal;
        document.getElementById('total-value-after-discount').innerText = formattedTotalAfterDiscount;
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

    document.querySelectorAll('.nominal').forEach(function (input) {
        input.addEventListener('input', function () {
            const row = this.id.split('_')[1];
            updateNominalValue(row);
        });
    });
    $('#addRow').click(function() {
        $('#selectInvoiceModal').modal('show');
    });

    $('#btnPayment').click(function() {
        $('#detailsModal').modal('show');
    });

    let details = @json($receivable_detail_pays);
    const paymentMethods = @json($paymentMethods);

    function initDataPayment(){
        const detailsBody = $(this).find('.details-body');
        detailsBody.empty();

        function groupAndSum(data) {
            const grouped = data.reduce((acc, { payment_method, payment_nominal, bg_check_number }) => {
                const key = `${payment_method}-${bg_check_number || 'NO_BG'}`;
                if (!acc[key]) {
                    acc[key] = { payment_method, bg_check_number, total_nominal: 0 };
                }
                acc[key].total_nominal += parseFloat(payment_nominal);
                return acc;
            }, {});
            return Object.values(grouped);
        }

        const groupedData = groupAndSum(details);
        const detailsArray = [];
        let total = 0;

        groupedData.forEach((detail, index) => {
            total+= detail.total_nominal;
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
                    <td><input type="text" oninput="formatNumber(this)" class="form-control" name="payment_details[${index}][payment_nominal]" value="${detail.total_nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" /></td>
                    <td><input type="number" placeholder="BG Check Number" class="form-control" name="payment_details[${index}][bg_check_number]" value="${detail.bg_check_number || ''}" /></td>
                    <td><button class="btn btn-danger deleteDetail"><i class="material-icons-outlined remove-row">remove</i></button></td>
                </tr>
            `;
            const detail2 = {
                payment_method: detail.payment_method,
                payment_nominal: detail.total_nominal,
                bg_check_number: detail.bg_check_number,
            };

            const hiddenInput = `
                <input type="hidden" name="payment_details[${index}][payment]" value='${JSON.stringify(detail2)}' />
            `;
            document.getElementById('total-payment-value').innerText = total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            $(`#pay-row`).append(hiddenInput);

            detailsBody.append(detailRow);
        });
    }

    initDataPayment();


    $('#detailsModal').on('show.bs.modal', function () {
        const detailsBody = $(this).find('.details-body');
        detailsBody.empty();

        function groupAndSum(data) {
            const grouped = data.reduce((acc, { payment_method, payment_nominal, bg_check_number }) => {
                const key = `${payment_method}-${bg_check_number || 'NO_BG'}`;
                if (!acc[key]) {
                    acc[key] = { payment_method, bg_check_number, total_nominal: 0 };
                }
                acc[key].total_nominal += parseFloat(payment_nominal);
                return acc;
            }, {});
            return Object.values(grouped);
        }

        const groupedData = groupAndSum(details);
        groupedData.forEach((detail, index) => {
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
                    <td><input type="text" oninput="formatNumber(this)" class="form-control" name="payment_details[${index}][payment_nominal]" value="${detail.total_nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" /></td>
                    <td><input type="number" placeholder="BG Check Number" class="form-control" name="payment_details[${index}][bg_check_number]" value="${detail.bg_check_number || ''}" /></td>
                    <td><button class="btn btn-danger deleteDetail"><i class="material-icons-outlined remove-row">remove</i></button></td>
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
                <td><input type="text" oninput="formatNumber(this)" placeholder="Nominal" class="form-control" name="payment_details[${childRowCount}][payment_nominal]" /></td>
                <td><input type="number" placeholder="BG Check Number" class="form-control" name="payment_details[${childRowCount}][bg_check_number]" /></td>
                <td><button class="btn btn-danger deleteDetail"><i class="material-icons-outlined remove-row">remove</i></button></td>
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
        details = detailsArray;

        $('#total-payment-value').text(total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','));
        $('input[id="payment_details"]').val(JSON.stringify(detailsArray));
        $('#detailsModal').modal('hide');
    });

    $('#selectInvoicesButton').click(function() {
    const selectedInvoices = [];
        $('#invoiceTable .invoice-checkbox:checked').each(function() {
            const invoiceNumber = $(this).val();
            const documentDate = $(this).data('document-date').substring(0,10);
            const debtBalance = $(this).data('debt-balance').debt_balance.toString();

            selectedInvoices.push({
                invoiceNumber: invoiceNumber,
                documentDate: documentDate,
                debtBalance: debtBalance
            });
        });

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

    $(document).on('click', '.deleteRow', function() {
        $(this).closest('tr').remove();
        rowCount--;
        calculateTotals();
    });

    $(document).on('click', '.deleteDetail', function() {
        $(this).closest('tr').remove();
    });

    document.getElementById('receivable-payment-form').addEventListener('submit', function(event) {
        event.preventDefault();
        let isValid = true;
        const detailsJson = $(this).find(`input[id="payment_details"]`).val();
        const existingDetails = detailsJson ? JSON.parse(detailsJson) : [];
        let selectedPaymentMethods = [];

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

        let hasDiscount = false;
        $('#parentTbody tr').each(function(index) {
            const discountNominal = $(this).find(`input[name="details[${index}][discount]"]`).val().replace(/,/g, '');
            if (parseFloat(discountNominal) > 0) {
                hasDiscount = true;
            }
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

        let totalNominalPayment = 0;
        $('#parentTbody tr').each(function(index) {
            const nominal = parseFloat($(this).find(`input[name="details[${index}][nominal_payment]"]`).val().replace(/,/g, '')) || 0;
            totalNominalPayment += nominal;
        });

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
            const documentDate = document.getElementById('document_date').value;
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
