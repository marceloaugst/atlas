import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { formatPrice } from '@/lib/money';
import { Category, Paginated, Product } from '@/types/models';

interface Props {
    products: Paginated<Product>;
    categories: Category[];
    filters: { category?: string; q?: string };
}

export default function Index({ products, categories, filters }: Props) {
    function filterByCategory(slug: string | null) {
        router.get('/', slug ? { category: slug } : {}, { preserveState: true });
    }

    return (
        <AppLayout>
            <Head title="Catálogo" />

            <div className="mb-8 flex flex-wrap gap-2">
                <button
                    onClick={() => filterByCategory(null)}
                    className={`rounded-full px-4 py-1.5 text-sm ${
                        !filters.category ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200'
                    }`}
                >
                    Todas
                </button>
                {categories.map((category) => (
                    <button
                        key={category.id}
                        onClick={() => filterByCategory(category.slug)}
                        className={`rounded-full px-4 py-1.5 text-sm ${
                            filters.category === category.slug
                                ? 'bg-slate-900 text-white'
                                : 'bg-white text-slate-600 ring-1 ring-slate-200'
                        }`}
                    >
                        {category.name}
                    </button>
                ))}
            </div>

            <div className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
                {products.data.map((product) => (
                    <Link
                        key={product.id}
                        href={`/livros/${product.slug}`}
                        className="group rounded-lg bg-white p-3 shadow-sm ring-1 ring-slate-100 transition hover:shadow-md"
                    >
                        <div className="aspect-[2/3] overflow-hidden rounded bg-slate-100">
                            {product.cover_url && (
                                <img
                                    src={product.cover_url}
                                    alt={product.title}
                                    className="h-full w-full object-cover transition group-hover:scale-105"
                                />
                            )}
                        </div>
                        <h3 className="mt-3 line-clamp-2 text-sm font-medium">{product.title}</h3>
                        <p className="text-xs text-slate-500">{product.authors.map((a) => a.name).join(', ')}</p>
                        <p className="mt-2 font-semibold">{formatPrice(product.price)}</p>
                    </Link>
                ))}
            </div>

            {products.data.length === 0 && (
                <p className="py-16 text-center text-slate-500">Nenhum livro encontrado.</p>
            )}

            <div className="mt-10 flex flex-wrap justify-center gap-1">
                {products.links.map((link, index) => (
                    <button
                        key={index}
                        disabled={!link.url}
                        onClick={() => link.url && router.visit(link.url, { preserveState: true })}
                        className={`rounded px-3 py-1.5 text-sm ${
                            link.active
                                ? 'bg-slate-900 text-white'
                                : 'bg-white text-slate-600 ring-1 ring-slate-200 disabled:opacity-40'
                        }`}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </div>
        </AppLayout>
    );
}
