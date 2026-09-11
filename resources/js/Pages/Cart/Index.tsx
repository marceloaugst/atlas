import { Head, Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { formatPrice } from '@/lib/money';
import { Cart } from '@/types/models';

interface Props {
    cartDetail: Cart;
}

export default function Index({ cartDetail }: Props) {
    const subtotal = cartDetail.items.reduce((sum, item) => sum + item.quantity * item.unit_price, 0);

    return (
        <AppLayout>
            <Head title="Carrinho" />

            <h1 className="mb-6 text-2xl font-semibold">Meu carrinho</h1>

            {cartDetail.items.length === 0 ? (
                <p className="text-slate-500">
                    Seu carrinho está vazio.{' '}
                    <Link href="/" className="underline">
                        Ver catálogo
                    </Link>
                </p>
            ) : (
                <div className="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_280px]">
                    <ul className="divide-y divide-slate-200 rounded-lg bg-white ring-1 ring-slate-100">
                        {cartDetail.items.map((item) => (
                            <CartRow key={item.id} item={item} />
                        ))}
                    </ul>

                    <div className="h-fit rounded-lg bg-white p-5 ring-1 ring-slate-100">
                        <div className="flex justify-between text-sm text-slate-600">
                            <span>Subtotal</span>
                            <span>{formatPrice(subtotal)}</span>
                        </div>
                        <button
                            onClick={() => router.visit('/checkout')}
                            className="mt-4 w-full rounded-lg bg-slate-900 py-3 text-sm font-medium text-white"
                        >
                            Finalizar compra
                        </button>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}

function CartRow({ item }: { item: Cart['items'][number] }) {
    const { data, setData, patch, processing } = useForm({ quantity: item.quantity });

    function updateQuantity(quantity: number) {
        if (quantity < 0) return;
        setData('quantity', quantity);
        patch(`/carrinho/${item.id}`, { preserveScroll: true });
    }

    function remove() {
        router.delete(`/carrinho/${item.id}`, { preserveScroll: true });
    }

    return (
        <li className="flex items-center gap-4 p-4">
            <div className="h-20 w-14 flex-none overflow-hidden rounded bg-slate-100">
                {item.product.cover_url && (
                    <img src={item.product.cover_url} alt={item.product.title} className="h-full w-full object-cover" />
                )}
            </div>

            <div className="flex-1">
                <Link href={`/livros/${item.product.slug}`} className="font-medium hover:underline">
                    {item.product.title}
                </Link>
                <p className="text-sm text-slate-500">{formatPrice(item.unit_price)} / unidade</p>
            </div>

            <div className="flex items-center gap-2">
                <button
                    disabled={processing}
                    onClick={() => updateQuantity(data.quantity - 1)}
                    className="h-7 w-7 rounded border border-slate-200 text-sm"
                >
                    −
                </button>
                <span className="w-6 text-center text-sm">{data.quantity}</span>
                <button
                    disabled={processing}
                    onClick={() => updateQuantity(data.quantity + 1)}
                    className="h-7 w-7 rounded border border-slate-200 text-sm"
                >
                    +
                </button>
            </div>

            <p className="w-24 text-right font-medium">{formatPrice(item.quantity * item.unit_price)}</p>

            <button onClick={remove} className="text-slate-400 hover:text-red-600" aria-label="Remover">
                ✕
            </button>
        </li>
    );
}
