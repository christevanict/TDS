@extends('layouts.master')

@section('title', 'Purchase Requisition Summary')

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
    <x-page-title title="Purchase Requisition Summary" pagetitle="Purchase Requisition Summary {{__('Information')}}" />
    <hr>

    <!-- Date Filter Form -->
    <div class="mb-3 card">
        <div class="card-body">
            <form method="GET" action="{{ route('transaction.purchase_requisition.summary') }}">
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
            <h6 class="mb-2 text-uppercase">Purchase Requisition Summary</h6>
            <div class="table-responsive">
                <table id="purchase-invoice-summary" class="table table-hover table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Purchase Requisition Number</th>
                            <th>{{__('Purchase Order Number')}}</th>
                            <th>{{__('Document Date')}}</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseRequisitions as $invoice)
                            <tr class="clickable-row">
                                <td>{{ $invoice->purchase_requisition_number }}</td>
                                <td>{{ $invoice->purchase_order_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($invoice->document_date)->format('d M Y') }}</td>
                                <td>{{ number_format($invoice->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-amount-row">
                            <td colspan="3">Total untuk range tanggal terpilih:</td>
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
