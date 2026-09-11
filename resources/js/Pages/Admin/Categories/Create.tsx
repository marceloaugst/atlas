import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Form from './Form';

export default function Create() {
    return (
        <AdminLayout>
            <Head title="Nova categoria" />

            <h1 className="mb-6 text-2xl font-semibold">Nova categoria</h1>

            <Form action="/admin/categories" method="post" />
        </AdminLayout>
    );
}
