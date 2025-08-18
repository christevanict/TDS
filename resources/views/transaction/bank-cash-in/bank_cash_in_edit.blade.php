@extends('layouts.master')

@section('title', 'Edit Kas Masuk')

@section('content')
<div class="row">
    <x-page-title title="Edit {{__('Bank Cash In')}}" pagetitle="Edit {{__('Bank Cash In')}}" />
    <hr>
    <div class="container content">
        <h2>Edit {{__('Bank Cash In')}} Input</h2>
        @if (!$editable)
        <h7 style="color: red">Alasan tidak bisa edit</h7>
            <ul>
                <li>Sudah di Closing</li>
            </ul>
        @endif

        <div id="message-container">
            @if(session('success'))
                <div id="success-message" class="alert alert-success fade show">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div id="error-message" class="alert alert-danger fade show">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- Mengubah form method menjadi POST -->
        <form id="bank-cash-in-form" action="{{ route('transaction.bank_cash_in.update', $bankCashIn->id) }}" method="POST">
            @csrf
            <!-- Tidak perlu @method('PUT'), karena hanya menggunakan POST -->

            <!-- Card for {{__('Bank Cash In')}} Information -->
            <div class="card mb-3">
                <div class="card-header">{{__('Bank Cash In')}} Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bank_cash_in_number">{{__('Bank Cash In Number')}}</label>
                                <input type="text" name="bank_cash_in_number" class="form-control" readonly required value="{{ old('bank_cash_in_number', $bankCashIn->bank_cash_in_number) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="bank_cash_in_date">Tanggal {{__('Bank Cash In')}}</label>
                                <input type="date" name="bank_cash_in_date" class="form-control date-picker" required value="{{ old('bank_cash_in_date', $bankCashIn->bank_cash_in_date) }}" id="document_date">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="account_number">Masuk Ke Akun</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-acc" class="form-control" autocomplete="off" placeholder="Search by Account Number or Account Name" required readonly value="{{$bankCashIn->account_number.' - '.$coas->firstWhere('account_number',$bankCashIn->account_number)->account_name}}">
                                        <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-acc" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="account_number" id="account_number" value="{{$bankCashIn->account_number}}">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="note">{{__('Notes')}}</label>
                                <textarea name="note" class="form-control" rows="5">{{ old('note', $bankCashIn->note) }}</textarea>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                {{-- <label for="department_code">{{__('Department Code')}}</label> --}}
                                <input type="hidden" name="department_code" id="department_code" class="form-control" readonly value="{{ $departments->department_code }}" required>
                            </div>
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <select hidden class="form-select" id="company_code" name="company_code" required>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->company_code }}" {{ old('company_code', $bankCashIn->company_code) == $company->company_code ? 'selected' : '' }}>
                                                {{ $company->company_name . ' (' . $company->company_code . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card for {{__('Bank Cash In Details')}} -->
            <div class="card mb-3">
                <div class="card-header">{{__('Bank Cash In Details')}}</div>
                <div class="card-body">
                    <table class="table" id="cash-in-details-table">
                        <thead>
                            <tr>
                                <th>{{__('Account Number')}}</th>
                                <th>Nominal</th>
                                <th>{{__('Notes')}}</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bankCashIn->details as $detail)
                                <tr>
                                    <td>
                                        <div class="form-group mb-3">
                                            <div class="input-group">
                                                <input type="text" id="search-acc-{{ $loop->index }}" autocomplete="off" class="form-control" placeholder="Search by Account Number or Account Name" required readonly value="{{$detail->account_number.' - '.$coas->firstWhere('account_number',$detail->account_number)->account_name}}">
                                                <button style="height:100%;" class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-{{ $loop->index }}')"><i class="material-icons-outlined">edit</i></button>
                                            </div>
                                            <div id="search-result-acc-{{ $loop->index }}" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                                <!-- Search results will be injected here -->
                                            </div>
                                            <input type="hidden" name="details[{{ $loop->index }}][account_number]" id="acc_number_{{ $loop->index }}" value="{{$detail->account_number}}">
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" step="0.01" name="details[{{ $loop->index }}][nominal]" class="form-control text-end nominal" required value="{{ old('details.' . $loop->index . '.nominal', $detail->nominal) }}">
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $loop->index }}][note]" class="form-control" placeholder="..." value="{{ old('details.' . $loop->index . '.note', $detail->note) }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined">delete</i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <td></td>
                            <td><h5 class="text-end">Total: <span id="total-value">{{number_format($bankCashIn->nominal,0,'.',',')}}</span></h5></td>
                            <td colspan="2"></td>
                        </tfoot>
                    </table>
                    <button type="button" id="add-row" class="btn btn-secondary">Tambah Detail</button>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-primary" @if(!in_array('update', $privileges)||!$editable) disabled @endif>Edit Kas Masuk</button>
            </div>
        </form>

        <form id="delete-form" action="{{ route('transaction.bank_cash_in.destroy', $bankCashIn->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('POST')
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $bankCashIn->id }}')"
                @if(!in_array('delete', $privileges)||!$editable) disabled @endif
            ><i class="material-icons-outlined">delete</i></button>
        </form>

    </div>

    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('transaction.bank_cash_in') }}"; // Redirect to list page
                }
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
</div>

@section('scripts')
<script>
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);

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
    setupSearch('search-acc', 'search-result-acc','account_number');
    let rowCount = {{ count($bankCashIn->details) }};
    for (let i = 0; i < rowCount; i++) {
        setupSearch(`search-acc-${i}`, `search-result-acc-${i}`,`acc_number_${i}`);
    }

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
            console.log(total);

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

    document.addEventListener('DOMContentLoaded', function () {
        // let rowCount = {{ count($bankCashIn->details) }};

        // Fungsi untuk menambahkan baris baru
        document.getElementById('add-row').addEventListener('click', function () {
            const detailsTableBody = document.querySelector('#cash-in-details-table tbody');
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
                    <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][nominal]" class="form-control text-end nominal" required placeholder="Nominal">
                </td>
                <td>
                    <input type="text" name="details[${rowCount}][note]" class="form-control"  placeholder="...">
                </td>
                <td>
                    <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined">delete</i></button>
                </td>
            `;
            detailsTableBody.appendChild(newRow);
            setupSearch(`search-acc-${rowCount}`, `search-result-acc-${rowCount}`,`acc_number_${rowCount}`);
            calculateTotals();
            addInputListeners();
            rowCount++;
        });

        // Event delegation untuk menghapus baris
        document.querySelector('#cash-in-details-table tbody').addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
            }
        });
    });

    function confirmDelete(event, id) {
    event.preventDefault(); // Prevent form submission
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#0c6efd',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form').submit(); // Submit the form
        }
    });
    }

    document.getElementById('bank-cash-in-form').addEventListener('submit', function(event) {
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
                document.getElementById('bank-cash-in-form').submit();
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
