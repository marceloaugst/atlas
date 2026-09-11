import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { formatPrice } from '@/lib/money';
import { Order, Paginated } from '@/types/models';

interface Props {
    orders: Paginated<Order>;
}

const STATUS_LABELS: Record<string, string> = {
    PENDING: 'Aguardando pagamento',
    PAID: 'Pago',
    PROCESSING: 'Em processamento',
    SHIPPED: 'Enviado',
    DELIVERED: 'Entregue',
    CANCELED: 'Cancelado',
};

export default function Orders({ orders }: Props) {
    return (
        <AppLayout>
            <Head title="Meus pedidos" />

            <h1 className="mb-6 text-2xl font-semibold">Meus pedidos</h1>

            {orders.data.length === 0 ? (
                <p className="text-slate-500">
                    Você ainda não fez nenhum pedido.{' '}
                    <Link href="/" className="underline">
                        Ver catálogo
                    </Link>
                </p>
            ) : (
                <ul className="divide-y divide-slate-200 rounded-lg bg-white ring-1 ring-slate-100">
                    {orders.data.map((order) => (
                        <li key={order.uuid} className="flex items-center justify-between p-4">
                            <div>
                                <Link href={`/pedidos/${order.uuid}`} className="font-medium hover:underline">
                                    Pedido #{order.uuid.slice(0, 8).toUpperCase()}
                                </Link>
                                <p className="text-sm text-slate-500">
                                    {new Date(order.placed_at).toLocaleDateString('pt-BR')} — {order.items_count}{' '}
                                    {order.items_count === 1 ? 'item' : 'itens'}
                                </p>
                            </div>

                            <div className="flex items-center gap-4">
                                <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">
                                    {STATUS_LABELS[order.status] ?? order.status}
                                </span>
                                <span className="font-medium">{formatPrice(order.total)}</span>
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            <div className="mt-8 flex flex-wrap justify-center gap-1">
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
        </AppLayout>
    );
}
