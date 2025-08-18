@extends('layouts.master')

@section('title', 'Edit Zone')
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
<x-page-title title="Master" pagetitle="Edit Zone" />
<hr>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-2 text-uppercase">Edit Zone</h6>
                <form name="zone-form" id="zone-form" method="post" action="{{ url('TDS/master/zone/update', $zone->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label for="zone_code" class="form-label">Kode Zone</label>
                            <input type="text" id="zone_code" name="zone_code" class="form-control" value = "{{ $zone->zone_code }}" placeholder="Kode Zone" required readonly>
                            <br>
                            <label for="zone_name" class="form-label">Nama Zone</label>
                            <input type="text" id="zone_name" name="zone_name" class="form-control" value = "{{ $zone->zone_name }}" placeholder="Nama Zone" required>
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
                <h6 class="mb-2 text-uppercase">Detail Zone</h6>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <button type="button" class="btn btn-success" id="add-item-detail">Tambah Detail Zone</button>
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
                        @foreach ($zone_details as $detail)
                        <tr>
                            <td>
                                <select class="form-select unit_conversion" name="zone_details[{{$loop->index}}][city_code]" data-original-value="{{ $detail->city_code }}" required >
                                    @foreach ($citys as $city)
                                        <option value="{{ $city->city_code }}" {{ $detail->city_code == $city->city_code ? 'selected' : '' }}>
                                            {{ $city->city_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove-row"><i class="material-icons-outlined"
                                    >delete</i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <button type="submit" form="zone-form" class="btn btn-primary mt-3">Update Zone</button>
    </div>
</div>
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

let editable = true;
let rowCount = {{count($zone_details)}};

if (!editable) {
            for (let i = 0; i < rowCount; i++) {
                document.querySelectorAll(`.unit_conversion[name="item_details[${i}][unit_conversion]"]`).forEach(select => {
                    const originalValue = select.getAttribute('data-original-value');
                    Array.from(select.options).forEach(option => {
                        if (option.value !== originalValue) {
                            option.disabled = true; // Disable all but the original
                        }
                    });
                });
            }
        }


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

    $(document).ready(function() {
        $('#item-details-table').DataTable();
        // Function to add a new row in the item details table
        $('#add-item-detail').click(function() {
            var rowCount = $('#item-details-table tbody tr').length;

            var row = `
                <tr>
                    <td>
                        <select class="form-select unit_conversion" name="zone_details[${rowCount}][city_code]" required>
                            @foreach ($citys as $city)
                                <option value="{{ $city->city_code }}">{{ $city->city_name }}</option>
                            @endforeach
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

        // Remove row from item details table
        $('#item-details-table').on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
        });
    });
</script>
@endsection
