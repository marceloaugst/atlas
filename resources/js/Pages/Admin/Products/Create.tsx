import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Author, Category } from '@/types/models';
import Form from './Form';

interface Props {
    categories: Category[];
    authors: Author[];
}

export default function Create({ categories, authors }: Props) {
    return (
        <AdminLayout>
            <Head title="Novo produto" />

            <h1 className="mb-6 text-2xl font-semibold">Novo produto</h1>

            <Form categories={categories} authors={authors} action="/admin/products" method="post" />
        </AdminLayout>
    );
}
