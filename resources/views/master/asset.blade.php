
@extends('layouts.master')

@section('title', 'Master Asset')

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
<x-page-title title="Master" pagetitle="Asset" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Daftar Asset</h6>

        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEditAsset()" data-bs-target="#modalAsset"
        @if(!in_array('create', $privileges)) disabled @endif>
            Tambah Baru
        </button>
        @if(Auth::user()->username=='superadminICT')
        <button type="button" class="mb-3 btn btn-success" data-bs-toggle="modal" data-bs-target="#modalImport">
            Import Data
        </button>
        @endif
        <div class="table-responsive">
            <h6>Daftar Asset</h6>
            <table id="assetTable" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Asset Type</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($assets as $asset)
                        <tr class='clickable-row' onclick="editAsset('{{ $asset->id }}', '{{ $asset->asset_code }}', '{{ $asset->asset_name }}', '{{ $asset->asset_type }}')" data-bs-toggle="modal" data-bs-target="#modalAsset">
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $asset->asset_code }}</td>
                            <td>{{ $asset->asset_name }}</td>
                            <td>{{ $asset->assetType->asset_type_name ?? '' }}</td>

                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editAsset('{{ $asset->id }}', '{{ $asset->asset_code }}', '{{ $asset->asset_name }}', '{{ $asset->asset_type }}')">
                                    <i class="material-icons-outlined">edit</i>
                                </button>
                                <form id="delete-asset-form-{{ $asset->id }}" action="{{ route('master.assets.delete', $asset->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('POST')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteAsset(event, '{{ $asset->id }}')" @if(!in_array('delete', $privileges)) disabled @endif>
                                        <i class="material-icons-outlined">delete</i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>No</th>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Asset Type</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>

            <div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="importForm" method="post" action="{{ route('master.assets.import') }}"
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

        </div>
    </div>
</div>

<!-- Modal for Asset -->
<div class="modal fade" id="modalAsset" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <legend id="legendAssetForm">Input Asset</legend>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="asset-form" id="asset-form" method="post" action="{{ route('master.assets.insert') }}">
                    @csrf
                    <label for="asset_code" class="form-label">Asset Code</label>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" name="asset_code" id="asset_code" placeholder="Asset Code" required>
                    </div>

                    <label for="asset_name" class="form-label">Asset Name</label>
                    <div class="form-group mb-3">
                        <input type="text" class="form-control" name="asset_name" id="asset_name" placeholder="Asset Name" required>
                    </div>

                    <label for="asset_type" class="form-label">Asset Type</label>
                    <div class="form-group mb-3">
                        <select class="form-control" name="asset_type" id="asset_type" required>
                            <option value="">Select Asset Type</option>
                            @foreach ($assetTypes as $type)
                                <option value="{{ $type->asset_type_code }}">{{ $type->asset_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="btn-action-asset" name="btn-action-asset" type="submit" class="btn btn-primary btn-md">Insert</button>
                    <button type="button" class="btn btn-danger" id="cancelButtonAsset" style="display:none;" data-bs-dismiss="modal" onclick="cancelEditAsset()">Cancel</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelEditAsset()">Close</button>
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
@endsection

@section('scripts')
<script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#assetTable').DataTable();
    });

    let privileges = @json($privileges);

    function cancelEditAsset() {
        document.getElementById('asset_code').value = '';
        document.getElementById('asset_name').value = '';
        document.getElementById('asset_type').value = '';
        document.getElementById('company_code').value = '';
        document.getElementById('department_code').value = '';
        document.getElementById('legendAssetForm').innerText = 'Input Asset';
        document.getElementById('cancelButtonAsset').style.display = 'none';
        document.getElementById('btn-action-asset').innerText = 'Insert';
        document.getElementById('asset-form').action = "{{ route('master.assets.insert') }}";
        document.getElementById('btn-action-asset').disabled = !privileges.includes('create');
    }

    function editAsset(id, asset_code, asset_name, asset_type, company_code, department_code) {
        document.getElementById('asset_code').value = asset_code;
        document.getElementById('asset_name').value = asset_name;
        document.getElementById('asset_type').value = asset_type;
        document.getElementById('company_code').value = company_code;
        document.getElementById('department_code').value = department_code;
        document.getElementById('btn-action-asset').innerText = 'Edit';
        document.getElementById('cancelButtonAsset').style.display = 'inline';
        document.getElementById('legendAssetForm').innerText = 'Edit Asset';
        document.getElementById('asset-form').action = `{{ route('master.assets.update', '') }}/${id}`;
        document.getElementById('btn-action-asset').disabled = !privileges.includes('update');

        $('#modalAsset').modal('show');
    }

    function confirmDeleteAsset(event, id) {
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
                document.getElementById('delete-asset-form-' + id).submit();
            }
        });
    }
</script>
@endsection
