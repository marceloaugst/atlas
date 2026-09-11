<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\GoogleBooksException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportGoogleBookRequest;
use App\Models\Category;
use App\Services\GoogleBooks\GoogleBooksService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GoogleBooksController extends Controller
{
    public function __construct(private readonly GoogleBooksService $googleBooks) {}

    public function index(Request $request): Response
    {
        $query = $request->string('q')->toString();
        $books = [];
        $error = null;

        if ($query !== '') {
            try {
                $books = $this->googleBooks->search($query);
            } catch (GoogleBooksException $e) {
                $error = $e->getMessage();
            }
        }

        $importedIds = $books
            ? $this->googleBooks->importedGoogleBooksIds(array_map(fn ($book) => $book->googleBooksId, $books))
            : [];

        return Inertia::render('Admin/GoogleBooks/Index', [
            'query' => $query,
            'books' => $books,
            'importedIds' => $importedIds,
            'error' => $error,
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(ImportGoogleBookRequest $request): RedirectResponse
    {
        try {
            $product = $this->googleBooks->importById(
                googleBooksId: $request->string('google_books_id')->toString(),
                categoryId: $request->integer('category_id'),
                price: (int) round($request->float('price') * 100),
                stock: $request->integer('stock'),
            );
        } catch (GoogleBooksException $e) {
            return back()->withErrors(['import' => $e->getMessage()]);
        }

        return redirect()->route('admin.products.edit', $product)->with('success', 'Livro importado com sucesso.');
    }
}
