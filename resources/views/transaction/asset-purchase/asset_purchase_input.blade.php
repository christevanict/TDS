@extends('layouts.master')

@section('title', 'Input Pembelian Aset')

@section('content')
<div class="row">
    <x-page-title title="Pembelian Aset" pagetitle="Input Pembelian Aset" />
    <hr>
    <div class="container content">
        <h2>Input Pembelian Aset</h2>

        <form id="asset-purchase-form" action="{{ route('asset-purchase.store') }}" method="POST">
            @csrf

            <div class="card mb-3">
                <div class="card-header">Informasi Pembelian Aset</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">{{__('Search Supplier')}}</label>
                                <input type="text" id="search" class="form-control"
                                    placeholder="Search by Vendor Code, Name, or Address" autocomplete="off">
                                <div id="search-results" class="list-group"
                                    style="display:none; position:relative; z-index:1000; width:100%;"></div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="supplier_code">{{__('Supplier Code')}}</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control" readonly >
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="supplier_name">{{__('Supplier Name')}}</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" readonly>
                            </div>

                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="document_date">Tanggal Dokumen</label>
                                <input type="date" name="document_date" class="form-control date-picker" required value="{{ old('document_date', date('Y-m-d')) }}" id="document_date" max="{{ date('Y-m-d') }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">Tanggal Jatuh Tempo</label>
                                <input type="date" name="due_date" class="form-control date-picker" required value="{{ old('due_date', date('Y-m-d')) }}" id="due_date">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="asset_code">Aset</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-asset" autocomplete="off" class="form-control" placeholder="Search by Asset Number or Name" required autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-asset')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-asset" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="asset_code" id="asset_code">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="subtotal">Subtotal</label>
                                <input type="text" oninput="formatNumber(this); calculateNominal()" name="subtotal" class="form-control text-end" required value="{{ old('subtotal') }}" id="subtotal">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="add_tax">Pajak</label>
                                <input type="text" oninput="formatNumber(this); calculateNominal()" name="add_tax" class="form-control text-end" readonly required value="{{ old('add_tax') }}" id="add_tax">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="nominal">Nominal</label>
                                <input type="text" name="nominal" class="form-control text-end" readonly required value="{{ old('nominal') }}" id="nominal">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit Pembelian Aset</button>
            </div>
        </form>
    </div>

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
</div>

