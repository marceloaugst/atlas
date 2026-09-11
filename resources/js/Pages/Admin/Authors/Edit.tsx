import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Author } from '@/types/models';
import Form from './Form';

interface Props {
    author: Author;
}

export default function Edit({ author }: Props) {
    return (
        <AdminLayout>
            <Head title="Editar autor" />

            <h1 className="mb-6 text-2xl font-semibold">Editar autor</h1>

            <Form author={author} action={`/admin/authors/${author.id}`} method="put" />
        </AdminLayout>
    );
}
