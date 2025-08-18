@extends('layouts.master')

@section('title', 'Master Item Detail')
@section('css')
<style>
    .clickable-row {
        cursor: pointer;
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }
</style>
@endsection

@section('content')
<x-page-title title="Master" pagetitle="Item Detail" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Item Detail</h6>
        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput"
        @if(Auth::user()->role === 1)
            style="display: none"
        @endif
        >
            Tambah Baru
        </button>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Base Unit</th>
                        <th>Conversion</th>
                        <th>Unit Conversion</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemDetails as $itemDetail)
                        <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput" onclick="editItemDetail(
                            '{{ addslashes($itemDetail->id) }}',
                            '{{ addslashes($itemDetail->item_code) }}',
                            '{{ addslashes($itemDetail->base_unit) }}',
                            '{{ addslashes($itemDetail->conversion) }}',
                            '{{ addslashes($itemDetail->unit_conversion) }}',
                            '{{ addslashes($itemDetail->company_code) }}',
                            @if (Auth::user()->role != 1 && Auth::user()->role != 2)
                                true
                            @else
                                false
                            @endif
                        )">
                            <td>{{ $itemDetail->item->item_name ?? '' }} ({{ $itemDetail->item_code }})</td>
                            <td>{{ $itemDetail->baseUnit->unit_name ?? '' }} ({{ $itemDetail->base_unit }})</td>
                            <td>{{ $itemDetail->conversion }}</td>
                            <td>{{ $itemDetail->unitConversion->unit_name ?? '' }} ({{ $itemDetail->unit_conversion }})</td>
                            <td>
                                <button class="btn btn-sm btn-warning"
                                @if(Auth::user()->role != 5 && Auth::user()->role != 7)
                                    style="display: none"
                                @endif
                                onclick="editItemDetail(
                                    '{{ addslashes($itemDetail->id) }}',
                                    '{{ addslashes($itemDetail->item_code) }}',
                                    '{{ addslashes($itemDetail->base_unit) }}',
                                    '{{ addslashes($itemDetail->conversion) }}',
                                    '{{ addslashes($itemDetail->unit_conversion) }}',
                                    '{{ addslashes($itemDetail->company_code) }}',
                                    @if (Auth::user()->role != 1 && Auth::user()->role != 2)
                                        true
                                    @else
                                        false
                                    @endif
                                )"><i class="material-icons-outlined">edit</i></button>

                                <form id="delete-form-{{ $itemDetail->id }}" action="{{ route('master.item-detail.delete', $itemDetail->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(event,'{{ $itemDetail->id }}')"
                                        @if(Auth::user()->role != 5 && Auth::user()->role != 7)
                                            style="display: none"
                                        @endif
                                    ><i class="material-icons-outlined">delete</i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>Item</th>
                        <th>Base Unit</th>
                        <th>Conversion</th>
                        <th>Unit Conversion</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInput" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <legend id="legendForm">Item Detail Insert</legend>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="item-detail-form" id="item-detail-form" method="POST" action="{{ route('master.item-detail.insert') }}">
                    @csrf

                    <label for="item_code" class="form-label">Item</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="item_code" name="item_code" onchange="populateBaseUnit()" required>
                            @foreach ($items as $item)
                                <option value="{{ $item->item_code }}" data-base-unit="{{ $item->base_unit }}">
                                    {{ $item->item_name.' ('.$item->item_code.')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <label for="base_unit" class="form-label">Base Unit</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="base_unit" name="base_unit" required>
                            @foreach ($itemUnits as $itemUnit)
                                <option value="{{ $itemUnit->unit }}">{{ $itemUnit->unit_name.' ('.$itemUnit->unit.')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="conversion" class="form-label">Conversion</label>
                    <div class="input-group mb-3">
                        <input type="number" id="conversion" name="conversion" class="form-control" placeholder="Conversion" aria-label="conversion" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="unit_conversion" class="form-label">Unit Conversion</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="unit_conversion" name="unit_conversion" required>
                            @foreach ($itemUnits as $itemUnit)
                                <option value="{{ $itemUnit->unit }}">{{ $itemUnit->unit_name.' ('.$itemUnit->unit.')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label hidden for="company_code" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}">{{ $company->company_name.' ('.$company->company_code.')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <br>
                    <button id="btn-action" name="btn-action" type="submit" class="btn btn-primary btn-md"
                    @if(Auth::user()->role == 1)
                        style="display: none"
                    @endif
                    >Insert</button>

                    <button type="button" class="btn btn-danger" id="cancelButton" style="display:none;" data-bs-dismiss="modal" onclick="cancelEdit()">Cancel</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelEdit()">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')

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


<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        var table = $('#example').DataTable( {
            lengthChange: false,
            buttons: [ 'copy', 'excel', 'pdf', 'print']
        } );

        table.buttons().container()
            .appendTo( '#example_wrapper .col-md-6:eq(0)' );
    } );
</script>
<script>
    function populateBaseUnit() {
        const selectedItem = document.getElementById('item_code');
        const baseUnit = selectedItem.options[selectedItem.selectedIndex].getAttribute('data-base-unit');
        document.getElementById('base_unit').value = baseUnit ? baseUnit : '';
    }

    function confirmDelete(event, id) {
        event.stopPropagation();
        event.preventDefault();
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
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    function cancelEdit() {
        document.getElementById('item_code').value = '';
        document.getElementById('base_unit').value = '';
        document.getElementById('conversion').value = '';
        document.getElementById('unit_conversion').value = '';
        document.getElementById('legendForm').innerText = 'Item Detail Insert';
        document.getElementById('cancelButton').style.display = 'none';
        document.getElementById('btn-action').innerText = 'Insert';
        document.getElementById('btn-action').disabled = false;
        document.getElementById('item-detail-form').action = `{{ route('master.item-detail.insert') }}`; // Ensure using the route for insert
    }

    function editItemDetail(id, item_code, base_unit, conversion, unit_conversion, company_code, update) {
        if (!update) {
            document.getElementById('btn-action').style.display = 'none';
        } else {
            document.getElementById('btn-action').style.display = 'inline';
        }
        document.getElementById('btn-action').innerText = 'Edit';
        document.getElementById('item_code').value = item_code;
        document.getElementById('base_unit').value = base_unit;
        document.getElementById('conversion').value = conversion;
        document.getElementById('unit_conversion').value = unit_conversion;
        document.getElementById('company_code').value = company_code;
        document.getElementById('cancelButton').style.display = 'inline';
        document.getElementById('legendForm').innerText = 'Item Detail Edit';
        document.getElementById('item-detail-form').action = `/TDS/master/item-detail/edit/${id}`; // Ensure using the route for edit
    }
</script>

@endsection
