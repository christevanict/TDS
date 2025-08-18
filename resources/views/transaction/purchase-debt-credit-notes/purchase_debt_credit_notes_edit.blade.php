@extends('layouts.master')

@section('title', 'Edit Purchase Credit Debt Note')

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
    <x-page-title title="Purchase Credit Debt Note" pagetitle="Edit Purchase Credit Debt Note" />
    <hr>
    <div class="container content">
        <h2>Edit Purchase Credit Debt Note</h2>

        <form id="purchase-credit-debt-note-form" action="{{ route('transaction.purchase_debt_credit_notes.update', $purchaseDebtCreditNote->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card mb-3">
                <div class="card-header">Purchase Credit Debt Note {{__('Information')}}</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="purchase_credit_note_date">Debt/Credit Note Date</label>
                                <input type="date" name="purchase_credit_note_date" id="purchase_credit_note_date" class="form-control date-picker" required value="{{ $purchaseDebtCreditNote->purchase_credit_note_date }}">
                            </div>

                            <div class="form-group mt-3">
                                <label for="invoice_number">Invoice Number</label>
                                <input type="text" name="invoice_number" id="invoice_number" class="form-control" required value="{{ $purchaseDebtCreditNote->invoice_number }}" readonly>
                                <input type="hidden" name="total_old" id="total_old" class="form-control" required value="{{ $purchaseDebtCreditNote->total }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-3">
                                <label for="account_payable">Account Payable</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-acc-payable" class="form-control" placeholder="Search by Account Number or Account Name" required readonly value="{{$purchaseDebtCreditNote->account_payable.' - '.$coas->firstWhere('account_number',$purchaseDebtCreditNote->account_payable)->account_name}}" >
                                        <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-payable')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-acc-payable" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="account_payable" id="account_payable" value="{{$purchaseDebtCreditNote->account_payable}}">
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
                <div class="card-header">Debt/Credit Note Details</div>
                <div class="card-body">
                    <table class="table table-bordered" id="details-table">
                        <thead>
                            <tr>
                                <th>{{__('Account Number')}}</th>
                                <th>Nominal</th>
                                <th>{{__('Notes')}}</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($purchaseDebtCreditNote->details)
                                @foreach ($purchaseDebtCreditNote->details as $index => $detail)
                                <tr class="detail-row">
                                    <td>
                                        <div class="form-group mb-3">
                                            <div class="input-group">
                                                <input type="text" id="search-acc-{{ $index }}" class="form-control" placeholder="Search by Account Number or Account Name" required readonly value="{{$detail->account_number.' - '.$coas->firstWhere('account_number',$detail->account_number)->account_name}}">
                                                <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-{{ $index }}')"><i class="material-icons-outlined">edit</i></button>
                                            </div>
                                            <div id="search-result-acc-{{ $index }}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                                <!-- Search results will be injected here -->
                                            </div>
                                            <input type="hidden" name="details[{{ $index }}][account_number]" id="acc_number_{{ $index }}" value="{{$detail->account_number}}">
                                        </div>
                                    </td>
                                    <td><input type="number" name="details[{{ $index }}][nominal]" class="form-control" required value="{{ $detail->nominal }}"></td>
                                    <td><input type="text" name="details[{{ $index }}][note]" class="form-control" value="{{ $detail->note }}"></td>
                                    <td>
                                        <button type="button" class="btn btn-danger remove-detail" title="Remove">
                                            <i class="material-icons-outlined remove-row">delete</i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    <button type="button" id="add-detail" class="btn btn-secondary">Add Detail</button>
                </div>
            </div>

            <div class="text-start mb-3">
                <button type="submit" class="btn btn-primary">Update Note</button>
            </div>
        </form>
        <form id="delete-form" action="{{ route('transaction.purchase_debt_credit_notes.destroy', $purchaseDebtCreditNote->id) }}" method="POST" style="display:inline;" >
            @csrf
            @method('DELETE')
            <input type="hidden" name="total_old" id="total_old" class="form-control" required value="{{ $purchaseDebtCreditNote->total }}">
            <input type="hidden" name="status" id="status" class="form-control" required value="{{ $purchaseDebtCreditNote->status }}">
            <input type="hidden" name="order" id="order" class="form-control" required value="{{ $purchaseDebtCreditNote->invoice_number }}">
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $purchaseDebtCreditNote->id }}')"
                @if(Auth::user()->role != 5 && Auth::user()->role != 7)
                    style="display: none"
                @endif
            ><i class="material-icons-outlined">delete</i></button>
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
    let detailIndex = {{ $purchaseDebtCreditNote->details ? count($purchaseDebtCreditNote->details) : 0 }};
    for (let i = 0; i < detailIndex; i++) {
        setupSearch(`search-acc-${i}`, `search-result-acc-${i}`,`acc_number_${i}`);
    }

    function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this purchase debet/credit note?',
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

    document.addEventListener('DOMContentLoaded', function() {
        let detailIndex = {{ $purchaseDebtCreditNote->details ? count($purchaseDebtCreditNote->details) : 0 }};

        document.getElementById('add-detail').addEventListener('click', function() {
            const newRow = document.createElement('tr');
            newRow.classList.add('detail-row');
            newRow.innerHTML = `
                <td>
                   <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-${detailIndex}" class="form-control" placeholder="Search by Account Number or Account Name" required>
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

            // Add event listener for removing detail rows
            newRow.querySelector('.remove-detail').addEventListener('click', function() {
                newRow.remove();
            });
        });

        // Add event listener for removing existing detail rows
        document.querySelectorAll('.remove-detail').forEach(function(button) {
            button.addEventListener('click', function() {
                button.closest('tr').remove();
            });
        });
    });
</script>
@endsection
@endsection
