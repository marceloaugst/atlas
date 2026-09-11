<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with(['category', 'authors'])
            ->active()
            ->when($request->string('category')->isNotEmpty(), function ($query) use ($request) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $request->string('category')));
            })
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->string('q').'%');
            })
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Catalog/Index', [
            'products' => $products,
            'categories' => Category::where('active', true)->orderBy('name')->get(),
            'filters' => $request->only(['category', 'q']),
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load(['category', 'authors']);

        return Inertia::render('Catalog/Show', [
            'product' => $product,
        ]);
    }
}
