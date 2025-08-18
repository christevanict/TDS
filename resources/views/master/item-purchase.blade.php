@extends('layouts.master')

@section('title', 'Master Harga Beli Barang')
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
<x-page-title title="Master" pagetitle="Harga Beli Barang" />
		<hr>
		<div class="card">
			<div class="card-body">
                <h6 class="mb-2 text-uppercase">Harga Beli Barang</h6>
                <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEdit()" data-bs-target="#modalInput" @if(!in_array('create', $privileges)) disabled @endif>
                    Tambah Baru
                </button>
				<div class="table-responsive">
					<table id="example" class="table table-hover table-bordered" style="width:100%">
						<thead>
							<tr>
                                <th>No</th>
								<th>Barang</th>
                                <th>Unit</th>
                                <th>{{__('Price')}} Beli</th>
                                <th>{{__('Supplier')}}</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
                            @foreach ($itemPurchases as $itemPurchase)
                                <tr class='clickable-row' data-bs-toggle="modal" data-bs-target="#modalInput"  onclick="editCategory(
                                    '{{ addslashes($itemPurchase->id ?? '') }}',
                                    '{{ addslashes($itemPurchase->barcode ?? '') }}',
                                    '{{ addslashes($itemPurchase->item_code ?? '') }}',
                                    '{{ addslashes($itemPurchase->purchase_price ?? '') }}',
                                    '{{ addslashes($itemPurchase->unit ?? '') }}',
                                    '{{ addslashes($itemPurchase->supplier ?? '') }}',
                                    '{{ addslashes($itemPurchase->company_code ?? '') }}',
                                )">
                                    <td>{{ $loop->iteration}}</td>
                                    <td>{{ $itemPurchase->items->item_name ?? '' }} </td>
                                    <td>{{$itemPurchase->unit ?? '' }}</td>
                                    <td>{{$itemPurchase->purchase_price}}</td>
                                    <td>{{$itemPurchase->suppliers->supplier_name??''}} </td>
                                    <td>

                                    <button class="btn btn-sm btn-warning" onclick="editCategory(
                                    '{{ addslashes($itemPurchase->id ?? '') }}',
                                    '{{ addslashes($itemPurchase->barcode ?? '') }}',
                                    '{{ addslashes($itemPurchase->item_code ?? '') }}',
                                    '{{ addslashes($itemPurchase->purchase_price ?? '') }}',
                                    '{{ addslashes($itemPurchase->unit ?? '') }}',
                                    '{{ addslashes($itemPurchase->supplier ?? '') }}',
                                    '{{ addslashes($itemPurchase->company_code ?? '') }}',
                                        )"><i class="material-icons-outlined">edit</i></button>
                                        <form id="delete-form-{{ $itemPurchase->barcode}}-{{ $itemPurchase->supplier}}" action="{{ url('/TDS/master/item-purchase/delete/' . $itemPurchase->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('POST')
                                            <button type="button" class="btn btn-sm btn-danger"  onclick="confirmDelete(event,'{{ $itemPurchase->barcode }}','{{ $itemPurchase->supplier }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

						<tfoot>
							<tr>
                                <th>No</th>
								<th>Barang</th>
                                <th>Unit</th>
                                <th>{{__('Price')}} Beli</th>
                                <th>{{__('Supplier')}}</th>
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
                  <h5 class="modal-title" id="legendForm">Input Harga Beli Barang  </h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form name="itemPurchase-form" id="itemPurchase-form" method="post" action="{{url('/TDS/master/item-purchase/insert')}}">
                        @csrf
                    <label for="exampleInputEmail1" class="form-label">Barang</label>
                    <div class="form-group mb-3">
                        <input type="hidden" class="form-control item-input" name="item_code" id="item_code" >
                        <input type="text" class="form-control item-input" name="item_name" id="item_name" placeholder="{{__('Search Item')}}" autocomplete="off">
                        <div id="item-search-results" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:300px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">Harga Beli</label>
                    <div class="input-group mb-3">
                        <input type="number" id="purchase_price" name="purchase_price" class="form-control" placeholder="Harga Beli" aria-label="Harga Beli" aria-describedby="basic-addon1" required>
                    </div>
                    <label for="exampleInputEmail1" class="form-label">{{__('Supplier')}}</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="supplier" name="supplier" required>
                            @foreach ($suppliers as $supplier)
                                <option value={{$supplier->supplier_code}}>{{$supplier->supplier_name.' ('.$supplier->supplier_code.')'}}</option>
                            @endforeach
                        </select>
                    </div>
                    <label hidden for="exampleInputEmail1" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value={{$company->company_code}}>{{ $company->company_name. ' (' . $company->company_code. ')'}}</option>
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
    <!-- In your <head> -->

	<script>
		$(document).ready(function() {
			var table = $('#example').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
		} );
        document.getElementById('supplier').value = '';



        function confirmDelete(event,id,supplier) {
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
                    document.getElementById('delete-form-' + id+'-'+supplier).submit();
                }
            })
        }
        let privileges = @json($privileges);
        function cancelEdit() {
            document.getElementById('barcode').value = '';
            document.getElementById('purchase_price').value = '';
            document.getElementById('supplier').value = '';
            document.getElementById('legendForm').innerText = 'Harga Beli Barang Insert';
            document.getElementById('cancelButton').style.display = 'none';
            document.getElementById('btn-action').innerText = 'Insert';
            document.getElementById('itemPurchase-form').action = `/TDS/master/item-purchase/insert`;
            if(!privileges.includes('create')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }


        function editCategory(id, barcode,item_code,purchase_price,unit,supplier,company_code) {
            document.getElementById('barcode').value = barcode;
            document.getElementById('purchase_price').value = purchase_price;
            document.getElementById('supplier').value = supplier;
            document.getElementById('company_code').value = company_code;
            document.getElementById('legendForm').innerText = 'Harga Beli Barang Update';
            document.getElementById('cancelButton').style.display = 'inline-block';
            document.getElementById('btn-action').innerText = 'Edit';
            document.getElementById('itemPurchase-form').action = `/TDS/master/item-purchase/edit/${id}`;
            if(!privileges.includes('update')){
                document.getElementById('btn-action').disabled =true
            }else{
                document.getElementById('btn-action').disabled =false
            }
        }

        let activeIndex = -1;
        let items = @json($items);
        function setupItemSearch() {
            // Track the active item in the dropdown
            const searchInput = document.getElementById(`item_name`);
            const resultsContainer = document.getElementById(`item-search-results`);

            // Input event listener for filtering
            searchInput.addEventListener('input', function() {
                activeIndex = -1; // Reset active index on new input
                let query = this.value.toLowerCase();
                resultsContainer.innerHTML = '';
                resultsContainer.style.display = 'none';

                if (query.length > 0) {
                    let filteredItems = items.filter(item =>
                        item.item_code.toLowerCase().includes(query) ||
                        item.item_name.toLowerCase().includes(query)
                    );

                    if (filteredItems.length > 0) {
                        resultsContainer.style.display = 'block';
                        filteredItems.forEach((item, index) => {
                            let listItem = document.createElement('a');
                            listItem.className = 'list-group-item list-group-item-action';
                            listItem.href = '#';
                            listItem.innerHTML = `
                                <small><strong>${item.item_name}</strong> (${item.item_code})</small>
                            `;

                            listItem.addEventListener('click', function(e) {
                                e.preventDefault();
                                selectItem(item);
                            });

                            resultsContainer.appendChild(listItem);
                        });
                    }
                }
            });

            // Keydown event listener for navigation
            searchInput.addEventListener('keydown', function(e) {
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
        setupItemSearch();

        // Helper function to update active item
        function updateActiveItem(items) {
            items.forEach((item, index) => {
                item.classList.toggle('active', index === activeIndex);
            });
            if (activeIndex >= 0) {
                items[activeIndex].scrollIntoView({ block: 'nearest' });
            }
        }

        // Helper function to handle item selection
        function selectItem(item) {
            document.getElementById('item_code').value = item.item_code;
            document.getElementById('item_name').value = item.item_name;
            document.getElementById(`item-search-results`).style.display = 'none';
        }
    </script>
@endsection
