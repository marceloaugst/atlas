import { Link, router, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { SharedProps } from '@/types';

export default function AppLayout({ children }: PropsWithChildren) {
    const { cart, auth } = usePage<SharedProps>().props;

    function logout() {
        router.post('/logout');
    }

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <header className="border-b border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                    <Link href="/" className="text-xl font-semibold tracking-tight">
                        📚 Atlas
                    </Link>

                    <div className="flex items-center gap-6 text-sm font-medium">
                        <Link href="/carrinho" className="flex items-center gap-2">
                            🛒 Carrinho
                            {cart.count > 0 && (
                                <span className="rounded-full bg-slate-900 px-2 py-0.5 text-xs text-white">
                                    {cart.count}
                                </span>
                            )}
                        </Link>

                        {auth.user ? (
                            <div className="flex items-center gap-4">
                                <Link href="/minha-conta/pedidos" className="text-slate-600 hover:text-slate-900">
                                    Meus pedidos
                                </Link>
                                <span className="text-slate-400">{auth.user.name}</span>
                                <button onClick={logout} className="text-slate-600 hover:text-slate-900">
                                    Sair
                                </button>
                            </div>
                        ) : (
                            <div className="flex items-center gap-4">
                                <Link href="/login" className="text-slate-600 hover:text-slate-900">
                                    Entrar
                                </Link>
                                <Link href="/registro" className="text-slate-600 hover:text-slate-900">
                                    Criar conta
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
            </header>

            <main className="mx-auto max-w-6xl px-4 py-8">{children}</main>
        </div>
    );
}
