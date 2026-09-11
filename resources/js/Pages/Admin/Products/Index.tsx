import { Head, Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatPrice } from '@/lib/money';
import { Paginated, Product } from '@/types/models';

interface Props {
    products: Paginated<Product & { category: { name: string } }>;
    filters: { q?: string };
}

export default function Index({ products, filters }: Props) {
    const [q, setQ] = useState(filters.q ?? '');

    function search(e: FormEvent) {
        e.preventDefault();
        router.get('/admin/products', q ? { q } : {}, { preserveState: true });
    }

    function destroy(product: Product) {
        if (! confirm(`Excluir o produto "${product.title}"?`)) return;
        router.delete(`/admin/products/${product.id}`);
    }

    return (
        <AdminLayout>
            <Head title="Produtos" />

            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Produtos</h1>
                <Link href="/admin/products/create" className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">
                    Novo produto
                </Link>
            </div>

            <form onSubmit={search} className="mb-4">
                <input
                    className="input max-w-xs"
                    placeholder="Buscar por título..."
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                />
            </form>

            <table className="w-full overflow-hidden rounded-lg bg-white text-sm ring-1 ring-slate-100">
                <thead className="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th className="px-4 py-3">Título</th>
                        <th className="px-4 py-3">Categoria</th>
                        <th className="px-4 py-3">Preço</th>
                        <th className="px-4 py-3">Estoque</th>
                        <th className="px-4 py-3">Status</th>
                        <th className="px-4 py-3" />
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {products.data.map((product) => (
                        <tr key={product.id}>
                            <td className="px-4 py-3">{product.title}</td>
                            <td className="px-4 py-3">{product.category.name}</td>
                            <td className="px-4 py-3">{formatPrice(product.price)}</td>
                            <td className="px-4 py-3">{product.stock}</td>
                            <td className="px-4 py-3">
                                {product.active ? 'Ativo' : 'Inativo'}
                                {product.featured ? ' · Destaque' : ''}
                            </td>
                            <td className="px-4 py-3 text-right">
                                <Link
                                    href={`/admin/products/${product.id}/edit`}
                                    className="mr-4 text-slate-600 hover:underline"
                                >
                                    Editar
                                </Link>
                                <button onClick={() => destroy(product)} className="text-red-600 hover:underline">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <div className="mt-6 flex flex-wrap justify-center gap-1">
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
        </AdminLayout>
    );
}
