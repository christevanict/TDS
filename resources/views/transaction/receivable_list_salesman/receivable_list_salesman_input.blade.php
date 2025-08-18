@extends('layouts.master')

@section('title',  __('Daftar Tagihan'))
@section('css')
<style>
    #search-result-dest {
        max-height: 200px; /* Set your desired maximum height */
        overflow-y: auto; /* Enable vertical scrolling */
        border: 1px solid #ccc; /* Optional: Add a border */
        background-color: #fff; /* Optional: Set a background color */
        display: none; /* Initially hidden */
    }
</style>
@endsection
@section('content')
<div class="row">
    <x-page-title title="{{__('Daftar Tagihan')}}" pagetitle="{{__('Daftar Tagihan')}} Input" />
    <hr>
    <div class="container content">
        <h2>{{__('Daftar Tagihan')}} Input</h2>

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

        <form id="rl-form" action="{{ isset($receivableList) ? route('transaction.receivable_list_salesman.update', $receivableList->id) : route('transaction.receivable_list_salesman.insert') }}" method="POST">
            @csrf
            @if(isset($receivableList)) @method('PUT') @endif

            <div class="card mb-3">
                <div class="card-header">{{__('Daftar Tagihan')}} {{__('Information')}}</div>
                <div class="card-body">
                    <input type="hidden" id="checkHPP" value="0">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="city_code">{{__('Kode Kota')}}</label>
                                <input type="text" name="city_code" id="city_code" class="form-control" readonly required>
                            </div>
                            <button type="button" class="btn btn-secondary mt-3" id="addRow">{{__('Pilih Kota')}}</button>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_date">{{__('Document Date')}}</label>
                                <input type="date" name="document_date" id="document_date" class="form-control date-picker" required value="{{ old('document_date', $receivableList->document_date ?? date('Y-m-d')) }}">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="department_code">{{__('Department Code')}}</label>
                                <input type="hidden" name="department_code" class="form-control" value="{{$department_TDS}}">
                                <input type="text" name="department_name" id="department_name" class="form-control" value="{{$department_TDSn->department_name}}" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <select hidden class="form-select" id="company_code" name="company_code" required>
                                        @foreach ($companies as $company)
                                            <option value="{{$company->company_code}}">{{$company->company_name.' ('.$company->company_code.')'}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <br>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">{{__('Tanda Terima')}} Details</div>
                <div class="card-body">
                    <h5 class="text-end">Total: <span id="total-value">0</span></h5>
                    <div style="overflow-x: auto;">
                    <table class="table" id="dynamicTable">
                        <thead>
                            <td style="min-width: 220px">Code</td>
                            <td style="min-width: 220px">Nama</td>
                            <td style="min-width: 220px">Tanggal</td>
                            <td style="min-width: 220px">No. Faktur</td>
                            <td style="min-width: 170px">Nilai Faktur</td>
                            <td style="min-width: 170px">Pembayaran</td>
                            <td style="min-width: 170px">Terutang</td>
                            <td>Action</td>
                        </thead>
                        <tbody id="parentTbody">
                            <!-- Parent rows will be added dynamically here -->
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="selectInvoiceModal" tabindex="-1" aria-labelledby="selectInvoiceModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="selectInvoiceModalLabel">Pilih {{__('Tanda Terima')}}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered" id="invoiceTable" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Select</th>
                                        <th>{{__('Kode Kota')}}</th>
                                        <th>{{__('Nama Kota')}}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($citys as $idx => $city)
                                    <tr data-customer-id="{{ $city->city_code }}">
                                        <td style="text-align: center; vertical-align: middle;">
                                            <input type="checkbox" id="invoice-checkbox-{{$idx}}" onChange="siniFuck({{ $idx }})" class="invoice-checkbox" value="{{ $city->city_code }}">
                                        </td>
                                        <td>{{ $city->city_code }}</td>
                                        <td>{{ $city->city_name }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="selectInvoicesButton">Select</button>
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit {{__('Daftar Tagihan')}}</button>
            </div>
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
                    window.location.href = "{{ route('transaction.sales_invoice') }}"; // Redirect to list page
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
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
let dtku;
let valSelected = "";
function siniFuck(idx){
    let checkBox = document.getElementById(`invoice-checkbox-${idx}`);
    if (checkBox.checked == true){
        valSelected += $(`#invoice-checkbox-${idx}`).val() + "|";
    }else{
        xvalSelected = "";
        const myArray = valSelected.split("|");
        myArray.forEach(function (value) {
            if(value !== "" && value !== $(`#invoice-checkbox-${idx}`).val()){
                xvalSelected += value + "|";
            }
        });
        valSelected = xvalSelected;
    }
}

document.addEventListener('DOMContentLoaded', function () {
            // Check if the success message is present
            @if(session('success'))
                // Show SweetAlert confirmation modal
                Swal.fire({
                    title: 'Tanda Terima Created',
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
                            //window.location.href = "{{ route('sales_invoice.print.all', ['id' => ':id']) }}".replace(':id', id);
                        }
                    }
                });
            @endif
        });
