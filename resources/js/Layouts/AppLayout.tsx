import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { SharedProps } from '@/types';

export default function AppLayout({ children }: PropsWithChildren) {
    const { cart } = usePage<SharedProps>().props;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                    <Link href="/" className="text-xl font-semibold tracking-tight">
                        📚 Atlas
                    </Link>

                    <Link href="/carrinho" className="flex items-center gap-2 text-sm font-medium">
                        🛒 Carrinho
                        {cart.count > 0 && (
                            <span className="rounded-full bg-slate-900 px-2 py-0.5 text-xs text-white">
                                {cart.count}
                            </span>
                        )}
                    </Link>
                </div>
            </header>

            <main className="mx-auto max-w-6xl px-4 py-8">{children}</main>
        </div>
    );
}
