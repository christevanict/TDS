@extends('layouts.master')

@section('title','Analisa '. __('Purchase Invoice'))

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
    <x-page-title title="Analisa {{__('Purchase Invoice')}}" pagetitle="Analisa {{__('Purchase Invoice')}}" />
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
            <h6 class="mb-2 text-uppercase">Rangkuman {{__('Purchase Invoice')}}</h6>
            <div class="table-responsive">
                <table id="purchase-invoice-summary" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>{{__('Document Date')}}</th>
                            <th>{{__('Purchase Invoice Number')}}</th>
                            <th>Kode {{__('Supplier')}}</th>
                            <th>Nama {{__('Supplier')}}</th>
                            <th>Nama Barang</th>
                            <th>COLY</th>
                            <th>QTY</th>
                            <th>Harga</th>
                            <th>Diskon</th>
                            <th>Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $rowNumber = 0; // Initialize counter outside both loops
                        @endphp
                        @foreach ($purchaseInvoices as $invoice)
                            @foreach($invoice->details as $detail)
                            @php
                                $rowNumber++; // Increment for each detail row
                            @endphp
                            <tr class="clickable-row">
                                <td>{{$rowNumber}}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->document_date)->format('d M Y') }}</td>
                                <td>{{ $invoice->purchase_invoice_number }}</td>
                                <td>{{ $invoice->suppliers->supplier_code }}</td>
                                <td>{{ $invoice->suppliers->supplier_name }}</td>
                                <td>{{$detail->items->item_name}}</td>
                                <td>{{number_format($detail->qty,0)}}</td>
                                <td>{{number_format(($detail->qty*$detail->base_qty),0)}}</td>
                                <td>{{number_format(($detail->price),0)}}</td>
                                <td>{{number_format(($detail->disc_nominal+($detail->disc_percent*$detail->qty*$detail->base_qty*$detail->price)),0)}}</td>
                                <td class="text-end">{{ number_format($detail->nominal,0) }}</td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-amount-row">
                            <td colspan="10">Total untuk range tanggal terpilih:</td>
                            <td>{{ number_format($totalAmount, 2) }}</td>
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
            $('#purchase-invoice-summary').DataTable();
        });
    </script>
@endsection
