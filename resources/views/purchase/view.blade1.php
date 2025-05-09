@extends('layouts.backend.layouts')

@section('page-content')
<!-- Wrapper Start -->
<div class="wrapper">

    <div class="content-page">
        <div class="container-fluid add-form-list">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <div class="header-title">
                                <h4 class="card-title">Purchase Order</h4>
                            </div>
                            <div>
                                <a href="{{ route('purchase.list') }}"
                                    class="btn btn-secondary">Back</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="card">
                                <h5><strong>Bill No:</strong> {{ $purchase->bill_no }}</h5>
                                <h5><strong>Party Name:</strong> {{ $purchase->vendor->name }}</h5>
                                <h5><strong>Date:</strong>
                                    {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') }}
                                </h5>

                                <hr>

                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Brand</th>
                                                <th>Batch</th>
                                                <th>MFG Date</th>
                                                <th>MRP</th>
                                                <th>Qty</th>
                                                <th>Rate</th>
                                                <th>Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <blade
                                                foreach|%20(%24purchase-%3EproductsItems%20as%20%24key%20%3D%3E%20%24product)>
                                                <tr>
                                                    <td>{{ $product->brand_name }}</td>
                                                    <td>{{ $product->batch }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($product->mfg_date)->format('m-Y') }}
                                                    </td>
                                                    <td>{{ number_format($product->mrp, 2) }}</td>
                                                    <td>{{ $product->qnt }}</td>
                                                    <td>{{ number_format($product->rate, 2) }}</td>
                                                    <td>{{ number_format($product->amount, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <hr>
                                <div class="row">
                                    <div class="col-md-6 offset-md-6">
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <th>Total</th>
                                                    <td>₹{{ number_format($purchase->total, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Excise Fee</th>
                                                    <td>₹{{ number_format($purchase->excise_fee, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Composition VAT</th>
                                                    <td>₹{{ number_format($purchase->composition_vat, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>Surcharge on CA</th>
                                                    <td>₹{{ number_format($purchase->surcharge_on_ca, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>TCS</th>
                                                    <td>₹{{ number_format($purchase->tcs, 2) }}</td>
                                                </tr>
                                                <tr>
                                                    <th>AED to be Paid</th>
                                                    <td>₹{{ number_format($purchase->aed_to_be_paid, 2) }}</td>
                                                </tr>
                                                <tr class="table-primary">
                                                    <th><strong>Total With Tax</strong></th>
                                                    <td><strong>₹{{ number_format($purchase->total_amount, 2) }}</strong>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page end -->
        </div>
    </div>
</div>


<!-- Wrapper End -->
@endsection