let rowCount = 0;
var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);
function calculateTotals() {
    let total = 0;
    document.querySelectorAll('.total').forEach(function (input) {

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

    const customers = @json($customers);
    let customerId='';
    let citys = @json($citys);
    let receivable = @json($receivable);

    $('#selectInvoicesButton').click(function() {

        const selectedRequisition = [];
        const myArray = valSelected.split("|");
        myArray.forEach(function (value) {
            if(value !== ""){
                selectedRequisition.push({
                    requNumber: value
                });
            }
        });

        filteredCitys = citys.filter((so)=>
            selectedRequisition.some(selectedDetail =>
                so.city_code === selectedDetail.requNumber
            )
        );

        filteredCustomer = customers.filter((so)=>
            selectedRequisition.some(selectedDetail =>
                so.city === selectedDetail.requNumber
            )
        );

        filteredReceivable = receivable.filter((so)=>
            filteredCustomer.some(selectedDetail =>
                so.customer_code === selectedDetail.customer_code
            )
        );

        const datas = [];let textCityCode = "";

        filteredCitys.forEach(detail => {
            const a={
                'id':detail.id,
                'city_code':detail.city_code,
                'city_name':detail.city_name,
            }
            textCityCode += detail.city_code + "|";
        });

        filteredReceivable.forEach(detail => {
            const a={
                'id':detail.id,
                'customer_code':detail.customer.customer_code,
                'customer_name':detail.customer.customer_name,
                'document_number':detail.document_number,
                'document_date':detail.document_date,
                'total':detail.total_debt,
                'nominal':detail.debt_balance,
                'paid':detail.total_debt - detail.debt_balance
            }
            datas.push(a);
        });

        $("#city_code").val(textCityCode.substring(0,textCityCode.length-1));
        populateItemModal(datas);
        $('#selectInvoiceModal').modal('hide');
        $('#itemModal').modal('show');
    });

    function dateToString(tgl){
        let bulan = (tgl.getMonth()+1);
        let hari = tgl.getDate();
        if(bulan < 10){
            bulan = "0" + bulan
        }

        if(hari < 10){
            hari = "0" + hari
        }
        return tgl.getFullYear() + "-" + bulan + "-" + hari
    }

    function populateItemModal(items) {
        const tbody = $('#parentTbody');

        tbody.empty();

        items.forEach((item, index) => {
            const newDate = new Date(item.document_date);
            const tgl = dateToString(newDate);
            const row = `
                <tr>
                    <td>
                        <input type="text" id="customer_code_${rowCount}" name="details[${rowCount}][customer_code]" class="form-control" value="${item.customer_code}" readonly />
                    </td>
                    <td>
                        <input type="text" id="customer_name_${rowCount}" name="details[${rowCount}][customer_name]" class="form-control" value="${item.customer_name}" readonly />
                    </td>
                    <td>
                        <input type="date" id="document_date_${rowCount}" name="details[${rowCount}][document_date]" class="form-control date-picker" value="${tgl}" readonly />
                    </td>
                    <td>
                        <input type="text" id="document_number_${rowCount}" name="details[${rowCount}][document_number]" class="form-control" value="${item.document_number}" readonly />
                    </td>
                    <td>
                        <input type="text" id="total_${rowCount}" name="details[${rowCount}][nominal]" class="form-control nominal" value="${item.total.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly />
                    </td>
                    <td>
                        <input type="text" id="paid_${rowCount}" name="details[${rowCount}][paid]" class="form-control nominal" value="${item.paid.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly />
                    </td>
                    <td>
                        <input type="text" id="nominal_${rowCount}" name="details[${rowCount}][nominal_left]" class="form-control nominal total" value="${item.nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')}" readonly />
                    </td>
                    <td id="pay-row-${rowCount}">
                        <button type="button" class="btn btn-danger deleteRow"><i class="material-icons-outlined remove-row">remove</i></button>
                    </td>
                </tr>
            `;
            rowCount++;
            tbody.append(row);
        });
        calculateTotals();
    }

    // Handle item selection from itemModal
    // Replace the existing $('#chooseItem').click handler

    document.getElementById('rl-form').addEventListener('submit', function (e) {
        // Get all rows from the dynamicTable tbody
        const rows = document.querySelectorAll('#dynamicTable tbody tr');

        // Initialize a variable to hold the first warehouse_code value
        let initialWarehouseCode = null;

        // Flag to track if all values are the same
        let allValuesMatch = true;

        // if (rows.length > 10) {
        //     e.preventDefault(); // Prevent form submission
        //     Swal.fire({
        //         title: 'Warning!',
        //         text: 'The maximum number of items is 10.',
        //         icon: 'warning',
        //         confirmButtonText: 'OK'
        //     });
        //     return; // Stop further validation
        // }
    });

    // Initialize row count
    document.addEventListener('DOMContentLoaded', function () {

        // Function to update price based on item and unit selections

        $('#addRow').click(function() {
            $('#selectInvoiceModal').modal('show');
        });

        // Event delegation for row removal
        document.querySelector('#dynamicTable').addEventListener('click', function (e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.closest('tr').remove();
                rowCount--; // Decrement row count
                calculateTotals();
            }
        });

        // Initialize the first row
    });

    let activeIndex = -1;
    $(document).ready(function() {
        dtku = $('#invoiceTable').DataTable();
    });
</script>
@endsection

@endsection
