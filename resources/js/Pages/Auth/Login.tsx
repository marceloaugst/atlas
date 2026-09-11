import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '@/Layouts/AppLayout';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/login');
    }

    return (
        <AppLayout>
            <Head title="Entrar" />

            <div className="mx-auto max-w-sm">
                <h1 className="mb-6 text-2xl font-semibold">Entrar</h1>

                <form onSubmit={submit} className="space-y-4">
                    <label className="block text-sm">
                        <span className="text-slate-500">E-mail</span>
                        <input
                            type="email"
                            className="input mt-1"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                        />
                        {errors.email && <span className="mt-1 block text-xs text-red-600">{errors.email}</span>}
                    </label>

                    <label className="block text-sm">
                        <span className="text-slate-500">Senha</span>
                        <input
                            type="password"
                            className="input mt-1"
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                        />
                        {errors.password && <span className="mt-1 block text-xs text-red-600">{errors.password}</span>}
                    </label>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg bg-slate-900 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                    >
                        Entrar
                    </button>
                </form>

                <p className="mt-4 text-sm text-slate-500">
                    Não tem uma conta?{' '}
                    <Link href="/registro" className="underline">
                        Criar conta
                    </Link>
                </p>
            </div>
        </AppLayout>
    );
}
