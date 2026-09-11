import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import Form from './Form';

export default function Create() {
    return (
        <AdminLayout>
            <Head title="Novo autor" />

            <h1 className="mb-6 text-2xl font-semibold">Novo autor</h1>

            <Form action="/admin/authors" method="post" />
        </AdminLayout>
    );
}
