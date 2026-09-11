import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Author } from '@/types/models';

interface Props {
    authors: (Author & { products_count: number })[];
}

export default function Index({ authors }: Props) {
    function destroy(author: Author) {
        if (! confirm(`Excluir o autor "${author.name}"?`)) return;
        router.delete(`/admin/authors/${author.id}`);
    }

    return (
        <AdminLayout>
            <Head title="Autores" />

            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Autores</h1>
                <Link href="/admin/authors/create" className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">
                    Novo autor
                </Link>
            </div>

            <table className="w-full overflow-hidden rounded-lg bg-white text-sm ring-1 ring-slate-100">
                <thead className="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th className="px-4 py-3">Nome</th>
                        <th className="px-4 py-3">Produtos</th>
                        <th className="px-4 py-3" />
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {authors.map((author) => (
                        <tr key={author.id}>
                            <td className="px-4 py-3">{author.name}</td>
                            <td className="px-4 py-3">{author.products_count}</td>
                            <td className="px-4 py-3 text-right">
                                <Link
                                    href={`/admin/authors/${author.id}/edit`}
                                    className="mr-4 text-slate-600 hover:underline"
                                >
                                    Editar
                                </Link>
                                <button onClick={() => destroy(author)} className="text-red-600 hover:underline">
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
