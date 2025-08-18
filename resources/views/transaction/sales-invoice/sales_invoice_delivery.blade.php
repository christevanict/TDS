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
@endsection
@section('content')
<div class="row">
    <x-page-title title="Konfirmasi Pengiriman {{__('Sales Invoice')}}" pagetitle="Konfirmasi Pengiriman {{__('Sales Invoice')}} Input" />
    <hr>
    <div class="container content">
        <h2>Konfirmasi Pengiriman {{__('Sales Invoice')}} </h2>
        <form id="bank-cash-out-form" action="{{ route('transaction.sales_invoice.update_status')}}" method="POST">
            @csrf

            <div class="card mb-3">
                <div class="card-header">Konfirmasi Pengiriman {{__('Sales Invoice')}}</div>
                <div class="card-body">
                    <input type="hidden" id="count_rows" name="count_rows" value="{{old('count_rows',0)}}">
                    <div style="overflow-x: auto;">
                        <div class="responsive">


                    <table class="table" id="example">
                        <thead>
                            <tr>
                                <th style="max-width: 400px;">Tanggal Pengiriman</th>
                                <th>Nomor Faktur</th>
                                <th style="min-width: 150px">Pelanggan</th>
                                <th style="min-width: 200px">Tanggal Faktur</th>
                                <th class="text-end">Jumlah COLY</th>
                                <th class="text-end" style="min-width: 150px">Total</th>
                            </tr>
                            <tr>
                                <th colspan="2" style="font-size: 13px; color:darkred;">(Biarkan kosong apabila faktur tersebut tidak dikirim)</th>
                                <th colspan="4"></th>
                            </tr>
                        </thead>
                        <tbody id="itemRows">
                            @foreach ($combinedInvoices as $index => $invoice)
                                <tr data-row-id="0">
                                    <td>
                                        <input type="date" name="details[{{$index}}][delivery_date]" id="delivery_date" class="form-control date-picker" required value="">
                                    </td>
                                    <td style="font-size: 18px;">
                                        {{$invoice instanceof \App\Models\SalesInvoice ? $invoice->sales_invoice_number : $invoice->pbr_number}}
                                        <input type="hidden" class="form-control" name="details[{{$index}}][document_number]" id=""
                                        value="{{$invoice instanceof \App\Models\SalesInvoice ? $invoice->sales_invoice_number : $invoice->pbr_number}}" readonly>
                                        <input type="hidden" name="details[{{$index}}][type]" value="{{$invoice instanceof \App\Models\SalesInvoice ? 'sales_invoice' : 'pbr'}}">
                                    </td>
                                    <td>
                                        @if($invoice instanceof \App\Models\SalesInvoice)
                                            {{$invoice->customers->customer_name}}
                                        @elseif($invoice instanceof \App\Models\Pbr)
                                            {{$invoice->customers->customer_name}}
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($invoice->document_date)->format('d M Y') }}
                                    </td>
                                    <td class="text-end">
                                        @if($invoice instanceof \App\Models\SalesInvoice||$invoice instanceof \App\Models\Pbr)
                                            {{ number_format($invoice->details->sum('qty'), 0, ',', '.') }}
                                        @else
                                            {{ number_format($invoice->qty ?? 0, 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ number_format($invoice->total, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                    </div>
                    <button type="button" id="add-row" class="btn btn-primary mt-3">Add Detail</button>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('update', $privileges)) disabled @endif>Submit Konfirmasi Pengiriman</button>
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
    <script src="{{ URL::asset('build/plugins/datatable/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/plugins/datatable/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
    $(document).ready(function() {
			var table = $('#example').DataTable();
		} );

var now = new Date(),
maxDate = now.toISOString().substring(0,10);
$('#document_date').prop('max', maxDate);



</script>
@endsection

@endsection
