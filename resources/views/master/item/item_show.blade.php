@extends('layouts.master')

@section('title', 'Lihat Jasa')
@section('css')
<style>
    .form-group {
        margin-bottom: 1rem; /* Adjust the spacing */
    }
</style>
@endsection

@section('content')
<x-page-title title="Master" pagetitle="Lihat Jasa" />
<hr>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-2 text-uppercase">Detail Jasa</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="item_code" class="form-label">Kode Jasa</label>
                        <input type="text" id="item_code" name="item_code" class="form-control" value="{{ $item->item_code }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="item_name" class="form-label">Nama Jasa</label>
                        <input type="text" id="item_name" name="item_name" class="form-control" value="{{ $item->item_name }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="item_category" class="form-label">Kategori Jasa</label>
                        <input type="text" id="item_category" name="item_category" class="form-control" value="{{ $item->category->item_category_name }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="base_unit" class="form-label">Unit Dasar</label>
                        <input type="text" id="base_unit" name="base_unit" class="form-control" value="{{ $item->baseUnits->unit_name }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="sales_unit" class="form-label">Unit Jual</label>
                        <input type="text" id="sales_unit" name="sales_unit" class="form-control" value="{{ $item->salesUnit->unit_name }}" readonly>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="purchase_unit" class="form-label">Unit Beli</label>
                        <input type="text" id="purchase_unit" name="purchase_unit" class="form-control" value="{{ $item->purchaseUnit->unit_name }}" readonly>
                    </div>
                </div>
                <br>
                <h6 class="mb-2 text-uppercase">Detail Item</h6>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Unit Konversi</th>
                            <th>Konversi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemDetails as $detail)
                        <tr>
                            <td>{{ $detail->unitConversion->unit_name }}</td>
                            <td>{{ $detail->conversion }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
