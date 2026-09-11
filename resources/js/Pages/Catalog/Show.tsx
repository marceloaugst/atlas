import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { formatPrice } from '@/lib/money';
import { Product } from '@/types/models';

interface Props {
    product: Product;
}

export default function Show({ product }: Props) {
    const purchasable = product.active && product.stock > 0;
    const [adding, setAdding] = useState(false);

    function addToCart() {
        setAdding(true);
        router.post(
            '/carrinho',
            { product_id: product.id, quantity: 1 },
            { preserveScroll: true, onFinish: () => setAdding(false) },
        );
    }

    return (
        <AppLayout>
            <Head title={product.title} />

            <Link href="/" className="text-sm text-slate-500 hover:underline">
                &larr; Voltar ao catálogo
            </Link>

            <div className="mt-4 grid grid-cols-1 gap-10 md:grid-cols-[300px_1fr]">
                <div className="aspect-[2/3] overflow-hidden rounded-lg bg-slate-100">
                    {product.cover_url && (
                        <img src={product.cover_url} alt={product.title} className="h-full w-full object-cover" />
                    )}
                </div>

                <div>
                    <p className="text-sm text-slate-500">{product.category.name}</p>
                    <h1 className="mt-1 text-2xl font-semibold">{product.title}</h1>
                    <p className="mt-1 text-slate-600">{product.authors.map((a) => a.name).join(', ')}</p>

                    <p className="mt-6 text-3xl font-bold">{formatPrice(product.price)}</p>

                    <button
                        disabled={!purchasable || adding}
                        onClick={addToCart}
                        className="mt-4 rounded-lg bg-slate-900 px-6 py-3 text-sm font-medium text-white disabled:cursor-not-allowed disabled:bg-slate-300"
                    >
                        {purchasable ? 'Adicionar ao carrinho' : 'Indisponível'}
                    </button>

                    <dl className="mt-8 grid grid-cols-2 gap-y-2 text-sm text-slate-600">
                        <dt className="text-slate-400">Editora</dt>
                        <dd>{product.publisher ?? '—'}</dd>
                        <dt className="text-slate-400">ISBN</dt>
                        <dd>{product.isbn ?? '—'}</dd>
                        <dt className="text-slate-400">Páginas</dt>
                        <dd>{product.pages ?? '—'}</dd>
                    </dl>

                    {product.description && (
                        <p className="mt-6 whitespace-pre-line text-slate-700">{product.description}</p>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
