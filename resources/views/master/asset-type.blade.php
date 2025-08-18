@extends('layouts.master')

@section('title', 'Master Asset Type')
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
<x-page-title title="Master" pagetitle="Asset Type" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Asset Type</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput">
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
								<th>Asset Type Code</th>
								<th>Asset Type Name</th>
                                <th>Economic Life</th>
                                <th>Tariff Depreciation</th>
								<th>Company</th>
                                <th>{{__('Department')}}</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($assetTypes as $assetType)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($assetType->asset_type_code ?? '') }}',
                                    '{{ addslashes($assetType->asset_type_name ?? '') }}',
                                    '{{ addslashes($assetType->economic_life ?? '') }}',
                                    '{{ addslashes($assetType->tariff_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_asset ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_akum_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->company_code ?? '') }}',
                                    '{{ addslashes($assetType->department_code ?? '') }}'
                                )">
                                    <td>{{$assetType->asset_type_code}}</td>
                                    <td>{{$assetType->asset_type_name}}</td>
                                    <td>{{$assetType->economic_life}}</td>
                                    <td>{{$assetType->tariff_depreciation}}</td>
                                    <td>{{$assetType->company ? $assetType->company->company_name:''}}</td>
                                    <td>{{$assetType->department ? $assetType->department->department_name:''}}</td>
                                    <td>

                                    <button class="btn btn-sm btn-warning" onclick="editCategory(
                                    '{{ addslashes($assetType->asset_type_code ?? '') }}',
                                    '{{ addslashes($assetType->asset_type_name ?? '') }}',
                                    '{{ addslashes($assetType->economic_life ?? '') }}',
                                    '{{ addslashes($assetType->tariff_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_asset ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_akum_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->company_code ?? '') }}',
                                    '{{ addslashes($assetType->department_code ?? '') }}'
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="delete-form-{{ $assetType->asset_type_code}}" action="{{ url('/TDS/master/asset-type/delete/' . $assetType->asset_type_code) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $assetType->asset_type_code }}')"><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
								<th>Asset Type Code</th>
								<th>Asset Type Name</th>
                                <th>Economic Life</th>
                                <th>Tariff Depreciation</th>
								<th>Company</th>
                                <th>{{__('Department')}}</th>
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
                  <h5 class="modal-title" id="legendForm">Asset Type  Insert</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="asset-type-form" id="asset-type-form" method="post" action="{{url('/TDS/master/asset-type/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Asset Type Code</label>
                    <div class="input-group mb-3">
                        <input type="text" id="asset_type_code" name="asset_type_code" class="form-control" placeholder="Asset Type Code" aria-label="asset_type_code" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Asset Type Name</label>
                    <div class="input-group mb-3">
                        <input type="text" id="asset_type_name" name="asset_type_name" class="form-control" placeholder="Asset Type Name" aria-label="asset_type_name" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Economic Life</label>
                    <div class="input-group mb-3">
                        <input type="number" id="economic_life" name="economic_life" class="form-control" placeholder="Economic Life" aria-label="economic_life" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Tariff Depreciation</label>
                    <div class="input-group mb-3">
                        <input type="number" id="tariff_depreciation" name="tariff_depreciation" class="form-control" placeholder="Tariff Depreciation" aria-label="tariff_depreciation" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="company_code" name="company_code">
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{$company->company_code.' '.$company->company_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Acc Number Asset</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="acc_number_asset" name="acc_number_asset">
                            @foreach ($coas as $coa)
                                <option data-company="{{$coa->company_code}}" value={{$coa->account_number}}>{{$coa->account_number.' '.$coa->account_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Acc Number Akum Depreciation</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="acc_number_akum_depreciation" name="acc_number_akum_depreciation">
                            @foreach ($coas as $coa)
                                <option data-company="{{$coa->company_code}}" value={{$coa->account_number}}>{{$coa->account_number.' '.$coa->account_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Acc Number Depreciation</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="acc_number_depreciation" name="acc_number_depreciation">
                            @foreach ($coas as $coa)
                                <option data-company="{{$coa->company_code}}" value={{$coa->account_number}}>{{$coa->account_number.' '.$coa->account_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Department')}}</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="department_code" name="department_code">
                            @foreach ($departments as $department)
                                <option value={{$department->department_code}}>{{$department->department_code.' '.$department->department_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="btn-action" name="btn-action" type="submit" class="btn btn-primary btn-md">Insert</button>
                    <button type="button" class="btn btn-danger" id="cancelButton" style="display:none;" data-bs-dismiss="modal" onclick="cancelEdit()">Cancel</button>
                </form>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelEdit()">Close</button>
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
			var table = $('#example').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
		} );
	</script>
    <script>
        function confirmDelete(event,id) {
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
            })
        }

        function cancelEdit() {
            document.getElementById('asset_type_code').value = '';
            document.getElementById('asset_type_name').value = '';
            document.getElementById('economic_life').value = '';
            document.getElementById('tariff_depreciation').value = '';
            document.getElementById('acc_number_asset').value = '';
            document.getElementById('acc_number_akum_depreciation').value = '';
            document.getElementById('acc_number_depreciation').value = '';
            document.getElementById('company_code').value = '';
            document.getElementById('department_code').value = '';
            document.getElementById('legendForm').innerText = 'Asset Type Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('asset-type-form').action = `/TDS/master/asset-type/insert`;
        }


        function editCategory(asset_type_code,asset_type_name,economic_life,tariff_depreciation,acc_number_asset,acc_number_akum_depreciation,acc_number_depreciation,company_code, department_code) {
            document.getElementById('asset_type_code').value = asset_type_code;
            document.getElementById('asset_type_name').value = asset_type_name;
            document.getElementById('economic_life').value = economic_life;
            document.getElementById('tariff_depreciation').value = tariff_depreciation;
            document.getElementById('acc_number_asset').value = acc_number_asset;
            document.getElementById('acc_number_akum_depreciation').value = acc_number_akum_depreciation;
            document.getElementById('acc_number_depreciation').value = acc_number_depreciation;
            document.getElementById('company_code').value = company_code;
            document.getElementById('department_code').value = department_code;
            document.getElementById('legendForm').innerText = 'Asset Type Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('asset-type-form').action = `/TDS/master/asset-type/edit/${asset_type_code}`;
        }
    </script>
    <script>
        // Get the company and account_payable select elements
        var companySelect = document.getElementById('company_code');
        var economicLife = document.getElementById('economic_life');
        var tariffDepreciation = document.getElementById('tariff_depreciation');
        var accNumberAsset = document.getElementById('acc_number_asset');
        var accNumberAkumDepreciation = document.getElementById('acc_number_akum_depreciation');
        var accNumberDepreciation = document.getElementById('acc_number_depreciation');

        // Add event listener for when the company is selected
        companySelect.addEventListener('change', function() {
            var selectedCompany = this.value;
            economicLife.value = '';
            tariffDepreciation.value = '';
            accNumberAsset.value = '';
            accNumberAkumDepreciation.value = '';
            accNumberDepreciation.value = '';
            for (var i = 0; i < accNumberAsset.options.length; i++) {
                var optionNumAss = accNumberAsset.options[i];
                var optionNumAk = accNumberAkumDepreciation.options[i];
                var optionNumDe = accNumberDepreciation.options[i];
                var coaCompanyCode = optionNumAss.getAttribute('data-company');
                if (selectedCompany === "" || coaCompanyCode === selectedCompany) {
                    // optionEco.style.display = 'block';
                    // optionTarr.style.display = 'block';
                    optionNumAss.style.display = 'block';
                    optionNumAk.style.display = 'block';
                    optionNumDe.style.display = 'block';
                } else {
                    // optionEco.style.display = 'none';
                    // optionTarr.style.display = 'none';
                    optionNumAss.style.display = 'none';
                    optionNumAk.style.display = 'none';
                    optionNumDe.style.display = 'none';
                }
            }
            accNumberAsset.value = '';
        });
    </script>
@endsection
