@extends('layouts.master')

@section('title','Rangkuman '. __('Sales Invoice'))

@section('css')
    <style>
        .clickable-row {
            cursor: pointer;
        }

        .clickable-row:hover,
        .clickable-row:focus {
            background-color: #f1f1f1;
        }

        .btn-insert {
            margin-bottom: 20px;
        }

        .btn-print,
        .btn-edit {
            margin-right: 10px;
        }

        .date-filter {
            margin-bottom: 20px;
        }

        /* Align the total amount row to the right */
        .total-amount-row td {
            font-weight: bold;
            text-align: right;
            background-color: #f8f9fa;
        }

        /* Style for filter inputs */
        .filter-row input {
            width: 100%;
            box-sizing: border-box;
        }
    </style>
@endsection

@section('content')
    <x-page-title title="{{__('Sales Invoice')}}" pagetitle="Rangkuman {{__('Sales Invoice')}}" />
    <hr>

    <!-- Date Filter Form -->
    <div class="mb-3 card">
        <div class="card-body">
            <form method="GET" action="{{ route('transaction.sales_invoice.summary_detail') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="from_date">From Date</label>
                        <input type="date" name="from_date" id="from_date" class="form-control date-picker"
                            value="{{ request()->from_date }}">
                    </div>
                    <div class="col-md-4">
                        <label for="to_date">To Date</label>
                        <input type="date" name="to_date" id="to_date" class="form-control date-picker"
                            value="{{ request()->to_date }}">
                    </div>
                    <div class="col-auto d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h6 class="mb-2 text-uppercase">Rangkuman {{__('Sales Invoice')}}</h6>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>{{__('Document Date')}}</th>
                            <th>{{__('Sales Invoice Number')}}</th>
                            <th>Kode {{__('Customer')}}</th>
                            <th>Nama {{__('Customer')}}</th>
                            <th>Nama Barang</th>
                            <th>QTY</th>
                            <th>Harga</th>
                            @if(in_array('discount', $privileges))
                            <th>Diskon</th>
                            @endif
                            @if(in_array('price', $privileges))
                            <th>Nominal</th>
                            @endif
                        </tr>
                        <tr class="filter-row">
                            <th></th> <!-- Empty for No -->
                            <th><input type="text" class="form-control" placeholder="Filter Date" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter SI Number" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Kode" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Nama" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Barang" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter QTY" data-sort="false"></th>
                            <th><input type="text" class="form-control" placeholder="Filter Harga" data-sort="false"></th>
                            @if(in_array('discount', $privileges))
                            <th><input type="text" class="form-control" placeholder="Filter Diskon" data-sort="false"></th>
                            @endif
                            @if(in_array('price', $privileges))
                            <th><input type="text" class="form-control" placeholder="Filter Nominal" data-sort="false"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rowNumber = 0; // Initialize counter outside both loops
                        @endphp
                        @foreach ($salesInvoices as $invoice)
                        @foreach($invoice->details as $detail)
                        @php
                            $rowNumber++; // Increment for each detail row
                        @endphp
                        <tr class="clickable-row">
                            <td>{{$rowNumber}}</td>
                            <td>{{ \Carbon\Carbon::parse($invoice->document_date)->format('d M Y') }}</td>
                            <td>{{ $invoice->sales_invoice_number??$invoice->pbr_number }}</td>
                            <td>{{ $invoice->customers->customer_code }}</td>
                            <td>{{ $invoice->customers->customer_name }}</td>
                            <td>{{$detail->items->item_name}}</td>
                            <td>{{number_format($detail->qty,0)}}</td>
                            <td>{{number_format(($detail->price),0)}}</td>
                            @if(in_array('discount', $privileges))
                            <td>{{number_format(($detail->disc_nominal+($detail->disc_percent*$detail->qty*$detail->base_qty*$detail->price)),0)}}</td>
                            @endif
                            @if(in_array('price', $privileges))
                            <td class="text-end">{{ number_format($detail->nominal,0) }}</td>
                            @endif
                        </tr>
                        @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-amount-row">
                            @if(in_array('price', $privileges))
                            <td colspan="9">Total untuk range tanggal terpilih:</td>
                            <td>{{ number_format($totalAmount,0) }}</td>
                            @endif
                        </tr>
                    </tfoot>
                </table>
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
            // Debugging logs to verify library loading
            console.log('jQuery loaded:', typeof jQuery);
            console.log('DataTables loaded:', typeof $.fn.DataTable);
            console.log('Table element:', $('#example').length);

            // Ensure CSRF token for AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            console.log('Attempting to initialize DataTable');
            var table = null;
            try {
                table = $('#example').DataTable({
                    lengthChange: false,
                    orderCellsTop: true,
                        buttons: ['copy', 'excel', 'pdf', 'print'],
                    drawCallback: function() {
                        console.log('drawCallback triggered');
                        calculateTotalAmount();
                    }
                });
                table.buttons().container()
                    .appendTo('#example_wrapper .col-md-6:eq(0)');
                console.log('DataTable initialized:', table);

                // Prevent sorting when clicking filter inputs
                $('#example thead tr.filter-row input[data-sort="false"]').on('click', function(e) {
                    e.stopPropagation();
                });

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
            } catch (e) {
                console.error('DataTable initialization failed:', e);
            }

            function calculateTotalAmount() {
                if (!table) {
                    console.error('DataTable not initialized');
                    return;
                }

                @if(in_array('price', $privileges))
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
                    $('#example tfoot tr.total-amount-row td').eq(1).text(formattedTotal);
                @else
                    console.log('Price privilege not available, skipping total calculation');
                @endif
            }
        });
    </script>
@endsection
