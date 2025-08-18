@extends('layouts.master')

@section('title', 'Input Jasa')
@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }

    .form-group {
        margin-bottom: 1rem; /* Adjust the spacing */
    }
</style>
@endsection

@section('content')
<x-page-title title="Master" pagetitle="Input Jasa" />
<hr>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-2 text-uppercase">Input Jasa</h6>
                <form name="item-form" id="item-form" method="post" action="{{ url('TDS/master/item/insert') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="item_code" class="form-label">Kode Jasa</label>
                            <input type="text" id="item_code" name="item_code" class="form-control" placeholder="Kode Jasa" required>
                            <br>
                            <label for="item_name" class="form-label">Nama Jasa</label>
                            <input type="text" id="item_name" name="item_name" class="form-control" placeholder="Nama Jasa" required>


                            <br>
                            <label for="base_unit" class="form-label">Unit Default</label>
                            <select class="form-select" id="base_unit" name="base_unit" required>
                                <option value="" disabled selected>Select Unit Default</option>
                                @foreach ($itemUnits as $itemUnit)
                                    <option value="{{ $itemUnit->unit }}" selected>{{ $itemUnit->unit_name }}</option>
                                @endforeach
                            </select>
                            <br>
                            <label for="sales_unit" class="form-label">Unit Jual</label>
                            <select class="form-select" id="sales_unit" name="sales_unit" required>
                                <option value="" disabled selected>Select Unit Jual</option>
                                @foreach ($itemUnits as $itemUnit)
                                    <option value="{{ $itemUnit->unit }}" selected>{{ $itemUnit->unit_name }}</option>
                                @endforeach
                            </select>
                            <br>
                            <label for="purchase_unit" class="form-label">Unit Beli</label>
                            <select class="form-select" id="purchase_unit" name="purchase_unit" required>
                                <option value="" disabled selected>Select Unit Beli</option>
                                @foreach ($itemUnits as $itemUnit)
                                    <option value="{{ $itemUnit->unit }}" selected>{{ $itemUnit->unit_name }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label for="item_category" class="form-label">Kategori Jasa</label>
                            <select class="form-select" id="item_category" name="item_category" required>
                                @foreach ($itemCategories as $itemCategory)
                                    <option value="{{ $itemCategory->item_category_code }}" selected>{{ $itemCategory->item_category_name }}</option>
                                @endforeach
                            </select>
                            <br>
                            <label for="additional_tax" class="form-label">Additional Tax</label>
                            <div class="form-group">
                                <input type="radio" name="additional_tax" value="yes" id="additional_tax_yes" required>
                                <label for="additional_tax_yes">Yes</label><br>

                                <input type="radio" name="additional_tax" value="no" id="additional_tax_no" checked>
                                <label for="additional_tax_no">No</label><br>
                            </div>
                            {{-- <label for="exampleInputEmail1" class="form-label">Include</label>
                        <div class="form-group">
                            <input type="radio" name="include" value="yes" id="include_yes" required>
                            <label for="include_yes">Yes</label><br>

                            <input type="radio" name="include" value="no" id="include_no">
                            <label for="include_no">No</label><br>
                        </div> --}}
                        </div>
                    <br>
                        <div class="input-group mb-3">
                            <input type="hidden" name="company_code" value={{$companies->company_code}}>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="mb-2 text-uppercase">Jasa Details</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <button type="button" class="btn btn-success d-none" id="add-item-detail">{{__('Add Item')}} Detail</button>
                                </div>
                            </div>
                            <table class="table" id="item-details-table">
                                <thead>
                                    <tr>
                                        <th>Konversi</th>
                                        <th>Unit Konversi</th>
                                        <th>Status Aktif</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" oninput="handleDecimalInput(this)" name="item_details[0][conversion]" class="form-control conversion" min="1" value="1" required readonly>
                                        </td>
                                        <td>
                                            <select id="unit_0" name="item_details[0][unit_conversion]" class="form-control unit-conversion" onchange="" required>
                                                <option value="" selected disabled>Select Unit Conversion</option>
                                                @foreach ($itemUnits as $itemUnit)
                                                    <option value="{{ $itemUnit->unit }}" selected>
                                                        {{ $itemUnit->unit_name }} ({{ $itemUnit->unit }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="item_details[0][stat]" role="switch" id="status"  checked >
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger remove-row disabled"><i class="material-icons-outlined"
                                                >delete</i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </form>
            </div>
        </div>



        <button type="submit" form="item-form" class="btn btn-primary mt-3">Submit Item</button>
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
@if ($errors->any())
<script>
let errorMessages = 'The ';
@foreach ($errors->all() as $error)
    errorMessages += '- {{ $error }}';
@endforeach
errorMessages += '- is required';

Swal.fire({
    title: 'Validation Error!',
    text: errorMessages,
    icon: 'error',
    confirmButtonText: 'OK'
});
</script>
@endif
@endsection

@section('scripts')
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        let selectedUnits = []; // Array to keep track of selected units

        function handleDecimalInput(input) {
            // Replace comma with period
            let value = input.value.replace(',', '.');

            // Allow only numbers and one decimal point
            value = value.replace(/[^0-9.]/g, '');

            // Ensure only one decimal point
            const decimalCount = value.split('.').length - 1;
            if (decimalCount > 1) {
                const parts = value.split('.');
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            // Update the input value
            input.value = value;
        }

        // Function to add a new row in the item details table
        $('#add-item-detail').click(function() {
            var rowCount = $('#item-details-table tbody tr').length;
            var row = `
                <tr>
                    <td>
                        <input type="text" oninput="handleDecimalInput(this)" name="item_details[${rowCount}][conversion]" class="form-control conversion" min="1" value="1" required>
                    </td>
                    <td>
                        <select id="unit_${rowCount}" name="item_details[${rowCount}][unit_conversion]" class="form-control unit-conversion" required>
                            <option value="">Select Unit</option>
                            ${getUnitOptions()}
                        </select>
                    </td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="item_details[${rowCount}][stat]" role="switch" id="status"  checked >
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined"
                                                >delete</i></button>
                    </td>
                </tr>
            `;
            $('#item-details-table tbody').append(row);
        });

        // Function to get unit options, excluding selected units
        function getUnitOptions() {
            let options = '';
            @foreach ($itemUnits as $itemUnit)
                options += `<option value="{{ $itemUnit->unit }}">{{ $itemUnit->unit_name }} ({{ $itemUnit->unit }})</option>`;
            @endforeach

            // Exclude selected units from options
            selectedUnits.forEach(unit => {
                options = options.replace(new RegExp(`<option value="${unit}">.*?</option>`), '');
            });

            return options;
        }

        // Update selected units when a dropdown changes
        $('#item-details-table').on('change', '.unit-conversion', function() {
            var selectedUnit = $(this).val();
            if (selectedUnit) {
                selectedUnits.push(selectedUnit); // Add the selected unit to the array
            }
        });

        // Remove row from item details table
        $('#item-details-table').on('click', '.remove-row', function() {
            const row = $(this).closest('tr');
            const selectedUnit = row.find('.unit-conversion').val();

            // Remove the selected unit from the array
            selectedUnits = selectedUnits.filter(unit => unit !== selectedUnit);

            row.remove();
        });
    });
</script>


@endsection
