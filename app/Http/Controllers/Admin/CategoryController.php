<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Categories/Index', [
            'categories' => Category::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create([
            ...$request->validated(),
            'slug' => Str::slug($request->string('slug')),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoria criada.');
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Edit', [
            'category' => $category,
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update([
            ...$request->validated(),
            'slug' => Str::slug($request->string('slug')),
        ]);

        return redirect()->route('admin.categories.index')->with('success', 'Categoria atualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $category->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'category' => 'Esta categoria possui produtos vinculados e não pode ser excluída.',
            ]);
        }

        return back()->with('success', 'Categoria removida.');
    }
}
