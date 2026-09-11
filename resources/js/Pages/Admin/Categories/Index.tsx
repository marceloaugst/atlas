import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Category } from '@/types/models';

interface Props {
    categories: (Category & { products_count: number })[];
}

export default function Index({ categories }: Props) {
    function destroy(category: Category) {
        if (! confirm(`Excluir a categoria "${category.name}"?`)) return;
        router.delete(`/admin/categories/${category.id}`);
    }

    return (
        <AdminLayout>
            <Head title="Categorias" />

            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Categorias</h1>
                <Link href="/admin/categories/create" className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">
                    Nova categoria
                </Link>
            </div>

            <table className="w-full overflow-hidden rounded-lg bg-white text-sm ring-1 ring-slate-100">
                <thead className="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th className="px-4 py-3">Nome</th>
                        <th className="px-4 py-3">Produtos</th>
                        <th className="px-4 py-3">Ativa</th>
                        <th className="px-4 py-3" />
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {categories.map((category) => (
                        <tr key={category.id}>
                            <td className="px-4 py-3">{category.name}</td>
                            <td className="px-4 py-3">{category.products_count}</td>
                            <td className="px-4 py-3">{category.active ? 'Sim' : 'Não'}</td>
                            <td className="px-4 py-3 text-right">
                                <Link
                                    href={`/admin/categories/${category.id}/edit`}
                                    className="mr-4 text-slate-600 hover:underline"
                                >
                                    Editar
                                </Link>
                                <button onClick={() => destroy(category)} className="text-red-600 hover:underline">
                                    Excluir
                                </button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </AdminLayout>
    );
}
