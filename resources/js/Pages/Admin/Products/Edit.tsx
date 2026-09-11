import { Head } from '@inertiajs/react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Author, Category } from '@/types/models';
import Form, { ProductFormValues } from './Form';

interface Props {
    product: ProductFormValues;
    categories: Category[];
    authors: Author[];
}

export default function Edit({ product, categories, authors }: Props) {
    return (
        <AdminLayout>
            <Head title="Editar produto" />

            <h1 className="mb-6 text-2xl font-semibold">Editar produto</h1>

            <Form
                product={product}
                categories={categories}
                authors={authors}
                action={`/admin/products/${product.id}`}
                method="put"
            />
        </AdminLayout>
    );
}
