import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { formatPrice } from '@/lib/money';
import { Order, OrderItem } from '@/types/models';

interface Props {
    order: Order & { items: OrderItem[] };
    canCancel: boolean;
}

const STATUS_LABELS: Record<string, string> = {
    PENDING: 'Aguardando pagamento',
    PAID: 'Pago',
    PROCESSING: 'Em processamento',
    SHIPPED: 'Enviado',
    DELIVERED: 'Entregue',
    CANCELED: 'Cancelado',
};

export default function Show({ order, canCancel }: Props) {
    const errors = usePage().props.errors as Record<string, string>;

    function cancelOrder() {
        if (! confirm('Tem certeza que deseja cancelar este pedido?')) return;
        router.post(`/minha-conta/pedidos/${order.uuid}/cancelar`);
    }

    return (
        <AppLayout>
            <Head title={`Pedido #${order.uuid.slice(0, 8)}`} />

            <div className="mx-auto max-w-2xl">
                <div className="rounded-lg bg-emerald-50 p-4 text-emerald-800">
                    ✅ Pedido confirmado! Enviamos os detalhes para <strong>{order.customer_email}</strong>.
                </div>

                <div className="mt-6 rounded-lg bg-white p-5 ring-1 ring-slate-100">
                    <div className="flex items-center justify-between">
                        <h1 className="font-medium">Pedido #{order.uuid.slice(0, 8).toUpperCase()}</h1>
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">
                            {STATUS_LABELS[order.status] ?? order.status}
                        </span>
                    </div>

                    <ul className="mt-4 divide-y divide-slate-100">
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

                    <div className="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">
                        <p className="font-medium text-slate-800">Endereço de entrega</p>
                        <p>
                            {order.address.street}, {order.address.number} — {order.address.neighborhood}
                        </p>
                        <p>
                            {order.address.city}/{order.address.state} — {order.address.zip_code}
                        </p>
                    </div>

                    {order.payment && (
                        <div className="mt-4 border-t border-slate-100 pt-4 text-sm text-slate-600">
                            <p className="font-medium text-slate-800">Pagamento</p>
                            <p>
                                {order.payment.method} — {order.payment.status}
                            </p>
                        </div>
                    )}
                </div>

                {errors.order && <p className="mt-4 text-sm text-red-600">{errors.order}</p>}

                <div className="mt-6 flex items-center justify-between">
                    <Link href="/" className="text-sm text-slate-500 hover:underline">
                        &larr; Voltar ao catálogo
                    </Link>

                    {canCancel && (
                        <button onClick={cancelOrder} className="text-sm text-red-600 hover:underline">
                            Cancelar pedido
                        </button>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
