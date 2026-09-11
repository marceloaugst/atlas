import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Coupon } from '@/types/models';
import Form from './Form';

interface Props {
    coupon: Coupon;
}

export default function Edit({ coupon }: Props) {
    return (
        <AdminLayout>
            <Head title="Editar cupom" />

            <h1 className="mb-6 text-2xl font-semibold">Editar cupom</h1>

            <Form coupon={coupon} action={`/admin/coupons/${coupon.id}`} method="put" />
        </AdminLayout>
    );
}
