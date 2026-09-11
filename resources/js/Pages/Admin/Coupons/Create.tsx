import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Form from './Form';

export default function Create() {
    return (
        <AdminLayout>
            <Head title="Novo cupom" />

            <h1 className="mb-6 text-2xl font-semibold">Novo cupom</h1>

            <Form action="/admin/coupons" method="post" />
        </AdminLayout>
    );
}
