<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;


class ProductController extends Controller
{
    use HttpResponses;

    // normal user methods


    public function index()
    {
        return ProductResource::collection(Product::all());
        
    } 
    public function show($id)
{
    $product = Product::find($id);
    if (!$product) {
        return $this->error("", 'Product does not exist', 404);
    }
    return new ProductResource($product);
}

   // to get the discounted products only
    public function discounted(){
        $products = Product::where('discounted', true)->get();
        if ($products->isEmpty()) {
            return $this->error("", 'No discounted products found.', 404);
        }
        return ProductResource::collection($products);
    }
    // to get products by category
    public function category($categoryName)
    {
        $category = Category::where('name', $categoryName)->first();

        if (!$category) {
            return $this->error("", 'Category not found', 404);
        }
        $products = $category->products;
        return ProductResource::collection($products);
    }


    // dealer user methods


    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();
    
        $category = Category::where('name', $validatedData['category_name'])->first();
            if (!$category) {
            return $this->error("", 'Category not found', 404);
        }
            $productData = [
            'name' => $validatedData['name'],
            'price' => $validatedData['price'],
            'description' => $validatedData['description'],
            'brand' => $validatedData['brand_name'],
            'discounted' => $validatedData['discounted'] ?? false,
            'category_id' => $category->id,
            'user_id' => Auth::id(),
        ];
    
        $product = Product::create($productData);
        
        return new ProductResource($product);
    }
    
    public function update(Request $request, Product $product)
{

    // Find the category by name
    if ($request->has('category_name')) {
        $category = Category::where('name', $request->input('category_name'))->first();

        if (!$category) {
            return $this->error("", 'Category not found', 404);
        }
        $product->category_id = $category->id;
    }
    $product->update(array_merge($request->except('category_name'), ['user_id' => auth()->id()]));
    return new ProductResource($product);
}

public function destroy($id)
{
    $product = Product::find($id);
    if (!$product) {
        return $this->error("", 'Product does not exist', 404);
    }
    $product->delete();
    return $this->success("", 'The Product has been deleted');
} 
}
