<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Author;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with('category')
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request) {
                $query->where('title', 'like', '%'.$request->string('q').'%');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'filters' => $request->only(['q']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $product = Product::create([
            ...$request->safe()->except(['price', 'author_ids']),
            'slug' => Str::slug($request->string('slug')),
            'price' => (int) round($request->float('price') * 100),
        ]);

        $product->authors()->sync($request->input('author_ids', []));

        return redirect()->route('admin.products.index')->with('success', 'Produto criado.');
    }

    public function edit(Product $product): Response
    {
        $product->load('authors');

        return Inertia::render('Admin/Products/Edit', [
            'product' => [
                ...$product->toArray(),
                'price' => $product->price / 100,
                'author_ids' => $product->authors->pluck('id'),
            ],
            'categories' => Category::orderBy('name')->get(),
            'authors' => Author::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $product->update([
            ...$request->safe()->except(['price', 'author_ids']),
            'slug' => Str::slug($request->string('slug')),
            'price' => (int) round($request->float('price') * 100),
        ]);

        $product->authors()->sync($request->input('author_ids', []));

        return redirect()->route('admin.products.index')->with('success', 'Produto atualizado.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        try {
            $product->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'product' => 'Este produto já possui pedidos e não pode ser excluído. Desative-o em vez disso.',
            ]);
        }

        return back()->with('success', 'Produto removido.');
    }
}
