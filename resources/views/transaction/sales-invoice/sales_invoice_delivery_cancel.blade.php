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
    <x-page-title title="{{__('Sales Invoice')}}" pagetitle="{{__('Sales Invoice')}}" />
    <hr>
    <div class="container content">
        <h2>{{__('Sales Invoice')}}</h2>
        <form id="bank-cash-out-form" action="{{ route('transaction.sales_invoice.update_status_cancel')}}" method="POST">
            @csrf

            <div class="card mb-3">
                <div class="card-header">Pembatalan Konfirmasi Pengiriman {{__('Sales Invoice')}}</div>
                <div class="card-body">
                    <input type="hidden" id="count_rows" name="count_rows" value="{{old('count_rows',0)}}">
                    <div style="overflow-x: auto;">

                        <br>
                        <div class="responsive">


                    <table class="table mt-2" id="example">
                        <thead>
                            <tr>
                                <th style="min-width: 150px">Cek</th>
                                <th style="min-width: 430px">Nomor Faktur</th>
                                <th style="min-width: 150px">Pelanggan</th>
                                <th style="min-width: 200px">Tanggal Faktur</th>
                            </tr>
                        </thead>
                        <tbody id="itemRows">
                            @foreach ($salesInvoices as $index => $si)
                                <tr data-row-id="0">
                                    <td>
                                        {{-- <input type="checkbox" name="" id=""> --}}
                                        <input class="form-check-input me-4" type="checkbox" name="details[{{$index}}][check]" id="check_{{$index}}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" name="details[{{$index}}][sales_invoice_number]" id=""
                                        value="{{$si->sales_invoice_number}}" readonly>
                                    </td>
                                    <td>
                                        {{$si->customers->customer_name}}
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($si->document_date)->format('d M Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <h6 class="mt-3 mb-5 text-danger font-weight-bold">Catatan : Jika anda tidak menemukan nomor faktur, berarti faktur tersebut telah memiliki pelunasan ,tanda terima atau daftar tagihan</h6>
                </div>
            </div>

            <div class="form-group submit-btn mb-3">
                <button type="submit" class="btn btn-success" @if(!in_array('create', $privileges)) disabled @endif>Submit Pembatalan Konfirmasi Pengiriman</button>
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
