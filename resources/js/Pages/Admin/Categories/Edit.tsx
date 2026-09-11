import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Category } from '@/types/models';
import Form from './Form';

interface Props {
    category: Category;
}

export default function Edit({ category }: Props) {
    return (
        <AdminLayout>
            <Head title="Editar categoria" />

            <h1 className="mb-6 text-2xl font-semibold">Editar categoria</h1>

            <Form category={category} action={`/admin/categories/${category.id}`} method="put" />
        </AdminLayout>
    );
}
