@extends('layouts.app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Edit Product</h1>
</div>
<form action="{{ route('product.update', $product) }}" method="post">
    @method('put')
    @csrf
    <section>
        <div class="row">
            <div class="col-md-6">
                <!--                    Product-->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Product</h6>
                    </div>
                    <div class="card-body border">
                        <div class="form-group">
                            <label for="product_name">Product Name</label>
                            <input type="text" name="product_name" id="product_name" required placeholder="Product Name"
                                class="form-control" value={{ $product->title }}>
                        </div>
                        <div class="form-group">
                            <label for="product_sku">Product SKU</label>
                            <input type="text" name="product_sku" id="product_sku" required placeholder="Product Name"
                                class="form-control" value={{ $product->sku }}>
                        </div>
                        <div class="form-group mb-0">
                            <label for="product_description">Description</label>
                            <textarea name="product_description" id="product_description" required rows="4"
                                class="form-control">{{ $product->description }}</textarea>
                        </div>
                    </div>
                </div>
                <!--                    Media-->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Media</h6>
                    </div>
                    <div class="card-body border">
                        <div id="file-upload" class="dropzone dz-clickable">
                            <div class="dz-default dz-message"><span>Drop files here to upload</span></div>
                        </div>
                    </div>
                </div>
            </div>
            <!--                Variants-->
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Variants</h6>
                    </div>
                    <div class="card-body pb-0" id="variant-sections">
                        <!-- data population -->
                        @php $sl = 0; @endphp
                        @foreach($vep as $id => $values)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="">Option</label>
                                    <select id="select2-option-{{ $sl }}" data-index="{{ $sl }}"
                                        name="product_variant[{{ $sl }}][option]"
                                        class="form-control custom-select select2 select2-option">
                                        <option value="1" {{ $id==1 ? 'selected' : '' }}>
                                            Color
                                        </option>
                                        <option value="2" {{ $id==2 ? 'selected' : '' }}>
                                            Size
                                        </option>
                                        <option value="6" {{ $id==3 ? 'selected' : '' }}>
                                            Style
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="d-flex justify-content-between">
                                        <span>Value</span>
                                        <a href="#" class="remove-btn" data-index="{{ $sl }}"
                                            onclick="removeVariant(event, this);">Remove</a>
                                    </label>
                                    <select id="select2-value-{{ $sl }}" data-index="{{ $sl }}"
                                        name="product_variant[{{ $sl }}][value][]"
                                        class="select2 select2-value form-control custom-select" multiple="multiple">
                                        @foreach($values as $value)
                                        <option value="{{ $value }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @php $sl++; @endphp
                        @endforeach
                        <!-- data population -->
                    </div>
                    <div class="card-footer bg-white border-top-0" id="add-btn">
                        <div class="row d-flex justify-content-center">
                            <button class="btn btn-primary add-btn" onclick="addVariant(event);">
                                Add another option
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card shadow">
                    <div class="card-header text-uppercase">Preview</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="text-center">
                                        <th width="33%">Variant</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                    </tr>
                                </thead>
                                <tbody id="variant-previews">
                                    @php $i=0; @endphp
                                    @forelse($product->product_variant_prices as $product_variant_with_price)
                                    <input type="hidden" name="product_preview[{{ $i }}][id]"
                                        value="{{ $product_variant_with_price['id'] }}">
                                    <tr>
                                        <td>
                                            <input type="text" name="product_preview[{{ $i }}][variant]"
                                                value="{{ $product_variant_with_price->product_variant_two_details ? strtoupper($product_variant_with_price->product_variant_two_details->variant) : '' }} {{ $product_variant_with_price->product_variant_two_details && $product_variant_with_price->product_variant_one_details ? '/ ' : '' }}{{ $product_variant_with_price->product_variant_one_details ? ucfirst($product_variant_with_price->product_variant_one_details->variant) : '' }} {{ $product_variant_with_price->product_variant_one_details && $product_variant_with_price->product_variant_three_details ? '/ ' : '' }}{{ $product_variant_with_price->product_variant_three_details ? $product_variant_with_price->product_variant_three_details->variant : '' }}">

                                        </td>
                                        <td><input type="text" name="product_preview[{{ $i }}][price]"
                                                value="{{ $product_variant_with_price['price'] }}"></td>
                                        <td><input type="text" name="product_preview[{{ $i }}][stock]"
                                                value="{{ $product_variant_with_price['stock'] }}"></td>
                                    </tr>
                                    @php $i++; @endphp
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-lg btn-primary" style="color: black;">Save</button>
        <button type="button" class="btn btn-secondary btn-lg" style="color: black;">Cancel</button>
    </section>
</form>
<script>
    var chec = document.getElementById('variant-previews');
        // console.log(chec);
</script>
@endsection

@push('page_js')
<script type="text/javascript" src="{{ asset('js/product.js') }}"></script>
@endpush