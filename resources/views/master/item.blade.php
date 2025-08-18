@extends('layouts.master')

@section('title', 'Master Jasa')
@section('css')
<style>
    .clickable-row {
        cursor: default; /* Changed cursor to default */
    }

    .clickable-row:hover, .clickable-row:focus {
        background-color: #f1f1f1;
    }
</style>
@endsection

@section('content')
<x-page-title title="Master" pagetitle="Jasa" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Jasa</h6>
        <a href="{{ route('item.input') }}" class="btn btn-primary mb-3"
        @if(!in_array('create', $privileges)) disabled @endif
        >
            Tambah Baru
        </a>
        <button type="button" class="mb-3 btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
            Import Data
        </button>
        <button type="button" class="mb-3 btn btn-success d-none" data-bs-toggle="modal" data-bs-target="#modalImport2">
            Import Data 2
        </button>
        <div class="table-responsive">
            <table id="example" class="table table-hover table-bordered mt-2" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode Jasa</th>
                        <th>Nama Jasa</th>
                        <th>Kategori Jasa</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr class='clickable-row'>
                            <td>{{$loop->iteration}}</td>
                            <td>{{$item->item_code}}</td>
                            <td>{{$item->item_name}}</td>
                            <td>{{$item->item_category ? $item->category->item_category_name : ''}}</td>
                            <td>
                                <a href="{{ route('item.show', ['id' => $item->id]) }}" class="btn btn-sm btn-info"
                                    {{-- @if(Auth::user()->role != 3 && Auth::user()->role != 4 && Auth::user()->role != 5&&Auth::user()->role !=6)
                                        style="display: none"
                                    @endif --}}
                                >
                                    <i class="material-icons-outlined">visibility</i>
                                </a>
                                <a href="{{ route('item.edit', ['id' => $item->id]) }}" class="btn btn-sm btn-warning @if(!in_array('update', $privileges)) disabled  @endif"

                                >
                                    <i class="material-icons-outlined">edit</i>
                                </a>
                                <form id="delete-form-{{ $item->item_code }}" action="{{ url('/TDS/master/item/delete/' . $item->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('POST') <!-- Change this to DELETE -->
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete(event,'{{ $item->item_code }}')"
                                        @if(!in_array('delete', $privileges)) disabled @endif
                                    ><i class="material-icons-outlined">delete</i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Kode Jasa</th>
                        <th>Nama Jasa</th>
                        <th>Kategori Jasa</th>

                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" method="post" action="{{ url('/TDS/master/item/import') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <label for="importFile" class="form-label">Choose file to import</label>
                    <input type="file" id="importFile" name="importFile" class="form-control"
                        accept=".csv, .xlsx" required>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalImport2" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import System</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="importForm" method="post" action="{{ url('/TDS/master/item/import-saldo') }}"
                    enctype="multipart/form-data">
                    @csrf
                    <label for="importFile" class="form-label">Choose file to import</label>
                    <input type="file" id="importFile" name="importFile" class="form-control"
                        accept=".csv, .xlsx" required>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInput" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <legend id="legendForm">Jasa Insert</legend>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="item-form" id="item-form" method="post" action="{{ url('master/item/insert') }}">
                    @csrf

                    <label for="item_code" class="form-label">Kode Jasa</label>
                    <div class="input-group mb-3">
                        <input type="text" id="item_code" name="item_code" class="form-control" placeholder="Kode Jasa" aria-label="item_code" aria-describedby="basic-addon1">
                    </div>
                    <label for="item_name" class="form-label">Nama Jasa</label>
                    <div class="input-group mb-3">
                        <input type="text" id="item_name" name="item_name" class="form-control" placeholder="Nama Jasa" aria-label="item_name" aria-describedby="basic-addon1">
                    </div>
                    <label for="item_category" class="form-label">Kategori Jasa</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="item_category" name="item_category">
                            @foreach ($itemCategories as $itemCategory)
                                <option value="{{ $itemCategory->item_category_code }}">{{ $itemCategory->item_category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="base_unit" class="form-label">Base Unit</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="base_unit" name="base_unit">
                            @foreach ($itemUnits as $itemUnit)
                                <option value="{{ $itemUnit->unit }}">{{ $itemUnit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="sales_unit" class="form-label">Sales Unit</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="sales_unit" name="sales_unit">
                            @foreach ($itemUnits as $itemUnit)
                                <option value="{{ $itemUnit->unit }}">{{ $itemUnit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="purchase_unit" class="form-label">Purchase Unit</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="purchase_unit" name="purchase_unit">
                            @foreach ($itemUnits as $itemUnit)
                                <option value="{{ $itemUnit->unit }}">{{ $itemUnit->unit_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="additional_tax" class="form-label">Additional Tax</label>
                    <div class="input-group mb-3">
                        <input type="number" id="additional_tax" name="additional_tax" class="form-control" placeholder="Additional Tax" aria-label="additional_tax" aria-describedby="basic-addon1">
                    </div>
                    <br>
                    <div class="input-group mb-3">
                        <input type="hidden" name="company_code" value={{$companies->company_code}}>
                    </div>
                    <br>
                    <div class="d-flex flex-row">
                        <button id="btn-action" name="btn-action" type="submit" class="btn btn-primary me-2">Insert</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
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
        var table = $('#example').DataTable( {
            lengthChange: false,
            buttons: [ 'copy', 'excel', 'pdf', 'print']
        } );

        table.buttons().container()
            .appendTo( '#example_wrapper .col-md-6:eq(0)' );
    } );
</script>

<script>
    function editItem(item_code, item_name, item_category, base_unit, sales_unit, purchase_unit, additional_tax, company_code, isDisabled) {
        $('#item_code').val(item_code);
        $('#item_name').val(item_name);
        $('#item_category').val(item_category);
        $('#base_unit').val(base_unit);
        $('#sales_unit').val(sales_unit);
        $('#purchase_unit').val(purchase_unit);
        $('#additional_tax').val(additional_tax);
        $('#company_code').val(company_code);

        $('#btn-action').text('Update');
        $('#legendForm').text('Item Update');
        $('#item-form').attr('action', '{{ url("master/item/update") }}');

        if (isDisabled) {
            $('#item_code, #item_name, #base_unit, #sales_unit, #purchase_unit, #additional_tax, #company_code').prop('readonly', true);
            $('#btn-action').hide();
        } else {
            $('#item_code, #item_name, #base_unit, #sales_unit, #purchase_unit, #additional_tax, #company_code').prop('readonly', false);
            $('#btn-action').show();
        }

        $('#modalInput').modal('show');
    }

    function confirmDelete(event, item_code) {
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
                document.getElementById('delete-form-' + item_code).submit();
            }
        });
    }
</script>
@endsection
