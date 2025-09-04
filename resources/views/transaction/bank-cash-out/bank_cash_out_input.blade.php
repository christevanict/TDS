@extends('layouts.master')

@section('title', 'Input Kas Keluar')

@section('content')
<div class="row">
    <x-page-title title="{{__('Bank Cash Out')}}" pagetitle="{{__('Bank Cash Out')}} Input" />
    <hr>
    <div class="container content">
        <h2>{{__('Bank Cash Out')}} Input</h2>



        <form id="bank-cash-out-form" action="{{ isset($bankCashOut) ? route('transaction.bank_cash_out.update', $bankCashOut->id) : route('transaction.bank_cash_out.store') }}" method="POST">
            @csrf
            @if(isset($bankCashOut)) @method('PUT') @endif

            <div class="card mb-3">
                <div class="card-header">{{__('Bank Cash Out')}} Information</div>
                <div class="card-body">
                    <input type="hidden" name="token" id="token" value="{{$token}}">
                    <div class="row">
                        <div class="col-md-6">
                            {{-- <div class="form-group">
                                <label for="bank_cash_out_number">{{__('Bank Cash Out Number')}}</label>
                                <input type="text" name="bank_cash_out_number" class="form-control" readonly required value="{{ old('bank_cash_out_number', $bankCashOut->bank_cash_out_number ?? $bank_cash_out_number) }}">
                            </div>
                            <br> --}}
                            <div class="form-group">
                                <label for="bank_cash_out_date">Tanggal {{__('Bank Cash Out')}}</label>
                                <input type="date" name="bank_cash_out_date" class="form-control date-picker" required value="{{ old('bank_cash_out_date', $bankCashOut->bank_cash_out_date ?? date('Y-m-d')) }}" id="document_date">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="account_number">Keluar Dari</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-acc" autocomplete="off" class="form-control" placeholder="Search by Account Number or Account Name" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-acc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="account_number" id="account_number">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="note">{{__('Notes')}}</label>
                                <textarea name="note" class="form-control" rows="5">{{ old('note', $bankCashOut->note ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-0">
                            <div class="form-group">
                                {{-- <label for="department_code">{{__('Department Code')}}</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <select hidden class="form-select" id="company_code" name="company_code" required>
                                        @foreach ($companies as $company)
                                            <option value="{{$company->company_code}}">{{$company->company_name.' ('.$company->company_code.')'}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card for {{__('Bank Cash Out Details')}} -->
            <div class="card mb-3">
                <div class="card-header">{{__('Bank Cash Out Details')}}</div>
                <div class="card-body">
                    <table class="table" id="cash-out-details-table">
                        <thead>
                            <tr>
                                <th>{{__('Account Number')}}</th>
                                <th>Nominal</th>
                                <th>{{__('Notes')}}</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="form-group mb-3">
                                        <div class="input-group">
                                            <input type="text" id="search-acc-0" autocomplete="off" class="form-control" placeholder="Search by Account Number or Account Name" required>
                                            <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-0')"><i class="material-icons-outlined">edit</i></button>
                                        </div><div id="search-result-acc-0" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                            <!-- Search results will be injected here -->
                                        </div>
                                        <input type="hidden" name="details[0][account_number]" id="acc_number_0">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" oninput="formatNumber" name="details[0][nominal]" class="form-control text-end nominal" required placeholder="Nominal">
                                </td>
                                <td>
                                    <input type="text" name="details[0][note]" class="form-control" placeholder="...">
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <td></td>
                            <td><h5 class="text-end">Total: <span id="total-value">0</span></h5></td>
                            <td colspan="2"></td>
                        </tfoot>
                    </table>
                    <button type="button" id="add-row" class="btn btn-primary">Tambah Detail</button>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit Kas Keluar</button>
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
        // Check if the success message is present
        @if(session('success'))
            // Show SweetAlert confirmation modal
            Swal.fire({
                title: '{{__('Bank Cash Out')}} Created',
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
                        window.location.href = "{{ route('transaction.bank_cash_out.print', ['id' => ':id']) }}".replace(':id', id);
                    }
                }
            });
        @endif
    });
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

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
    setupSearch('search-acc', 'search-result-acc','account_number');
    setupSearch('search-acc-0', 'search-result-acc-0','acc_number_0');

    document.getElementById('account_number').value = '';

    function calculateTotals() {
        let total = 0;
        const disc_nominal = 0;
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

    function addInputListeners() {
        document.querySelectorAll('.nominal').forEach(function (input) {
            input.addEventListener('input', function () {
                calculateTotals(); // Calculate totals when any input changes
            });
        });
    }
    addInputListeners();

    function formatNumber(input) {
        const cursorPosition = input.selectionStart;
        const originalValue = input.value;

        let value = input.value.replace(/[^0-9.,]/g, '');

        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }

        let [integerPart, decimalPart = ''] = value.split('.');

        integerPart = integerPart.replace(/,/g, '');

        const formattedInteger = integerPart ? Number(integerPart).toLocaleString('en-US', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }) : '';

        const formattedValue = decimalPart ? `${formattedInteger}.${decimalPart}` : formattedInteger;

        const originalSeparators = (originalValue.slice(0, cursorPosition).match(/,/g) || []).length;
        const newSeparators = (formattedValue.slice(0, cursorPosition).match(/,/g) || []).length;
        let newCursorPosition = cursorPosition + (newSeparators - originalSeparators);

        if (originalValue[cursorPosition - 1] === '.' && !formattedValue.includes('.')) {
            input.value = formattedInteger + '.';
            newCursorPosition = input.value.length;
        } else {
            input.value = formattedValue;
            if (originalValue[cursorPosition - 1] === '.' && formattedValue.includes('.')) {
                newCursorPosition = formattedValue.indexOf('.') + 1;
            }
        }

        // Ensure cursor position is valid
        newCursorPosition = Math.min(Math.max(newCursorPosition, 0), input.value.length);

        // Set cursor position
        input.setSelectionRange(newCursorPosition, newCursorPosition);
    }
    // document.getElementById('department_code').value = '';

    document.addEventListener('DOMContentLoaded', function () {
        let rowCount = {{ isset($bankCashOut) ? count($bankCashOut->details) : 1 }};

        document.getElementById('add-row').addEventListener('click', function () {
            const detailsTableBody = document.querySelector('#cash-out-details-table tbody');
            const newRow = document.createElement('tr');
            newRow.innerHTML = `
                <td>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-${rowCount}" autocomplete="off" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-${rowCount}')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-${rowCount}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="details[${rowCount}][account_number]" id="acc_number_${rowCount}">
                    </div>
                </td>
                <td>
                    <input type="text" oninput="formatNumber" name="details[${rowCount}][nominal]" class="form-control text-end nominal" required placeholder="Nominal">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][note]" class="form-control"  placeholder="...">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                </td>
            `;
            detailsTableBody.appendChild(newRow);
            setupSearch(`search-acc-${rowCount}`, `search-result-acc-${rowCount}`,`acc_number_${rowCount}`);
            calculateTotals();
            addInputListeners();
            rowCount++;
        });

        document.querySelector('#cash-out-details-table').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                rowCount--;
            }
        });
    });

    document.getElementById('bank-cash-out-form').addEventListener('submit', function(event) {
        event.preventDefault();
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
                document.getElementById('bank-cash-out-form').submit();
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
    });
</script>
@endsection
@endsection