@section('scripts')
<script>
    const assets = @json($asset);
    const suppliers = @json($suppliers);
    const tax = @json($tax);
    let supplierId='';
    function setupSearch(inputId, resultsContainerId, inputHid) {
        const inputElement = document.getElementById(inputId);
        const resultsContainer = document.getElementById(resultsContainerId);
        inputElement.addEventListener('input', function () {
            activeIndex = -1;
            let query = this.value.toLowerCase();
            resultsContainer.innerHTML = '';
            resultsContainer.style.display = 'none';
            if (query.length > 0) {
                let filteredResults = assets.filter(item =>
                    item.asset_code.toLowerCase().includes(query) ||
                    item.asset_name.toLowerCase().includes(query)
                );
                if (filteredResults.length > 0) {
                    resultsContainer.style.display = 'block';
                    filteredResults.forEach(item => {
                        let listItem = document.createElement('a');
                        listItem.className = 'list-group-item list-group-item-action';
                        listItem.href = '#';
                        listItem.innerHTML = `
                            <strong>${item.asset_code}</strong> - ${item.asset_name}`;
                        listItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            inputElement.value = item.asset_code + ' - ' + item.asset_name;
                            inputElement.readOnly = true;
                            document.getElementById(inputHid).value = item.asset_code;
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
                console.log(activeIndex , items.length - 1);

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
    function updateActiveItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndex);
        });
        if (activeIndex >= 0) {
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    let activeIndexCust = -1; // Track the active supplier in the dropdown

    document.getElementById('search').addEventListener('input', function () {
        activeIndexCust = -1; // Reset active index on new input
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = ''; // Clear previous results
        resultsContainer.style.display = 'none'; // Hide dropdown by default

        if (query.length > 0) {
            let filteredSuppliers = suppliers.filter(s =>
                s.supplier_code.toLowerCase().includes(query) ||
                s.supplier_name.toLowerCase().includes(query) ||
                s.address.toLowerCase().includes(query)
            );

            if (filteredSuppliers.length > 0) {
                resultsContainer.style.display = 'block'; // Show dropdown if matches found
                filteredSuppliers.forEach((supplier, index) => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.dataset.index = index; // Store index for reference
                    listItem.innerHTML = `
                        <strong>${supplier.supplier_code}</strong> - ${supplier.supplier_name} <br>
                        <small>${supplier.address}</small>
                    `;
                    listItem.addEventListener('click', function (e) {
                        e.preventDefault();
                        selectSupplier(supplier);
                    });
                    resultsContainer.appendChild(listItem); // Add item to dropdown
                });
            }
        }
    });

    // Keydown event listener for navigation
    document.getElementById('search').addEventListener('keydown', function (e) {
        const resultsContainer = document.getElementById('search-results');
        const items = resultsContainer.querySelectorAll('.list-group-item');
        if (items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (activeIndexCust < items.length - 1) {
                activeIndexCust++;
                updateActiveSupplier(items);
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeIndexCust > -1) {
                activeIndexCust--;
                updateActiveSupplier(items);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndexCust >= 0 && items[activeIndexCust]) {
                items[activeIndexCust].click(); // Trigger click event
            }
        }
    });

    // Helper function to update active supplier
    function updateActiveSupplier(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndexCust);
        });
        if (activeIndexCust >= 0 && items[activeIndexCust]) {
            items[activeIndexCust].scrollIntoView({ block: 'nearest' });
        }
    }

    // Helper function to handle supplier selection
    function selectSupplier(supplier) {
        supplierId = supplier.supplier_code;
        document.getElementById('search').value = '';
        document.getElementById('supplier_code').value = supplier.supplier_code;
        document.getElementById('supplier_name').value = supplier.supplier_name;
        document.getElementById('address').value = supplier.address;
        document.getElementById('search-results').style.display = 'none'; // Hide dropdown after selection

        if (!supplierId) {
            $('#invoiceTable tbody tr').show();
        } else {
            // Hide rows that do not match the selected supplier
            $('#invoiceTable tbody tr').each(function () {
                const selectedSuppliers = $(this).data('supplier-id');
                if (selectedSuppliers == supplierId) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    }

    function clearInput(inputId) {
        document.getElementById(inputId).value = '';
        document.getElementById(inputId).readOnly = false;
        document.getElementById('asset_code').value = '';
    }

    setupSearch('search-asset', 'search-result-asset', 'asset_code');

    function formatNumber(input) {
        const cursorPosition = input.selectionStart;
        input.value = input.value.replace(/[^0-9]/g, '');
        let value = input.value.replace(/,/g, '');
        let formattedValue = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        input.value = formattedValue;
        const newCursorPosition = formattedValue.length - (value.length - cursorPosition);
        input.setSelectionRange(newCursorPosition, newCursorPosition);
    }

    function calculateNominal() {
        let selectedSupplier = suppliers.find((e)=>e.supplier_code==supplierId);
        let subtotal = parseFloat(document.getElementById('subtotal').value.replace(/,/g, '')) || 0;
        let add_tax = 0;

        if(selectedSupplier.pkp){
            add_tax =  parseFloat(subtotal *(tax.tariff * tax.tax_base)/100).toFixed(0);
        }
        document.getElementById('add_tax').value = add_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        let nominal = parseFloat(subtotal) + parseFloat(add_tax);
        let formattedNominal = nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('nominal').value = formattedNominal;
    }

    document.getElementById('document_date').addEventListener('input', function () {
        let selectedDate = new Date(this.value);
        let today = new Date();
        today.setHours(0, 0, 0, 0);
        if (selectedDate > today) {
            this.value = today.toISOString().split('T')[0];
        }
    });
</script>
@endsection
@endsection
