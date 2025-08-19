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
                                <th>No</th>
								<th>Kode Tipe Asset</th>
								<th>Nama Tipe Asset</th>
                                <th>Jenis Depresiasi</th>
                                <th>Umur / Rasio Depresiasi</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($assetTypes as $assetType)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($assetType->asset_type_code ?? '') }}',
                                    '{{ addslashes($assetType->asset_type_name ?? '') }}',
                                    '{{ addslashes($assetType->depreciation_code ?? '') }}',
                                    '{{ addslashes($assetType->economic_life ?? '') }}',
                                    '{{ addslashes($assetType->tariff_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_asset ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_akum_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_depreciation ?? '') }}',
                                )">
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$assetType->asset_type_code}}</td>
                                    <td>{{$assetType->asset_type_name}}</td>
                                    <td>{{$assetType->depreciation_code}}</td>
                                    <td>{{$assetType->economic_life}}</td>
                                    <td>

                                    <button class="btn btn-sm btn-warning" onclick="editCategory(
                                    '{{ addslashes($assetType->asset_type_code ?? '') }}',
                                    '{{ addslashes($assetType->asset_type_name ?? '') }}',
                                    '{{ addslashes($assetType->depreciation_code ?? '') }}',
                                    '{{ addslashes($assetType->economic_life ?? '') }}',
                                    '{{ addslashes($assetType->tariff_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_asset ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_akum_depreciation ?? '') }}',
                                    '{{ addslashes($assetType->acc_number_depreciation ?? '') }}',
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
                                <th>No</th>
								<th>Kode Tipe Asset</th>
								<th>Nama Tipe Asset</th>
                                <th>Jenis Depresiasi</th>
                                <th>Umur / Rasio Depresiasi</th>
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
                    <label for="exampleInputEmail1" class="form-label">Kode Tipe Asset</label>
                    <div class="input-group mb-3">
                        <input type="text" id="asset_type_code" name="asset_type_code" class="form-control" placeholder="Kode Tipe Asset" aria-label="asset_type_code" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Nama Tipe Asset</label>
                    <div class="input-group mb-3">
                        <input type="text" id="asset_type_name" name="asset_type_name" class="form-control" placeholder="Nama Tipe Asset" aria-label="asset_type_name" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Metode Depresiasi</label>
                    <div class="input-group mb-3">
                        <select name="depreciation_code" class="form-control" id="depreciation_code">
                            @foreach ($depreciations as $depr)
                                <option value="{{$depr->depreciation_code}}">{{$depr->depreciation_code}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Umur Depresiasi</label>
                    <div class="input-group mb-3">
                        <input type="number" id="economic_life" name="economic_life" class="form-control" placeholder="Umur Depresiasi" aria-label="economic_life" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Tariff Depreciation</label>
                    <div class="input-group mb-3">
                        <input type="number" id="tariff_depreciation" name="tariff_depreciation" class="form-control" placeholder="Tariff Depreciation" aria-label="tariff_depreciation" aria-describedby="basic-addon1">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Akun Aktiva Asset</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-number-asset" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-number-asset')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-number-asset" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_asset" id="acc_number_asset">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Akun Akumulasi Penyusutan</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-akum" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-akum')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-akum" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_akum_depreciation" id="acc_number_akum_depreciation">
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Akun Beban Penyusutan</label>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="text" id="search-acc-depr" class="form-control" placeholder="Search by Account Number or Account Name" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-acc-depr')"><i class="material-icons-outlined">edit</i></button>
                        </div>
                        <div id="search-result-acc-depr" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                        <input type="hidden" name="acc_number_depreciation" id="acc_number_depreciation">
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

        setupSearch('search-acc-number-asset', 'search-result-acc-number-asset','acc_number_asset');
        setupSearch('search-acc-akum', 'search-result-acc-akum','acc_number_akum_depreciation');
        setupSearch('search-acc-depr', 'search-result-acc-depr','acc_number_depreciation');

        function cancelEdit() {
            document.getElementById('asset_type_code').value = '';
            document.getElementById('asset_type_name').value = '';
            document.getElementById('depreciation_code').value = '';
            document.getElementById('economic_life').value = '';
            document.getElementById('tariff_depreciation').value = '';
            document.getElementById('acc_number_asset').value = '';
            document.getElementById('search-acc-number-asset').value ='';
            document.getElementById('search-acc-number-asset').readOnly = false;

            document.getElementById('acc_number_akum_depreciation').value = '';
            document.getElementById('search-acc-akum').value ='';
            document.getElementById('search-acc-akum').readOnly = false;

            document.getElementById('acc_number_depreciation').value = '';
            document.getElementById('search-acc-depr').value ='';
            document.getElementById('search-acc-depr').readOnly = false;

            document.getElementById('legendForm').innerText = 'Asset Type Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('asset-type-form').action = `/TDS/master/asset-type/insert`;
        }


        function editCategory(asset_type_code,asset_type_name,depreciation_code,economic_life,tariff_depreciation,acc_number_asset,acc_number_akum_depreciation,acc_number_depreciation,company_code, department_code) {
            let textDisplay ='';
            document.getElementById('asset_type_code').value = asset_type_code;
            document.getElementById('asset_type_name').value = asset_type_name;
            document.getElementById('depreciation_code').value = depreciation_code;
            document.getElementById('economic_life').value = economic_life;
            document.getElementById('tariff_depreciation').value = tariff_depreciation;
            document.getElementById('acc_number_asset').value = acc_number_asset;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_asset).account_name;
            document.getElementById('search-acc-number-asset').value = acc_number_asset+' - '+textDisplay;
            document.getElementById('search-acc-number-asset').readOnly = true;

            document.getElementById('acc_number_akum_depreciation').value = acc_number_akum_depreciation;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_akum_depreciation).account_name;
            document.getElementById('search-acc-akum').value = acc_number_akum_depreciation+' - '+textDisplay;
            document.getElementById('search-acc-akum').readOnly = true;

            document.getElementById('acc_number_depreciation').value = acc_number_depreciation;
            textDisplay = coas.find((element)=>element.account_number ==acc_number_depreciation).account_name;
            document.getElementById('search-acc-depr').value = acc_number_depreciation+' - '+textDisplay;
            document.getElementById('search-acc-depr').readOnly = true;

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
    </script>
@endsection
