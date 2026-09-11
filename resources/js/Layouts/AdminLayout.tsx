import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { SharedProps } from '@/types';

const NAV = [
    { href: '/admin', label: 'Dashboard' },
    { href: '/admin/products', label: 'Produtos' },
    { href: '/admin/categories', label: 'Categorias' },
    { href: '/admin/authors', label: 'Autores' },
    { href: '/admin/pedidos', label: 'Pedidos' },
    { href: '/admin/clientes', label: 'Clientes' },
    { href: '/admin/coupons', label: 'Cupons' },
    { href: '/admin/importar-livros', label: 'Importar Google Books' },
];

export default function AdminLayout({ children }: PropsWithChildren) {
    const { url } = usePage();
    const { auth } = usePage<SharedProps>().props;

    return (
        <div className="min-h-screen bg-slate-50 text-slate-900">
            <div className="flex">
                <aside className="min-h-screen w-56 flex-none border-r border-slate-200 bg-white">
                    <div className="border-b border-slate-200 px-5 py-4">
                        <Link href="/" className="text-lg font-semibold">
                            📚 Atlas
                        </Link>
                        <p className="text-xs text-slate-400">Painel administrativo</p>
                    </div>

                    <nav className="flex flex-col gap-0.5 p-3">
                        {NAV.map((item) => {
                            const active = item.href === '/admin' ? url === '/admin' : url.startsWith(item.href);

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={`rounded-md px-3 py-2 text-sm ${
                                        active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'
                                    }`}
                                >
                                    {item.label}
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="mt-auto border-t border-slate-200 p-4 text-xs text-slate-400">
                        {auth.user?.name}
                    </div>
                </aside>

                <main className="min-w-0 flex-1 p-8">{children}</main>
            </div>
        </div>
    );
}
