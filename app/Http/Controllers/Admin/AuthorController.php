<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AuthorRequest;
use App\Models\Author;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuthorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Authors/Index', [
            'authors' => Author::withCount('products')->orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Authors/Create');
    }

    public function store(AuthorRequest $request): RedirectResponse
    {
        Author::create([
            ...$request->validated(),
            'slug' => Str::slug($request->string('slug')),
        ]);

        return redirect()->route('admin.authors.index')->with('success', 'Autor criado.');
    }

    public function edit(Author $author): Response
    {
        return Inertia::render('Admin/Authors/Edit', [
            'author' => $author,
        ]);
    }

    public function update(AuthorRequest $request, Author $author): RedirectResponse
    {
        $author->update([
            ...$request->validated(),
            'slug' => Str::slug($request->string('slug')),
        ]);

        return redirect()->route('admin.authors.index')->with('success', 'Autor atualizado.');
    }

    public function destroy(Author $author): RedirectResponse
    {
        try {
            $author->delete();
        } catch (QueryException) {
            return back()->withErrors([
                'author' => 'Este autor possui produtos vinculados e não pode ser excluído.',
            ]);
        }

        return back()->with('success', 'Autor removido.');
    }
}
