<?php

namespace App\Http\Controllers\API;

use App\Helpers\ProductHelper;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Validator;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::all();
        return $this->sendResponse(ProductResource::collection($products), 'Products retrieved successfully.');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'base_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'sell_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'quantity' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error,', $validator->errors());
        }
        do {
            $slug = ProductHelper::createSlug($input['name']);
        } while(ProductHelper::checkSlugExists($slug));
        do {
            $ean = ProductHelper::createEAN();
        } while(ProductHelper::checkEANExists($ean));
        $product = Product::create($input + ['slug' => $slug, 'ean' => $ean]);
        return $this->sendResponse(new ProductResource($product), 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)->first();
        if (is_null($product)) {
            return $this->sendError('Product not found.');
        }
        return $this->sendResponse(new ProductResource($product), 'Product retrieved successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $slug)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'ean' => 'required',
            'base_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'sell_price' => 'required|regex:/^\d+(\.\d{1,2})?$/',
            'quantity' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error,', $validator->errors());
        }
        $product = Product::where([
            ['slug', '=', $slug],
            ['ean', '=', $input['ean']],
        ])->first();
        if ($product->name != $input['name']) {
            $new_slug = ProductHelper::createSlug($input['name']);
            $product->name = $input['name'];
            $product->slug = $new_slug;
        }
        $product->base_price = $input['base_price'];
        $product->sell_price = $input['sell_price'];
        $product->quantity = $input['quantity'];
        $product->save();
        return $this->sendResponse(new ProductResource($product), 'Product updated successfully.');
    }

    /**
     * Update the quantity in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function updateQuantity(Request $request, string $slug)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'ean' => 'required',
            'quantity' => 'required|numeric|min:0',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Validation Error,', $validator->errors());
        }
        $product = Product::where([
            ['slug', '=', $slug],
            ['ean', '=', $input['ean']],
        ])->first();
        $product->quantity = $input['quantity'];
        $product->save();
        return $this->sendResponse(new ProductResource($product), 'Product quantity updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param string $slug
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $slug)
    {
        $affectedRow = Product::where('slug', $slug)->delete();
        if ($affectedRow) {
            return $this->sendResponse('', 'Product deleted successfully.');
        } else {
            return $this->sendError('', 'No product found.');
        }
    }
}
