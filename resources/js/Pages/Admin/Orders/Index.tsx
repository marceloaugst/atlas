import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatPrice } from '@/lib/money';
import { Order, Paginated } from '@/types/models';

interface Props {
    orders: Paginated<Order>;
    filters: { status?: string };
}

const STATUSES = ['PENDING', 'PAID', 'PROCESSING', 'SHIPPED', 'DELIVERED', 'CANCELED'];

const STATUS_LABELS: Record<string, string> = {
    PENDING: 'Aguardando pagamento',
    PAID: 'Pago',
    PROCESSING: 'Em processamento',
    SHIPPED: 'Enviado',
    DELIVERED: 'Entregue',
    CANCELED: 'Cancelado',
};

export default function Index({ orders, filters }: Props) {
    function filterByStatus(status: string | null) {
        router.get('/admin/pedidos', status ? { status } : {}, { preserveState: true });
    }

    return (
        <AdminLayout>
            <Head title="Pedidos" />

            <h1 className="mb-6 text-2xl font-semibold">Pedidos</h1>

            <div className="mb-4 flex flex-wrap gap-2">
                <button
                    onClick={() => filterByStatus(null)}
                    className={`rounded-full px-3 py-1 text-sm ${
                        !filters.status ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 ring-1 ring-slate-200'
                    }`}
                >
                    Todos
                </button>
                {STATUSES.map((status) => (
                    <button
                        key={status}
                        onClick={() => filterByStatus(status)}
                        className={`rounded-full px-3 py-1 text-sm ${
                            filters.status === status
                                ? 'bg-slate-900 text-white'
                                : 'bg-white text-slate-600 ring-1 ring-slate-200'
                        }`}
                    >
                        {STATUS_LABELS[status]}
                    </button>
                ))}
            </div>

            <table className="w-full overflow-hidden rounded-lg bg-white text-sm ring-1 ring-slate-100">
                <thead className="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th className="px-4 py-3">Pedido</th>
                        <th className="px-4 py-3">Cliente</th>
                        <th className="px-4 py-3">Data</th>
                        <th className="px-4 py-3">Status</th>
                        <th className="px-4 py-3">Total</th>
                        <th className="px-4 py-3" />
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {orders.data.map((order) => (
                        <tr key={order.uuid}>
                            <td className="px-4 py-3 font-mono text-xs">{order.uuid.slice(0, 8).toUpperCase()}</td>
                            <td className="px-4 py-3">
                                {order.customer_name}
                                <br />
                                <span className="text-xs text-slate-400">{order.customer_email}</span>
                            </td>
                            <td className="px-4 py-3">{new Date(order.placed_at).toLocaleDateString('pt-BR')}</td>
                            <td className="px-4 py-3">
                                <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs">
                                    {STATUS_LABELS[order.status] ?? order.status}
                                </span>
                            </td>
                            <td className="px-4 py-3">{formatPrice(order.total)}</td>
                            <td className="px-4 py-3 text-right">
                                <Link href={`/admin/pedidos/${order.uuid}`} className="text-slate-600 hover:underline">
                                    Ver
                                </Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>

            <div className="mt-6 flex flex-wrap justify-center gap-1">
                {orders.links.map((link, index) => (
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
