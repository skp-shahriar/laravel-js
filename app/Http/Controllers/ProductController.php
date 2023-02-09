<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if (!empty($request->all()) && !$request->has('page')) {
            $filterData['product_title'] = trim($request->query('product_title'));
            $filterData['variant'] = trim($request->query('variant'));
            $filterData['price_from'] = trim($request->query('price_from'));
            $filterData['price_to'] = trim($request->query('price_to'));
            $filterData['date'] = trim($request->query('date'));
            $filterData['filtering'] = true;

            $variantsRaw = Variant::with('productVariant')->get()->toArray();
            $variants = array_map(function ($v) {
                $item['name'] = $v['title'];
                $variant_array = [];
                foreach ($v['product_variant'] as $pv) {
                    if (!in_array($pv['variant'], $variant_array)) {
                        array_push($variant_array, $pv['variant']);
                    }
                }
                $item['children'] = array_values(array_unique($variant_array));
                return $item;
            }, $variantsRaw);

            $query = Product::query();
            if (!empty($filterData['product_title'])) {
                $query->where('products.title', 'like', '%' . $filterData['product_title'] . '%');
            }
            if (!empty($filterData['date'])) {
                $query->whereBetween('products.created_at', [$filterData['date'] . ' 00:00:00', $filterData['date'] . ' 23:59:59']);
            }
            $query->with(['product_variant_prices' => function ($query) use ($filterData) {
                if (!empty($filterData['variant'])) {
                    $variantsId = ProductVariant::where('variant', $filterData['variant'])->pluck('id');
                    $query->where(function ($w) use ($variantsId) {
                        $w->whereIn('product_variant_one', $variantsId);
                        $w->orWhereIn('product_variant_two', $variantsId);
                        $w->orWhereIn('product_variant_three', $variantsId);
                    });
                }

                if (!empty($filterData['price_from'])) {
                    $query->where('price', '>=', $filterData['price_from']);
                }
                if (!empty($filterData['price_to'])) {
                    $query->where('price', '<=', $filterData['price_to']);
                }
            }]);
            $products = $query->get();
            $products = $products->filter(function ($item) {
                return count($item->product_variant_prices) > 0;
            });

            return view('products.index', compact('variants', 'products', 'filterData'));
        } else {
            $filterData['product_title'] = '';
            $filterData['variant'] = '';
            $filterData['price_from'] = '';
            $filterData['price_to'] = '';
            $filterData['date'] = '';
            $filterData['filtering'] = false;

            $variantsRaw = Variant::with('productVariant')->get()->toArray();
            $variants = array_map(function ($v) {
                $item['name'] = $v['title'];
                $variant_array = [];
                foreach ($v['product_variant'] as $pv) {
                    if (!in_array($pv['variant'], $variant_array)) {
                        array_push($variant_array, $pv['variant']);
                    }
                }
                $item['children'] = array_values(array_unique($variant_array));
                return $item;
            }, $variantsRaw);
            $products = Product::with('product_variant_prices')->paginate(3);
            return view('products.index', compact('variants', 'products', 'filterData'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // dd($request->all());
        try {
            $product = Product::create([
                'title' => $request->product_name,
                'sku' => $request->product_sku,
                'description' => $request->product_description
            ]);

            foreach ($request->product_variant as $variants) {
                foreach ($variants['value'] as $value) {
                    ProductVariant::create([
                        'variant' => strtolower($value),
                        'variant_id' => $variants['option'],
                        'product_id' => $product->id,
                    ]);
                }
            }

            foreach ($request->product_preview as $vp) {
                $variants = explode('/', $vp['variant']);
                $variants = array_filter($variants);
                $pvs = [];
                foreach ($variants as $variant) {
                    $product_variant_id = ProductVariant::where('variant', strtolower($variant))->where('product_id', $product->id)->first()->id;
                    array_push($pvs, $product_variant_id);
                }

                ProductVariantPrice::create([
                    'product_variant_one' => $pvs[0] ?? null,
                    'product_variant_two' => $pvs[1] ?? null,
                    'product_variant_three' => $pvs[2] ?? null,
                    'product_id' => $product->id,
                    'price' => $vp['price'] ?? '',
                    'stock' => $vp['stock'] ?? ''
                ]);
            }

            return redirect()->route('product.index')->withMessage('Product Created Successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withValue()->withErrors($e->getMessage());
        }
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $color = ProductVariant::where('variant_id', 1)->where('product_id', $product->id)->distinct()->pluck('variant');
        $size = ProductVariant::where('variant_id', 2)->where('product_id', $product->id)->distinct()->pluck('variant');
        $style = ProductVariant::where('variant_id', 3)->where('product_id', $product->id)->distinct()->pluck('variant');

        if ($color->count() > 0) {
            $vep[1] = $color;
        }
        if ($size->count() > 0) {
            $vep[2] = $size;
        }
        if ($style->count() > 0) {
            $vep[3] = $style;
        }
        $product = $product;
        $variants = Variant::all();
        return view('products.edit', compact('variants', 'product', 'vep'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $product->title = $request->product_name;
        $product->sku = $request->product_sku;
        $product->description = $request->product_description;

        $product->update();

        foreach ($request->product_preview as $preview) {
            $pvp = ProductVariantPrice::find($preview['id']);
            $variants = explode(' / ', $preview['variant']);
            $va = [];
            foreach ($variants as $index => $variant) {
                $pv = ProductVariant::where('variant', $variant)->where('product_id', $product->id)->first();
                $va[$index] = $pv->id;
            }
            $pvp->update([
                'product_variant_one' => $va[0] ?? null,
                'product_variant_two' => $va[1] ?? null,
                'product_variant_three' => $va[2] ?? null,
                'product_id' => $product['id'],
                'price' => $preview['price'] ?? null,
                'stock' => $preview['stock'] ?? null

            ]);
        }
        return redirect()->route('product.index')->withMessage("Product updated successfully");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
