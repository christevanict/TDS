@extends('layouts.master')

@section('title', 'Edit Jurnal Umum')

@section('content')
<div class="row">
    <x-page-title title="Edit {{__('General Journal')}}" pagetitle="Edit {{__('General Journal')}}" />
    <hr>
    <div class="container content">
        <h2>Edit {{__('General Journal')}} Input</h2>
        @if (!$editable)
        <h7 style="color: red">Alasan tidak bisa edit</h7>
            <ul>
                <li>Sudah di Closing</li>
            </ul>
        @endif
        <!-- Change form action to update {{__('General Journal')}} -->
        <form id="general-journal-form" action="{{ route('transaction.general_journal.update', $generalJournal->id) }}" method="POST">
            @csrf

            <!-- Card for {{__('General Journal')}} Information -->
            <div class="card mb-3">
                <div class="card-header">{{__('General Journal')}} Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="general_journal_number">{{__('General Journal Number')}}</label>
                                <input type="text" name="general_journal_number" class="form-control" readonly required value="{{ old('general_journal_number', $generalJournal->general_journal_number) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="general_journal_date">{{__('General Journal')}} Date</label>
                                <input type="date" name="general_journal_date" class="form-control date-picker" required value="{{ old('general_journal_date', $generalJournal->general_journal_date) }}" id="document_date">
                            </div>
                            <br>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="note">{{__('Notes')}}</label>
                                <textarea name="note" class="form-control" rows="5">{{ old('note', $generalJournal->note) }}</textarea>
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
                                            <option value="{{ $company->company_code }}" {{ old('company_code', $generalJournal->company_code) == $company->company_code ? 'selected' : '' }}>
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

            <!-- Card for {{__('General Journal')}} Details -->
            <div class="card mb-3">
                <div class="card-header">{{__('General Journal')}} Details</div>
                <div class="card-body">
                    <table class="table" id="cash-out-details-table">
                        <thead>
                            <tr>
                                <th>Akun</th>
                                <th>Debit</th>
                                <th>Credit</th>
                                <th>{{__('Notes')}}</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($generalJournal->details as $detail)
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
                                        <input type="text" oninput="formatNumber(this)" name="details[{{ $loop->index }}][nominal_debet]" class="form-control debit text-end" required value="{{number_format($detail->nominal_debet,0,'.',',') }}">
                                    </td>
                                    <td>
                                        <input type="text" oninput="formatNumber(this)" name="details[{{ $loop->index }}][nominal_credit]" class="form-control credit text-end" required value="{{ number_format($detail->nominal_credit,0,'.',',') }}">
                                    </td>
                                    <td>
                                        <input type="text" name="details[{{ $loop->index }}][note]" class="form-control" placeholder="..." value="{{ old('details.' . $loop->index . '.note', $detail->note) }}">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td><div class="form-group mb-3 text-end">
                                    <strong>Total: <span id="total-debit">0</span></strong><br>
                                </div></td>
                                <td><div class="form-group mb-3 text-end">
                                    <strong>Total: <span id="total-credit">0</span></strong>
                                </div></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    <button type="button" id="add-row" class="btn btn-secondary">Tambah Detail</button>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-primary" @if(!in_array('update', $privileges)||!$editable) disabled @endif>Update {{__('General Journal')}}</button>
            </div>
        </form>

        <!-- Form for deleting the {{__('General Journal')}} entry -->
        <form id="delete-form" action="{{ route('transaction.general_journal.destroy', $generalJournal->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('POST')
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $generalJournal->id }}')"
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
                    window.location.href = "{{ route('transaction.general_journal') }}"; // Redirect to list page
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
    let rowCount = {{ isset($generalJournal) ? count($generalJournal->details) : 1 }};
    for (let i = 0; i < rowCount; i++) {
        setupSearch(`search-acc-${i}`, `search-result-acc-${i}`,`acc_number_${i}`);
    }

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

    document.addEventListener('DOMContentLoaded', function () {
        let rowCount = {{ isset($generalJournal) ? count($generalJournal->details) : 1 }};

        // Function to calculate totals
        function calculateTotals() {

            let totalDebit = 0;
            let totalCredit = 0;
            document.querySelectorAll('.debit').forEach(function (input) {
                totalDebit += parseFloat(input.value.replace(/,/g, '')) || 0;
            });
            document.querySelectorAll('.credit').forEach(function (input) {
                totalCredit += parseFloat(input.value.replace(/,/g, '')) || 0;
            });

            document.getElementById('total-debit').innerText = totalDebit.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            document.getElementById('total-credit').innerText = totalCredit.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

            return { totalDebit, totalCredit }; // Return totals for validation
        }



        // Initial calculation
        calculateTotals();

        // Add event listeners for input changes
        function addInputListeners() {
            document.querySelectorAll('.debit, .credit').forEach(function (input) {
                input.addEventListener('input', function () {
                    calculateTotals(); // Calculate totals when any input changes
                });
            });
        }

        addInputListeners(); // Set up listeners for existing inputs

        document.getElementById('general-journal-form').addEventListener('submit', function (event) {
            event.preventDefault();
            const { totalDebit, totalCredit } = calculateTotals();
            let isValid = true;

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
                    // Check if totals are equal
                    if (totalDebit !== totalCredit) {
                        isValid = false; // Prevent form submission
                        Swal.fire({
                            title: 'Warning!',
                            html: `<ul>
                                The total debits and credits do not match!
                            </ul>`,
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }

                    $('#cash-out-details-table tbody tr').each(function(index) {
                        const debet = $(this).find(`input[name="details[${index}][nominal_debet]"]`).val().replace(/,/g, '');
                        const credit = $(this).find(`input[name="details[${index}][nominal_credit]"]`).val().replace(/,/g, '');

                        if (debet>0 && credit>0) {
                            Swal.fire({
                            title: 'Warning!',
                            html: `<ul>
                                Only one of Nominal Debet or Nominal Credit can be filled for row ${index+1}.
                            </ul>`,
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                            isValid = false;
                        }
                    });
                    if (isValid){
                        document.getElementById('general-journal-form').submit();
                    }
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

        // Add row functionality
        document.getElementById('add-row').addEventListener('click', function () {
            const newRow = `
                <tr>
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
                        <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][nominal_debet]" class="form-control debit text-end" value="0" required placeholder="Debit">
                    </td>
                    <td>
                        <input type="text" oninput="formatNumber(this)" name="details[${rowCount}][nominal_credit]" class="form-control credit text-end" value="0" required placeholder="Credit">
                    </td>
                    <td>
                        <input type="text" name="details[${rowCount}][note]" class="form-control" placeholder="...">
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined remove-row">delete</i></button>
                    </td>
                </tr>
            `;
            document.querySelector('#cash-out-details-table tbody').insertAdjacentHTML('beforeend', newRow);
            setupSearch(`search-acc-${rowCount}`, `search-result-acc-${rowCount}`,`acc_number_${rowCount}`);
            rowCount++;
            addInputListeners(); // Re-apply listeners to include new inputs
            calculateTotals(); // Recalculate totals after adding a row
        });

        // Remove row functionality
        document.querySelector('#cash-out-details-table tbody').addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-row')) {
                event.target.closest('tr').remove();
                calculateTotals(); // Recalculate totals after removing a row
            }
        });
    });
    function confirmDelete(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Are you sure?',
            text: 'Do you want to delete this general journal?',
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
</script>
@endsection

@endsection
