@extends('layouts.master')

@section('title', 'Input Sales Invoice')
@section('css')
<style>
    #search-result-dest {
        max-height: 200px; /* Set your desired maximum height */
        overflow-y: auto; /* Enable vertical scrolling */
        border: 1px solid #ccc; /* Optional: Add a border */
        background-color: #fff; /* Optional: Set a background color */
        display: none; /* Initially hidden */
    }
    input:not([type]), input[type="checkbox"]{
        width: 20px;
        height: 20px;
        transform: scale(1.5);
        margin: 0;
        cursor: pointer;
    }
</style>
<!-- Include Flatpickr CSS for date picker -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endsection
@section('content')
<div class="row">
    <x-page-title title="Laporan Pengiriman {{__('Sales Invoice')}}" pagetitle="Laporan Pengiriman {{__('Sales Invoice')}} Input" />
    <hr>
    <div class="container content">
        <h2>Laporan Pengiriman {{__('Sales Invoice')}} </h2>
        <div class="card mb-3">
            <div class="card-header">Laporan Pengiriman {{__('Sales Invoice')}}</div>
            <div class="card-body">
                <input type="hidden" id="count_rows" name="count_rows" value="{{old('count_rows',0)}}">
                <!-- Date Range Filter -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="date_from">From Date:</label>
                        <input type="text" id="date_from" class="form-control date-picker" placeholder="Select start date">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to">To Date:</label>
                        <input type="text" id="date_to" class="form-control date-picker" placeholder="Select end date">
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button id="filter-btn" class="btn btn-primary">Filter</button>
                        <button id="reset-btn" class="btn btn-secondary">Reset</button>
                    </div>
                </div>
                <div style="overflow-x: auto;">
                    <div class="responsive">
                        <table class="table" id="example">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nomor Faktur</th>
                                    <th style="min-width: 150px">Kode Pelanggan</th>
                                    <th style="min-width: 150px">Grup Pelanggan</th>
                                    <th style="min-width: 150px">Pelanggan</th>
                                    <th style="min-width: 200px">Tanggal Faktur</th>
                                    <th>Tanggal Pengiriman</th>
                                    <th class="text-end">Jumlah COLY</th>
                                    <th class="text-end">Nominal Bfr Tax</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                                <tr class="filter-row">
                                    <th></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Nomor SI" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Kode Pelanggan" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Grup Pelanggan" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Nama Pelanggan" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Tanggal Dokumen" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Tanggal Kirim" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter COLY" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Nominal" data-sort="false"></th>
                                    <th><input type="text" class="form-control" placeholder="Filter Nominal" data-sort="false"></th>
                                </tr>
                            </thead>
                            <tbody id="itemRows">
                                @foreach ($combinedInvoices as $index => $si)
                                    <tr data-row-id="0" data-delivery-date="{{ \Carbon\Carbon::parse($si->delivery_date)->format('Y-m-d') }}">
                                        <td>{{$loop->iteration}}</td>
                                        <td style="font-size: 18px;">
                                            {{$si->sales_invoice_number??$si->pbr_number}}
                                            <input type="hidden" class="form-control" name="details[{{$index}}][sales_invoice_number]" id=""
                                            value="{{$si->sales_invoice_number}}" readonly>
                                        </td>
                                        <td>
                                            {{$si->customer_code}}
                                        </td>
                                        <td>
                                            {{ $customers->firstWhere(fn($customer) => $si->customers->group_customer === $customer->customer_code)?->customer_name ?? '' }}
                                        </td>
                                        <td>
                                            {{$si->customers->customer_name}}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($si->document_date)->format('d M Y') }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($si->delivery_date)->format('d M Y') }}
                                        </td>
                                        <td class="text-end">
                                            @if($si instanceof \App\Models\SalesInvoice||$si instanceof \App\Models\Pbr)
                                                {{ number_format($si->details->sum('qty'), 0, ',', '.') }}
                                            @else
                                                {{ number_format($si->qty ?? 0, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="text-end">{{number_format($si->subtotal)}}</td>
                                        <td class="text-end">{{number_format($si->total)}}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="10">Total</td>
                                </tr>
                                <tr class="total-amount-row">
                                    <td></td>
                                    <td class="text-center"></td>
                                    <td class="text-end" colspan="5"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                    <td class="text-end"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
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
@endsection

@section('scripts')
    <script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
    <!-- Include Flatpickr JS for date picker -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#example').DataTable({
                lengthChange: false,
                orderCellsTop: true,
                drawCallback: function() {
                    console.log('drawCallback triggered');
                    calculateTotalAmount();
                    calculateSubTotalAmount();
                    calculateQtyAmount();
                    calculateCount();
                }
            });
            $('#example thead tr.filter-row input').on('click', function(e) {
                e.stopPropagation();
            });
            calculateQtyAmount();
            calculateCount();
            calculateSubTotalAmount();
            calculateTotalAmount();
            // Add filtering functionality to input fields
            $('#example thead tr.filter-row input').on('keyup change', function() {
                var columnIndex = $(this).parent().index();
                table.column(columnIndex).search(this.value).draw();
            });

            // Restore filter inputs if page is reloaded with search params
            $('#example thead tr.filter-row input').each(function() {
                var columnIndex = $(this).parent().index();
                $(this).val(table.column(columnIndex).search());
            });

            // Initialize Flatpickr for date pickers

            // Custom filter function for date range
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var min = $('#date_from').val();
                    var max = $('#date_to').val();
                    var date = $(table.row(dataIndex).node()).data('delivery-date'); // Get date from data attribute

                    console.log('Filtering: min=', min, 'max=', max, 'date=', date); // Debug log

                    if (!date) return true; // If no date, include row

                    // Convert to Date objects for comparison
                    var minDate = min ? new Date(min) : null;
                    var maxDate = max ? new Date(max) : null;
                    var rowDate = new Date(date);

                    if (
                        (!minDate && !maxDate) || // No filter
                        (!minDate && rowDate <= maxDate) || // Only max date
                        (!maxDate && rowDate >= minDate) || // Only min date
                        (rowDate >= minDate && rowDate <= maxDate) // Both dates
                    ) {
                        return true;
                    }
                    return false;
                }
            );

            // Filter button click event
            $('#filter-btn').on('click', function() {
                console.log('Filter button clicked'); // Debug log
                table.draw();
            });

            // Reset button click event
            $('#reset-btn').on('click', function() {
                console.log('Reset button clicked'); // Debug log
                $('#date_from').val('');
                $('#date_to').val('');
                table.draw();
            });

            // Append buttons to DataTable
            table.buttons().container()
                .appendTo('#example_wrapper .col-md-6:eq(0)');

            function calculateQtyAmount() {
                if (!table) {
                    console.error('DataTable not initialized');
                    return;
                }

                    console.log('Calculating Qty amount...');
                    let total = 0;
                    table.column(7, { search: 'applied' }).data().each(function(value) {
                        if (value && typeof value === 'string') {
                            console.log('Processing value:', value);
                            let numericValue = parseFloat(value.replace(/,/g, '')) || 0;
                            total += numericValue;
                        }
                    });

                    console.log('Qty calculated:', total);
                    let formattedTotal = total.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    $('#example tfoot tr.total-amount-row td').eq(3).text(formattedTotal);
            }

            function calculateTotalAmount() {
                if (!table) {
                    console.error('DataTable not initialized');
                    return;
                }

                    console.log('Calculating total amount...');
                    let total = 0;
                    table.column(8, { search: 'applied' }).data().each(function(value) {
                        if (value && typeof value === 'string') {
                            console.log('Processing value:', value);
                            let numericValue = parseFloat(value.replace(/,/g, '')) || 0;
                            total += numericValue;
                        }
                    });

                    console.log('Total calculated:', total);
                    let formattedTotal = total.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    $('#example tfoot tr.total-amount-row td').eq(4).text(formattedTotal);
            }
            function calculateSubTotalAmount() {
                if (!table) {
                    console.error('DataTable not initialized');
                    return;
                }

                    console.log('Calculating total amount...');
                    let total = 0;
                    table.column(9, { search: 'applied' }).data().each(function(value) {
                        if (value && typeof value === 'string') {
                            console.log('Processing value:', value);
                            let numericValue = parseFloat(value.replace(/,/g, '')) || 0;
                            total += numericValue;
                        }
                    });

                    console.log('Total calculated:', total);
                    let formattedTotal = total.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    $('#example tfoot tr.total-amount-row td').eq(5).text(formattedTotal);
            }

            function calculateCount() {
                if (!table) {
                    console.error('DataTable not initialized');
                    return;
                }

                    console.log('Calculating total amount...');
                    let total = 0;
                    table.column(8, { search: 'applied' }).data().each(function(value) {
                        total++;
                    });

                    console.log('Total calculated:', total);
                    let formattedTotal = total.toLocaleString('id-ID', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                    $('#example tfoot tr.total-amount-row td').eq(1).text(formattedTotal);
            }
        });
    </script>
@endsection
