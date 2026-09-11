import { Head, router, usePage } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatPrice } from '@/lib/money';
import { Order, OrderItem } from '@/types/models';

interface Props {
    order: Order & { items: OrderItem[] };
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

export default function Show({ order }: Props) {
    const errors = usePage().props.errors as Record<string, string>;

    function updateStatus(status: string) {
        if (status === 'CANCELED' && ! confirm('Cancelar este pedido e restaurar o estoque?')) return;
        router.patch(`/admin/pedidos/${order.uuid}/status`, { status });
    }

    return (
        <AdminLayout>
            <Head title={`Pedido #${order.uuid.slice(0, 8)}`} />

            <h1 className="mb-6 text-2xl font-semibold">Pedido #{order.uuid.slice(0, 8).toUpperCase()}</h1>

            {errors.status && <p className="mb-4 text-sm text-red-600">{errors.status}</p>}

            <div className="grid grid-cols-1 gap-6 lg:grid-cols-[1fr_300px]">
                <div className="rounded-lg bg-white p-5 ring-1 ring-slate-100">
                    <ul className="divide-y divide-slate-100">
                        {order.items.map((item) => (
                            <li key={item.id} className="flex justify-between py-2 text-sm">
                                <span>
                                    {item.quantity}x {item.product_title}
                                </span>
                                <span>{formatPrice(item.subtotal)}</span>
                            </li>
                        ))}
                    </ul>

                    <dl className="mt-4 space-y-1 border-t border-slate-100 pt-4 text-sm">
                        <div className="flex justify-between text-slate-600">
                            <span>Subtotal</span>
                            <span>{formatPrice(order.subtotal)}</span>
                        </div>
                        {order.discount > 0 && (
                            <div className="flex justify-between text-slate-600">
                                <span>Desconto</span>
                                <span>- {formatPrice(order.discount)}</span>
                            </div>
                        )}
                        <div className="flex justify-between text-slate-600">
                            <span>Frete</span>
                            <span>{order.shipping === 0 ? 'Grátis' : formatPrice(order.shipping)}</span>
                        </div>
                        <div className="flex justify-between font-semibold">
                            <span>Total</span>
                            <span>{formatPrice(order.total)}</span>
                        </div>
                    </dl>
                </div>

                <div className="space-y-4">
                    <div className="rounded-lg bg-white p-5 ring-1 ring-slate-100">
                        <p className="mb-2 text-sm font-medium">Status</p>
                        <select
                            className="input"
                            value={order.status}
                            onChange={(e) => updateStatus(e.target.value)}
                        >
                            {STATUSES.map((status) => (
                                <option key={status} value={status}>
                                    {STATUS_LABELS[status]}
                                </option>
                            ))}
                        </select>
                    </div>

                    <div className="rounded-lg bg-white p-5 ring-1 ring-slate-100 text-sm">
                        <p className="font-medium">Cliente</p>
                        <p>{order.customer_name}</p>
                        <p className="text-slate-500">{order.customer_email}</p>
                    </div>

                    <div className="rounded-lg bg-white p-5 ring-1 ring-slate-100 text-sm">
                        <p className="font-medium">Endereço</p>
                        <p>
                            {order.address.street}, {order.address.number} — {order.address.neighborhood}
                        </p>
                        <p>
                            {order.address.city}/{order.address.state} — {order.address.zip_code}
                        </p>
                    </div>

                    {order.payment && (
                        <div className="rounded-lg bg-white p-5 ring-1 ring-slate-100 text-sm">
                            <p className="font-medium">Pagamento</p>
                            <p>
                                {order.payment.method} — {order.payment.status}
                            </p>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
