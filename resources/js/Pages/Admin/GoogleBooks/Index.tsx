import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Category, GoogleBook } from '@/types/models';

interface Props {
    query: string;
    books: GoogleBook[];
    importedIds: string[];
    error: string | null;
    categories: Category[];
}

export default function Index({ query, books, importedIds, error, categories }: Props) {
    const [q, setQ] = useState(query);
    const [importing, setImporting] = useState<string | null>(null);
    const errors = usePage().props.errors as Record<string, string>;

    function search(e: FormEvent) {
        e.preventDefault();
        router.get('/admin/importar-livros', q ? { q } : {}, { preserveState: true });
    }

    return (
        <AdminLayout>
            <Head title="Importar da Google Books" />

            <h1 className="mb-6 text-2xl font-semibold">Importar da Google Books</h1>

            <form onSubmit={search} className="mb-6 flex gap-2">
                <input
                    className="input max-w-md"
                    placeholder="Buscar por título, autor ou ISBN..."
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                />
                <button type="submit" className="rounded-lg bg-slate-900 px-5 py-2 text-sm text-white">
                    Buscar
                </button>
            </form>

            {error && <p className="mb-4 text-sm text-red-600">{error}</p>}
            {errors.import && <p className="mb-4 text-sm text-red-600">{errors.import}</p>}

            {query && books.length === 0 && ! error && (
                <p className="text-sm text-slate-500">Nenhum resultado para "{query}".</p>
            )}

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                {books.map((book) => (
                    <BookCard
                        key={book.googleBooksId}
                        book={book}
                        imported={importedIds.includes(book.googleBooksId)}
                        categories={categories}
                        importing={importing === book.googleBooksId}
                        onStartImport={() => setImporting(book.googleBooksId)}
                        onCancelImport={() => setImporting(null)}
                    />
                ))}
            </div>
        </AdminLayout>
    );
}

function BookCard({
    book,
    imported,
    categories,
    importing,
    onStartImport,
    onCancelImport,
}: {
    book: GoogleBook;
    imported: boolean;
    categories: Category[];
    importing: boolean;
    onStartImport: () => void;
    onCancelImport: () => void;
}) {
    const { data, setData, post, processing } = useForm({
        google_books_id: book.googleBooksId,
        category_id: '',
        price: '',
        stock: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/admin/importar-livros', { onSuccess: onCancelImport });
    }

    return (
        <div className="flex gap-4 rounded-lg bg-white p-4 ring-1 ring-slate-100">
            <div className="h-28 w-20 flex-none overflow-hidden rounded bg-slate-100">
                {book.coverUrl && <img src={book.coverUrl} alt={book.title} className="h-full w-full object-cover" />}
            </div>

            <div className="min-w-0 flex-1">
                <h3 className="truncate text-sm font-medium">{book.title}</h3>
                <p className="truncate text-xs text-slate-500">{book.authors.join(', ') || 'Autor desconhecido'}</p>
                {book.publisher && <p className="text-xs text-slate-400">{book.publisher}</p>}
                {book.isbn && <p className="text-xs text-slate-400">ISBN {book.isbn}</p>}

                {imported ? (
                    <span className="mt-2 inline-block rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">
                        Já importado
                    </span>
                ) : importing ? (
                    <form onSubmit={submit} className="mt-2 space-y-2">
                        <select
                            className="input text-xs"
                            value={data.category_id}
                            onChange={(e) => setData('category_id', e.target.value)}
                            required
                        >
                            <option value="">Categoria</option>
                            {categories.map((category) => (
                                <option key={category.id} value={category.id}>
                                    {category.name}
                                </option>
                            ))}
                        </select>
                        <div className="flex gap-2">
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="Preço (R$)"
                                className="input text-xs"
                                value={data.price}
                                onChange={(e) => setData('price', e.target.value)}
                                required
                            />
                            <input
                                type="number"
                                min="0"
                                placeholder="Estoque"
                                className="input text-xs"
                                value={data.stock}
                                onChange={(e) => setData('stock', e.target.value)}
                                required
                            />
                        </div>
                        <div className="flex gap-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="rounded-lg bg-slate-900 px-3 py-1.5 text-xs text-white disabled:opacity-60"
                            >
                                Confirmar
                            </button>
                            <button
                                type="button"
                                onClick={onCancelImport}
                                className="rounded-lg bg-slate-100 px-3 py-1.5 text-xs"
                            >
                                Cancelar
                            </button>
                        </div>
                    </form>
                ) : (
                    <button
                        onClick={onStartImport}
                        className="mt-2 rounded-lg bg-slate-900 px-3 py-1.5 text-xs text-white"
                    >
                        Importar
                    </button>
                )}
            </div>
        </div>
    );
}
