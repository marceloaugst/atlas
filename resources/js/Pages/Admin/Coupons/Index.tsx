import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { formatPrice } from '@/lib/money';
import { Coupon, Paginated } from '@/types/models';

interface Props {
    coupons: Paginated<Coupon>;
}

export default function Index({ coupons }: Props) {
    function destroy(coupon: Coupon) {
        if (! confirm(`Excluir o cupom "${coupon.code}"?`)) return;
        router.delete(`/admin/coupons/${coupon.id}`);
    }

    function formatValue(coupon: Coupon) {
        return coupon.type === 'PERCENTAGE' ? `${coupon.value}%` : formatPrice(coupon.value);
    }

    return (
        <AdminLayout>
            <Head title="Cupons" />

            <div className="mb-6 flex items-center justify-between">
                <h1 className="text-2xl font-semibold">Cupons</h1>
                <Link href="/admin/coupons/create" className="rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">
                    Novo cupom
                </Link>
            </div>

            <table className="w-full overflow-hidden rounded-lg bg-white text-sm ring-1 ring-slate-100">
                <thead className="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th className="px-4 py-3">Código</th>
                        <th className="px-4 py-3">Valor</th>
                        <th className="px-4 py-3">Uso</th>
                        <th className="px-4 py-3">Ativo</th>
                        <th className="px-4 py-3" />
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {coupons.data.map((coupon) => (
                        <tr key={coupon.id}>
                            <td className="px-4 py-3 font-mono">{coupon.code}</td>
                            <td className="px-4 py-3">{formatValue(coupon)}</td>
                            <td className="px-4 py-3">
                                {coupon.usage_count}
                                {coupon.usage_limit ? ` / ${coupon.usage_limit}` : ''}
                            </td>
                            <td className="px-4 py-3">{coupon.active ? 'Sim' : 'Não'}</td>
                            <td className="px-4 py-3 text-right">
                                <Link
                                    href={`/admin/coupons/${coupon.id}/edit`}
                                    className="mr-4 text-slate-600 hover:underline"
                                >
                                    Editar
                                </Link>
                                <button onClick={() => destroy(coupon)} className="text-red-600 hover:underline">
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
