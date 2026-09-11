import { Head, Link } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatPrice } from '@/lib/money';
import { AuthUser } from '@/types';
import { Order } from '@/types/models';

interface Props {
    customer: AuthUser & { orders_count: number; created_at: string };
    orders: Order[];
}

const STATUS_LABELS: Record<string, string> = {
    PENDING: 'Aguardando pagamento',
    PAID: 'Pago',
    PROCESSING: 'Em processamento',
    SHIPPED: 'Enviado',
    DELIVERED: 'Entregue',
    CANCELED: 'Cancelado',
};

export default function Show({ customer, orders }: Props) {
    return (
        <AdminLayout>
            <Head title={customer.name} />

            <h1 className="mb-1 text-2xl font-semibold">{customer.name}</h1>
            <p className="mb-6 text-slate-500">{customer.email}</p>

            <div className="rounded-lg bg-white ring-1 ring-slate-100">
                <div className="border-b border-slate-100 p-4 font-medium">
                    Pedidos ({customer.orders_count})
                </div>

                {orders.length === 0 ? (
                    <p className="p-4 text-sm text-slate-500">Nenhum pedido ainda.</p>
                ) : (
                    <ul className="divide-y divide-slate-100">
                        {orders.map((order) => (
                            <li key={order.uuid} className="flex items-center justify-between p-4 text-sm">
                                <div>
                                    <Link href={`/admin/pedidos/${order.uuid}`} className="font-medium hover:underline">
                                        Pedido #{order.uuid.slice(0, 8).toUpperCase()}
                                    </Link>
                                    <p className="text-slate-400">
                                        {new Date(order.placed_at).toLocaleDateString('pt-BR')}
                                    </p>
                                </div>
                                <div className="flex items-center gap-4">
                                    <span className="rounded-full bg-slate-100 px-2 py-0.5 text-xs">
                                        {STATUS_LABELS[order.status] ?? order.status}
                                    </span>
                                    <span className="font-medium">{formatPrice(order.total)}</span>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AdminLayout>
    );
}
