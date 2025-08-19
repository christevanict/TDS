@extends('layouts.master')

@section('title', 'Input Penjualan Aset')

@section('content')
<div class="row">
    <x-page-title title="Penjualan Aset" pagetitle="Input Penjualan Aset" />
    <hr>
    <div class="container content">
        <h2>Input Penjualan Aset</h2>

        <form id="asset-sale-form" action="{{ route('asset-sales.store') }}" method="POST">
            @csrf

            <div class="card mb-3">
                <div class="card-header">Informasi Penjualan Aset</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="search">{{__('Search Customer')}}</label>
                                <input type="text" id="search" class="form-control" placeholder="Search by Customer Code, Name, or Address" autocomplete="off">
                                <div id="search-results" class="list-group" style="display:none; position:relative; z-index:1000; width:100%;">
                                    <!-- Search results will be injected here -->
                                </div>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_code">{{__('Customer Code')}}</label>
                                <input type="text" name="customer_code" id="customer_code" class="form-control" readonly required>
                                <input type="hidden" name="category_customer" id="category_customer" class="form-control" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" readonly required>
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
                                <input type="date" name="document_date" class="form-control date-picker" required value="{{ old('document_date', date('Y-m-d')) }}" id="document_date">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="due_date">Tanggal Jatuh Tempo</label>
                                <input type="date" name="due_date" class="form-control date-picker" required value="{{ old('due_date', date('Y-m-d')) }}" id="due_date" max="{{ date('Y-m-d') }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="asset_number">Aset</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-asset" autocomplete="off" class="form-control" placeholder="Search by Asset Number or Name" required autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-asset')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-asset" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="asset_number" id="asset_number">
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
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit Penjualan Aset</button>
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
    const assets = @json($assetDetails);
    const customers = @json($customers);
    const tax = @json($tax);
     let activeIndexCust = -1; // Track the active customer in the dropdown

    document.getElementById('search').addEventListener('input', function () {
        activeIndexCust = -1; // Reset active index on new input
        let query = this.value.toLowerCase();
        let resultsContainer = document.getElementById('search-results');
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';

        if (query.length > 0) {
            let filteredCustomers = customers.filter(c =>
                c.customer_code.toLowerCase().includes(query) ||
                c.customer_name.toLowerCase().includes(query) ||
                c.address.toLowerCase().includes(query)
            );

            if (filteredCustomers.length > 0) {
                resultsContainer.style.display = 'block';
                filteredCustomers.forEach((customer, index) => {
                    let listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action';
                    listItem.href = '#';
                    listItem.dataset.index = index; // Store index for reference
                    listItem.innerHTML = `
                        <strong>${customer.customer_code}</strong> -
                        ${customer.customer_name} <br>
                        <small>${customer.address} - ${customer.city}</small>`;
                    listItem.addEventListener('click', function (e) {
                        e.preventDefault();
                        selectCustomer(customer);
                    });
                    resultsContainer.appendChild(listItem);
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
                updateActiveCustomer(items);
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (activeIndexCust > -1) {
                activeIndexCust--;
                updateActiveCustomer(items);
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeIndexCust >= 0 && items[activeIndexCust]) {
                items[activeIndexCust].click(); // Trigger click event
            }
        }
    });

    // Helper function to update active customer
    function updateActiveCustomer(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndexCust);
        });
        if (activeIndexCust >= 0 && items[activeIndexCust]) {
            items[activeIndexCust].scrollIntoView({ block: 'nearest' });
        }
    }

    // Helper function to handle customer selection
    function selectCustomer(customer) {
        let customerId = customer.customer_code;
        document.getElementById('search').value = '';
        document.getElementById('customer_code').value = customer.customer_code;
        document.getElementById('customer_name').value = customer.customer_name;
        document.getElementById('address').value = customer.address;
        document.getElementById('search-results').style.display = 'none';


    }
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
                    item.asset_number.toLowerCase().includes(query) ||
                    item.asset_name.toLowerCase().includes(query)
                );
                if (filteredResults.length > 0) {
                    resultsContainer.style.display = 'block';
                    filteredResults.forEach(item => {
                        let listItem = document.createElement('a');
                        listItem.className = 'list-group-item list-group-item-action';
                        listItem.href = '#';
                        listItem.innerHTML = `
                            <strong>${item.asset_number}</strong> - ${item.asset_name}`;
                        listItem.addEventListener('click', function(e) {
                            e.preventDefault();
                            inputElement.value = item.asset_number + ' - ' + item.asset_name;
                            inputElement.readOnly = true;
                            document.getElementById(inputHid).value = item.asset_number;
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

    function clearInput(inputId) {
        document.getElementById(inputId).value = '';
        document.getElementById(inputId).readOnly = false;
        document.getElementById('asset_number').value = '';
    }
    function updateActiveItem(items) {
        items.forEach((item, index) => {
            item.classList.toggle('active', index === activeIndex);
        });
        if (activeIndex >= 0) {
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        }
    }

    setupSearch('search-asset', 'search-result-asset', 'asset_number');

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
        let subtotal = parseFloat(document.getElementById('subtotal').value.replace(/,/g, '')) || 0;
        let add_tax = parseFloat(subtotal *(tax.tariff * tax.tax_base)/100).toFixed(0);

        document.getElementById('add_tax').value = add_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        let nominal = parseFloat(subtotal) + parseFloat(add_tax);
        let formattedNominal = nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('nominal').value = formattedNominal;
    }

</script>
@endsection
@endsection
