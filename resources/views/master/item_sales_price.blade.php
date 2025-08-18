@extends('layouts.master')

@section('title', 'Master Daftar Harga Jual')
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
<x-page-title title="Master" pagetitle="Daftar Harga Jual" />
<hr>
<div class="card">
    <div class="card-body">
        <h6 class="mb-2 text-uppercase">Daftar Harga Jual</h6>

        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" onclick="cancelEditSalesPrice()" data-bs-target="#modalSalesPrice"
        @if(!in_array('create', $privileges)) disabled @endif>
            Tambah Baru
        </button>

        <div class="table-responsive">
            <h6>Daftar Harga Jual</h6>
            <table id="salesPriceTable" class="table table-hover table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barang</th>
                        <th>Unit</th>
                        <th>Harga Jual</th>
                        {{-- <th>Category Customer</th> --}}
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemSalesPrices as $salesPrice)
                        <tr class='clickable-row' onclick="editSalesPrice('{{ $salesPrice->id }}', '{{ $salesPrice->barcode }}', '{{ $salesPrice->sales_price }}', '{{ $salesPrice->company_code }}', '{{ $salesPrice->unit }}','{{ $salesPrice->category_customer }}' )" data-bs-toggle="modal" data-bs-target="#modalSalesPrice">
                            <td>{{ $loop->iteration}}</td>
                            <td>{{ $salesPrice->items->item_name ?? '' }}</td>
                            <td>{{ $salesPrice->unit ?? '' }}</td>
                            <td>{{ $salesPrice->sales_price }}</td>
                            {{-- <td>{{ $salesPrice->CategoryCustomer->category_name }}</td> --}}
                            <td>
                                <button class="btn btn-sm btn-warning"      onclick="editSalesPrice('{{ $salesPrice->id }}', '{{ $salesPrice->barcode }}', '{{ $salesPrice->sales_price }}', '{{ $salesPrice->company_code }}', '{{ $salesPrice->unit }}')"><i class="material-icons-outlined">edit</i>
                                </button>

                                <form id="delete-sales-form-{{ $salesPrice->id }}" action="{{ route('master.item-sales-price.delete', $salesPrice->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmDeleteSalesPrice(event, '{{ $salesPrice->id }}')" @if(!in_array('delete', $privileges)) disabled @endif><i class="material-icons-outlined">delete</i></button>
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
                        <th>Harga Jual</th>
                        {{-- <th>Category Customer</th> --}}
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Barang Harga Jual -->
<div class="modal fade" id="modalSalesPrice" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <legend id="legendSalesPriceForm">Input Daftar Harga Jual</legend>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form name="sales-price-form" id="sales-price-form" method="post" action="{{ route('master.item-sales-price.insert') }}">
                    @csrf
                    <label for="item_code_sales" class="form-label">Barang</label>
                    <div class="form-group mb-3">
                        <input type="hidden" class="form-control item-input" name="item_code" id="item_code" >
                        <input type="text" class="form-control item-input" name="item_name" id="item_name" placeholder="{{__('Search Item')}}" autocomplete="off">
                        <div id="item-search-results" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:300px; overflow:scroll;">
                            <!-- Search results will be injected here -->
                        </div>
                    </div>

                    <label for="sales_price" class="form-label">Harga Jual</label>
                    <div class="input-group mb-3">
                        <input type="number" id="sales_price" name="sales_price" class="form-control" placeholder="Harga Jual" aria-label="sales_price" aria-describedby="basic-addon1" required>
                    </div>
                    <label hidden for="company_code_sales" class="form-label">Company</label>
                    <div class="input-group mb-3">
                        <select hidden class="form-select" id="company_code_sales" name="company_code" required>
                            @foreach ($companies as $company)
                                <option value="{{ $company->company_code }}">{{ $company->company_name. ' (' . $company->company_code. ')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="btn-action-sales" name="btn-action-sales" type="submit" class="btn btn-primary btn-md">Insert</button>
                    <button type="button" class="btn btn-danger" id="cancelButtonSales" style="display:none;" data-bs-dismiss="modal" onclick="cancelEditSalesPrice()">Cancel</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="cancelEditSalesPrice()">Close</button>
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
        $('#salesPriceTable').DataTable();
    } );
</script>
<script>
    let privileges = @json($privileges);
    function cancelEditSalesPrice() {
        document.getElementById('item_code_sales').value = '';
        document.getElementById('sales_price').value = '';
        document.getElementById('legendSalesPriceForm').innerText = 'Daftar Harga Jual Insert';
        document.getElementById('cancelButtonSales').style.display = 'none';
        document.getElementById('btn-action-sales').innerText = 'Insert';
        document.getElementById('sales-price-form').action = "{{ route('master.item-sales-price.insert') }}"; // Ensure the action is correct
        if(!privileges.includes('create')){
                document.getElementById('btn-action-sales').disabled =true
            }else{
                document.getElementById('btn-action-sales').disabled =false
            }
    }

    function editSalesPrice(id, item_code, sales_price, company_code, unit, category_customer) {
        console.log('a');

        document.getElementById('item_code_sales').value = item_code;
        document.getElementById('sales_price').value = sales_price;
        document.getElementById('company_code_sales').value = company_code;
        document.getElementById('btn-action-sales').innerText = 'Edit';
        document.getElementById('cancelButtonSales').style.display = 'inline';
        document.getElementById('legendSalesPriceForm').innerText = 'Daftar Harga Jual Edit';

        // Change the form action to point to the edit route
        document.getElementById('sales-price-form').action = `{{ route('master.item-sales-price.edit', '') }}/${id}`;

        if(!privileges.includes('update')){
                document.getElementById('btn-action-sales').disabled =true
            }else{
                document.getElementById('btn-action-sales').disabled =false
            }

        // Show the modal
        $('#modalSalesPrice').modal('show');
    }

    function confirmDeleteSalesPrice(event, id) {
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
                document.getElementById('delete-sales-form-' + id).submit();
            }
        });
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
