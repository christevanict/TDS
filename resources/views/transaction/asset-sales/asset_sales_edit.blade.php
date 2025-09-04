@extends('layouts.master')

@section('title', 'Edit Penjualan Aset')

@section('content')
<div class="row">
    <x-page-title title="Edit Penjualan Aset" pagetitle="Edit Penjualan Aset" />
    <hr>
    <div class="container content">
        @if (!$editable)
        <h7 style="color: red">Alasan tidak bisa edit</h7>
        <ul>
            @foreach (explode('<br>', trim($note, '<br>')) as $item)
                @if (!empty($item))
                    <li>{!! $item !!}</li>
                @endif
            @endforeach
        </ul>
        @endif
        <h2>Edit Penjualan Aset</h2>

        <div id="message-container">
            @if($errors->any())
                <div id="error-message" class="alert alert-danger fade show">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <form id="asset-sale-form" action="{{ route('asset-sales.update', $assetSale->id) }}" method="POST">
            @csrf

            <div class="card mb-3">
                <div class="card-header">Informasi Penjualan Aset</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="customer_code">{{__('Customer Code')}}</label>
                                <input type="text" name="customer_code" id="customer_code" class="form-control" value="{{ $assetSale->customer_code }}" readonly >
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="customer_name">{{__('Customer Name')}}</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $assetSale->customers->customer_name }}" readonly>
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="address">{{__('Address')}}</label>
                                <input type="text" name="address" id="address" class="form-control" value="{{ $assetSale->customers->address }}" readonly>
                            </div>

                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="asset_sales_number">Nomor Penjualan Aset</label>
                                <input type="text" name="asset_sales_number" class="form-control" readonly required value="{{ old('asset_sales_number', $assetSale->asset_sales_number) }}">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">Tanggal Dokumen</label>
                                <input type="date" name="document_date" class="form-control date-picker" required value="{{ old('document_date', $assetSale->document_date) }}" id="document_date" >
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="document_date">Tanggal Jatuh Tempo</label>
                                <input type="date" name="due_date" class="form-control date-picker" required value="{{ old('due_date', $assetSale->due_date) }}" id="due_date" >
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="asset_number">Aset</label>
                                <div class="form-group mb-3">
                                    <div class="input-group">
                                        <input type="text" id="search-asset" autocomplete="off" class="form-control" placeholder="Search by Asset Number or Name" required readonly value="{{ $assetSale->asset_number . ' - ' . ($assetSale->assetDetail->asset_name ?? '') }}" autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" onclick="clearInput('search-asset')"><i class="material-icons-outlined">edit</i></button>
                                    </div>
                                    <div id="search-result-asset" class="list-group" style="display:none; position:relative; z-index:1000; width:100%; max-height:200px; overflow:scroll;">
                                        <!-- Search results will be injected here -->
                                    </div>
                                    <input type="hidden" name="asset_number" id="asset_number" value="{{ $assetSale->asset_number }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="subtotal">Subtotal</label>
                                <input type="text" oninput="formatNumber(this); calculateNominal()" name="subtotal" class="form-control text-end" required value="{{ old('subtotal', number_format($assetSale->subtotal, 0, '.', ',')) }}" id="subtotal">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="add_tax">Pajak</label>
                                <input type="text" oninput="formatNumber(this); calculateNominal()" name="add_tax" class="form-control text-end" required value="{{ old('add_tax', number_format($assetSale->add_tax, 0, '.', ',')) }}" id="add_tax">
                            </div>
                            <br>
                            <div class="form-group">
                                <label for="nominal">Nominal</label>
                                <input type="text" name="nominal" class="form-control text-end" readonly required value="{{ old('nominal', number_format($assetSale->nominal, 0, '.', ',')) }}" id="nominal">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($editable)
            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-primary" @if(!in_array('update', $privileges)) disabled @endif>Edit Penjualan Aset</button>
            </div>

            @endif
        </form>
        @if($editable)
        <form id="delete-form" action="{{ route('asset-sales.destroy', $assetSale->id) }}" method="POST" style="display:inline;">
            @csrf
            @method('POST')
            <button type="button" class="btn btn-sm btn-danger mb-3" onclick="confirmDelete(event, '{{ $assetSale->id }}')" @if(!in_array('delete', $privileges)) disabled @endif>
                <i class="material-icons-outlined">delete</i>
            </button>
        </form>
        @endif
    </div>

    @if (session('success'))
        <script>
            Swal.fire({
                title: 'Success!',
                text: "{{ session('success') }}",
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('asset-sales.index') }}";
                }
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
</div>

@section('scripts')
<script>
    const assets = @json($assetDetails);
    const tax = @json($tax);
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
    let is_tax = @json($is_tax)

    function calculateNominal() {
        let subtotal = parseFloat(document.getElementById('subtotal').value.replace(/,/g, '')) || 0;
        let add_tax = 0;
        if(is_tax){
            add_tax = parseFloat(subtotal *(tax.tariff * tax.tax_base)/100).toFixed(0);
        }

        document.getElementById('add_tax').value = add_tax.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',')
        let nominal = parseFloat(subtotal) + parseFloat(add_tax);
        let formattedNominal = nominal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('nominal').value = formattedNominal;
    }

    function confirmDelete(event, id) {
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
                document.getElementById('delete-form').submit();
            }
        });
    }

</script>
@endsection
@endsection
