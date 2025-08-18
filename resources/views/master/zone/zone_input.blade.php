@extends('layouts.master')

@section('title', 'Input Zone')
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
<x-page-title title="Master" pagetitle="Input Zone" />
<hr>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-2 text-uppercase">Input Zone</h6>
                <form name="zone-form" id="zone-form" method="post" action="{{ url('TDS/master/zone/insert') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label for="zone_code" class="form-label">Kode Zone</label>
                            <input type="text" id="zone_code" name="zone_code" class="form-control" placeholder="Kode Zone" required>
                            <br>
                            <label for="zone_name" class="form-label">Nama Zone</label>
                            <input type="text" id="zone_name" name="zone_name" class="form-control" placeholder="Nama Zone" required>
                            <br>
                            <label for="is_active" class="form-label">Aktif</label>
                            <select class="form-select" id="is_active" name="is_active" required>
                                <option value=1>Aktif</option>
                                <option value=0>Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="mb-2 text-uppercase">Zone Details</h6>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <button type="button" class="btn btn-success" id="add-item-detail">{{__('Add City')}} Detail</button>
                                </div>
                            </div>
                            <table class="table" id="item-details-table">
                                <thead>
                                    <tr>
                                        <th>City</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select id="unit_0" name="zone_details[0][city_code]" class="form-control unit-conversion" onchange="" required>
                                                <option value="" selected disabled>Select City</option>
                                                @foreach ($citys as $city)
                                                    <option value="{{ $city->city_code }}">
                                                        {{ $city->city_name }}
                                                    </option>
                                                @endforeach
                                            </select>
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



        <button type="submit" form="zone-form" class="btn btn-primary mt-3">Submit Zone</button>
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
                        <select id="unit_${rowCount}" name="zone_details[${rowCount}][city_code]" class="form-control unit-conversion" required>
                            <option value="">Select Unit</option>
                            ${getUnitOptions()}
                        </select>
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
            @foreach ($citys as $city)
                options +=`<option value="{{ $city->city_code }}">{{ $city->city_name }}</option>`;
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

        $('#base_unit').on('change', function() {
            const val = this.value;
            console.log(val);

            $('#unit_0').val(val);
        });
        $('#sales_unit').on('change', function() {
            const val = this.value;
            console.log(val);

            $('#unit_1').val(val);
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
