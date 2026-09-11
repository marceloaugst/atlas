import { Head, Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { AuthUser } from '@/types';
import { Paginated } from '@/types/models';

interface Props {
    customers: Paginated<AuthUser & { orders_count: number; created_at: string }>;
    filters: { q?: string };
}

export default function Index({ customers, filters }: Props) {
    const [q, setQ] = useState(filters.q ?? '');

    function search(e: FormEvent) {
        e.preventDefault();
        router.get('/admin/clientes', q ? { q } : {}, { preserveState: true });
    }

    return (
        <AdminLayout>
            <Head title="Clientes" />

            <h1 className="mb-6 text-2xl font-semibold">Clientes</h1>

            <form onSubmit={search} className="mb-4">
                <input
                    className="input max-w-xs"
                    placeholder="Buscar por nome ou e-mail..."
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                />
            </form>

            <table className="w-full overflow-hidden rounded-lg bg-white text-sm ring-1 ring-slate-100">
                <thead className="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th className="px-4 py-3">Nome</th>
                        <th className="px-4 py-3">E-mail</th>
                        <th className="px-4 py-3">Pedidos</th>
                        <th className="px-4 py-3">Cadastro</th>
                        <th className="px-4 py-3" />
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {customers.data.map((customer) => (
                        <tr key={customer.id}>
                            <td className="px-4 py-3">{customer.name}</td>
                            <td className="px-4 py-3">{customer.email}</td>
                            <td className="px-4 py-3">{customer.orders_count}</td>
                            <td className="px-4 py-3">{new Date(customer.created_at).toLocaleDateString('pt-BR')}</td>
                            <td className="px-4 py-3 text-right">
                                <Link href={`/admin/clientes/${customer.id}`} className="text-slate-600 hover:underline">
                                    Ver
                                </Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <div className="mt-6 flex flex-wrap justify-center gap-1">
                {customers.links.map((link, index) => (
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
