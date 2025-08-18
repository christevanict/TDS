@extends('layouts.master')

@section('title', 'Edit Jasa')
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
<x-page-title title="Master" pagetitle="Edit Jasa" />
<hr>

<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-2 text-uppercase">Edit Item</h6>
                <form name="item-form" id="item-form" method="post" action="{{ url('TDS/master/item/update', $item->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6">
                            <label for="item_code" class="form-label">Kode Jasa</label>
                            <input type="text" id="item_code" name="item_code" class="form-control" value = "{{ $item->item_code }}" placeholder="Kode Jasa" required readonly>
                            <label for="item_name" class="form-label">Nama Jasa</label>
                            <input type="text" id="item_name" name="item_name" class="form-control" value = "{{ $item->item_name }}" placeholder="Nama Jasa" required>


                            <br>
                            <label for="base_unit" class="form-label">Unit Default</label>
                            <select class="form-select" id="base_unit" name="base_unit" required>
                                <option value="" disabled selected>Select Unit Default</option>
                                @foreach ($itemUnits as $itemUnit)
                                    <option value = "{{ $itemUnit->unit }}" {{ old('base_unit', $item->base_unit) == $itemUnit->unit ? 'selected' : '' }}>{{ $itemUnit->unit_name }}</option>
                                @endforeach
                            </select>
                            <br>
                            <label for="sales_unit" class="form-label">Unit Jual</label>
                            <select class="form-select" id="sales_unit" name="sales_unit" required>
                                <option value="" disabled selected>Select Unit Jual</option>
                                @foreach ($itemUnits as $itemUnit)
                                    <option value="{{ $itemUnit->unit }}" {{ old('sales_unit', $item->sales_unit) == $itemUnit->unit ? 'selected' : '' }}>{{ $itemUnit->unit_name }}</option>
                                @endforeach
                            </select>
                            <br>
                            <label for="purchase_unit" class="form-label">Unit Beli</label>
                            <select class="form-select" id="purchase_unit" name="purchase_unit" required>
                                <option value="" disabled selected>Select Unit Beli</option>
                                @foreach ($itemUnits as $itemUnit)
                                    <option value="{{ $itemUnit->unit }}" {{ old('purchase_unit', $item->purchase_unit) == $itemUnit->unit ? 'selected' : '' }}>{{ $itemUnit->unit_name }}</option>
                                @endforeach
                            </select>

                        </div>
                        <div class="col-md-6">
                            <label for="item_category" class="form-label">Kategori Jasa</label>
                            <select class="form-select" id="item_category" name="item_category" required>
                                <option value="" disabled selected>Select Category</option>
                                @foreach ($itemCategories as $itemCategory)
                                    <option value="{{ $itemCategory->item_category_code }}" {{ old('item_category', $item->item_category) == $itemCategory->item_category_code ? 'selected' : '' }}>{{ $itemCategory->item_category_name }}</option>
                                @endforeach
                            </select>
                            <br>
                            <label for="additional_tax" class="form-label">Additional Tax</label>
                            <div class="form-group">
                                <input type="radio" name="additional_tax" value="yes" id="additional_tax_yes"  {{ old('additional_tax', $item->additional_tax) == true ? 'checked' : '' }} >
                                <label for="additional_tax_yes">Yes</label><br>

                                <input type="radio" name="additional_tax" value="no" id="additional_tax_no" {{ old('additional_tax', $item->additional_tax) == false ? 'checked' : '' }} >
                                <label for="additional_tax_no">No</label><br>
                            </div>
                            {{-- <label for="exampleInputEmail1" class="form-label">Include</label>
                        <div class="form-group">
                            <input type="radio" name="include" value="yes" id="include_yes"  {{ old('include', $item->include) == true ? 'checked' : '' }} required>
                            <label for="include_yes">Yes</label><br>

                            <input type="radio" name="include" value="no" id="include_no" {{ old('include', $item->include) == false ? 'checked' : '' }}>
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
                <h6 class="mb-2 text-uppercase">Detail Jasa</h6>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <button type="button" class="btn btn-success" id="add-item-detail">Tambah Detail Jasa</button>
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
                        @foreach ($itemDetails as $detail)
                        <tr>
                            <td>
                                <input type="text" oninput="handleDecimalInput(this)" name="item_details[{{$loop->index}}][conversion]" class="form-control" value="{{ $detail->conversion }}" required @if(!$editable) readonly @endif>
                            </td>
                            <td>
                                <select class="form-select unit_conversion" name="item_details[{{$loop->index}}][unit_conversion]" data-original-value="{{ $detail->unit_conversion }}" required >
                                    @foreach ($itemUnits as $itemUnit)
                                        <option value="{{ $itemUnit->unit }}" {{ $detail->unit_conversion == $itemUnit->unit ? 'selected' : '' }}>
                                            {{ $itemUnit->unit_name }}
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="item_details[{{$loop->index}}][barcode]" class="form-control" value="{{ $detail->barcode }}">
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="item_details[{{$loop->index}}][stat]" role="switch" id="status" @if($detail->status) checked @endif>
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove-row @if(!$editable) disabled @endif"><i class="material-icons-outlined"
                                    >delete</i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <input type="hidden" name="hidden_detail_item_code" id="hidden_detail_item_code" value="{{ $item->item_code }}">
                <input type="hidden" name="hidden_detail_unit" id="hidden_detail_unit" value="">
            </div>
        </div>

        <button type="submit" form="item-form" class="btn btn-primary mt-3">Update Item</button>
    </div>
</div>
        </form>

        <form id="delete-form" action="{{ route('item.delete', $item->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('POST')
            <input type="hidden" id="item_code" name="item_code" class="form-control" value = "{{ $item->item_code }}" placeholder="Kode Jasa" required>
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event,'{{ $item->id }}')"
                @if(Auth::user()->role != 4 && Auth::user()->role != 5&&Auth::user()->role !=7)
                    style="display: none"
                @endif
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

let editable = @json($editable);
let rowCount = {{count($itemDetails)}};

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
            console.log(rowCount);

            var row = `
                <tr>
                    <td>
                        <input type="text" oninput="handleDecimalInput(this)" name="item_details[${rowCount}][conversion]" class="form-control" placeholder="Conversion" required>
                    </td>
                    <td>
                        <select class="form-select unit_conversion" name="item_details[${rowCount}][unit_conversion]" required>
                            @foreach ($itemUnits as $itemUnit)
                                <option value="{{ $itemUnit->unit }}">{{ $itemUnit->unit_name }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="item_details[${rowCount}][barcode]" class="form-control" placeholder="Enter Barcode">
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
