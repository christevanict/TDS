@extends('layouts.master')

@section('title', 'Input Purchase Credit Note')

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
</style>
@endsection

@section('content')
<div class="row">
    <x-page-title title="Purchase Credit Note" pagetitle="Purchase Credit Note Input" />
    <hr>
    <div class="container content">
        <h2>Purchase Credit Note Input</h2>

        <form id="purchase-credit-note-form" action="{{ route('transaction.purchase_debt_credit_notes.store') }}" method="POST">
            @csrf


            <input type="hidden" name="status" value="credit">
            <div class="card mb-3">
                <div class="card-header">Purchase Credit Note {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="purchase_credit_note_date">Credit Note Date</label>
                                <input type="date" name="purchase_credit_note_date" id="purchase_credit_note_date" class="form-control date-picker" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group mt-3">
                                <label for="invoice_number">Invoice Number</label>
                                <select name="invoice_number" id="invoice_number" class="form-control" required>
                                    <option value="" disabled selected></option>
                                    @foreach($purchaseInvoices as $invoice)
                                        <option value="{{ $invoice->purchase_invoice_number }}">{{ $invoice->purchase_invoice_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="account_payable">Account Payable</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-acc-payable" class="form-control" autocomplete="off" placeholder="Search by Account Number or Account Name" required >
                                        <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-payable')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-acc-payable" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="account_payable" id="account_payable">
                                </div>
                            </div>
                            <div class="form-group">
                                {{-- <label for="department_code">{{__('Department Code')}}</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">Credit Note Details</div>
                <div class="card-body">
                    <table class="table table-bordered" id="details-table">
                        <thead>
                            <tr>
                                <th>{{__('Account Number')}}</th>
                                <th>Nominal Debit</th>
                                <th>{{__('Notes')}}</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="detail-row">
                                <td>
                                    <div class="form-group mb-3">
                                        <div class="input-group">
                                            <input type="text" id="search-acc-0" class="form-control" autocomplete="off" placeholder="Search by Account Number or Account Name" required>
                                            <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-0')"><i class="material-icons-outlined">edit</i></button>
                                        </div>
                                        <div id="search-result-acc-0" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                            <!-- Search results will be injected here -->
                                        </div>
                                        <input type="hidden" name="details[0][account_number]" id="acc_number_0">
                                    </div>
                                </td>
                                <td><input type="number" name="details[0][nominal]" class="form-control" required></td>
                                <td><input type="text" name="details[0][note]" class="form-control"></td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-detail" title="Remove">
                                        <i class="material-icons-outlined remove-row">delete</i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <button type="button" id="add-detail" class="btn btn-primary">Add Detail</button>
                </div>
            </div>

            <div class="text-start mb-3">
                <button type="submit" class="btn btn-success">Save Credit Note</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#purchase_credit_note_date').prop('max', maxDate);

     const coas = @json($coas);
    function setupSearch(inputId, resultsContainerId,inputHid) {
        const inputElement = document.getElementById(inputId);
        const resultsContainer = document.getElementById(resultsContainerId);
        inputElement.addEventListener('input', function () {
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
    }
    function clearInput(inputId) {
        document.getElementById(inputId).value = '';
        document.getElementById(inputId).readOnly = false;
    }
    setupSearch('search-acc-payable', 'search-result-acc-payable','account_payable');
    setupSearch('search-acc-0', 'search-result-acc-0','acc_number_0');

    document.addEventListener('DOMContentLoaded', function() {
        let detailIndex = 1;

        document.getElementById('add-detail').addEventListener('click', function() {
            const newRow = document.createElement('tr');
            newRow.classList.add('detail-row');
            newRow.innerHTML = `
                <td>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-${detailIndex}" class="form-control" autocomplete="off" placeholder="Search by Account Number or Account Name" required>
                            <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-${detailIndex}')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-${detailIndex}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="details[${detailIndex}][account_number]" id="acc_number_${detailIndex}">
                    </div>
                </td>
                <td><input type="number" name="details[${detailIndex}][nominal]" class="form-control" required></td>
                <td><input type="text" name="details[${detailIndex}][note]" class="form-control"></td>
                <td><button type="button" class="btn btn-danger remove-detail"><i class="material-icons-outlined remove-row">delete</i></button></td>
            `;
            document.querySelector('#details-table tbody').appendChild(newRow);
            setupSearch(`search-acc-${detailIndex}`, `search-result-acc-${detailIndex}`,`acc_number_${detailIndex}`);
            detailIndex++;

            newRow.querySelector('.remove-detail').addEventListener('click', function() {
                newRow.remove();
            });
        });

        document.querySelectorAll('.remove-detail').forEach(function(button) {
            button.addEventListener('click', function() {
                button.closest('tr').remove();
            });
        });
    });
</script>
@endsection

@endsection
