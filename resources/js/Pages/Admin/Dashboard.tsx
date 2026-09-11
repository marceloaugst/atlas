import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatPrice } from '@/lib/money';

interface Props {
    metrics: {
        salesToday: number;
        salesMonth: number;
        ordersCount: number;
        customersCount: number;
    };
    topProducts: { product_title: string; total_sold: number }[];
}

export default function Dashboard({ metrics, topProducts }: Props) {
    return (
        <AdminLayout>
            <Head title="Dashboard" />

            <h1 className="mb-6 text-2xl font-semibold">Dashboard</h1>

            <div className="grid grid-cols-2 gap-4 md:grid-cols-4">
                <Metric label="Vendas hoje" value={formatPrice(metrics.salesToday)} />
                <Metric label="Vendas mês" value={formatPrice(metrics.salesMonth)} />
                <Metric label="Pedidos" value={String(metrics.ordersCount)} />
                <Metric label="Clientes" value={String(metrics.customersCount)} />
            </div>

            <div className="mt-8 rounded-lg bg-white p-5 ring-1 ring-slate-100">
                <h2 className="mb-4 font-medium">Produtos mais vendidos</h2>

                {topProducts.length === 0 ? (
                    <p className="text-sm text-slate-500">Nenhuma venda registrada ainda.</p>
                ) : (
                    <ul className="divide-y divide-slate-100">
                        {topProducts.map((product) => (
                            <li key={product.product_title} className="flex justify-between py-2 text-sm">
                                <span>{product.product_title}</span>
                                <span className="text-slate-500">{product.total_sold} vendas</span>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AdminLayout>
    );
}

function Metric({ label, value }: { label: string; value: string }) {
    return (
        <div className="rounded-lg bg-white p-5 ring-1 ring-slate-100">
            <p className="text-sm text-slate-500">{label}</p>
            <p className="mt-1 text-2xl font-semibold">{value}</p>
        </div>
    );
}
