@extends('layouts.master')

@section('title','Rangkuman '. __('Purchase Invoice'))

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
    </style>
@endsection

@section('content')
    <x-page-title title="{{__('Purchase Invoice')}} Summary" pagetitle="{{__('Purchase Invoice')}} Summary {{__('Information')}}" />
    <hr>

    <!-- Date Filter Form -->
    <div class="mb-3 card">
        <div class="card-body">
            <form method="GET" action="{{ route('transaction.purchase_invoice.summary') }}">
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
            <h6 class="mb-2 text-uppercase">{{__('Purchase Invoice')}} Summary</h6>
            <div class="table-responsive">
                <table id="example" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>{{__('Purchase Invoice Number')}}</th>
                            <th>{{__('Supplier Code')}}</th>
                            <th>{{__('Supplier Name')}}</th>
                            <th>Nomor Dokumen Pemasok</th>
                            <th>{{__('Document Date')}}</th>
                            @if(in_array('price', $privileges))
                            <th>{{__('Discount')}}</th>
                            <th>Subtotal</th>
                            <th>Tax Revenue</th>
                            <th>Add Tax</th>
                            <th>Total</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseInvoices as $invoice)
                            <tr class="clickable-row">
                                <td>{{$loop->iteration}}</td>
                                <td>{{ $invoice->purchase_invoice_number }}</td>
                                <td>{{ $invoice->supplier_code }}</td>
                                <td>{{ $invoice->suppliers->supplier_name }}</td>
                                <td>{{$invoice->vendor_number??''}}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->document_date)->format('d M Y') }}</td>
                                @if(in_array('price', $privileges))
                                <td class="text-end">{{ number_format($invoice->disc_nominal, 2) }}</td>
                                <td class="text-end">{{ number_format($invoice->subtotal, 2) }}</td>
                                <td class="text-end">{{ number_format($invoice->tax_revenue, 2) }}</td>
                                <td class="text-end">{{ number_format($invoice->add_tax, 2) }}</td>
                                <td class="text-end">{{ number_format($invoice->total, 2) }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-amount-row">
                            @if(in_array('price', $privileges))
                            <td colspan="10">Total untuk range tanggal terpilih:</td>
                            <td>{{ number_format($totalAmount, 2) }}</td>
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
            var table = $('#example').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );

			table.buttons().container()
				.appendTo( '#example_wrapper .col-md-6:eq(0)' );
        });
    </script>
@endsection
