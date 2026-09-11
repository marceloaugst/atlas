import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import AppLayout from '@/Layouts/AppLayout';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/registro');
    }

    return (
        <AppLayout>
            <Head title="Criar conta" />

            <div className="mx-auto max-w-sm">
                <h1 className="mb-6 text-2xl font-semibold">Criar conta</h1>

                <form onSubmit={submit} className="space-y-4">
                    <label className="block text-sm">
                        <span className="text-slate-500">Nome</span>
                        <input
                            className="input mt-1"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                        />
                        {errors.name && <span className="mt-1 block text-xs text-red-600">{errors.name}</span>}
                    </label>

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

                    <label className="block text-sm">
                        <span className="text-slate-500">Confirmar senha</span>
                        <input
                            type="password"
                            className="input mt-1"
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                        />
                    </label>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg bg-slate-900 py-2.5 text-sm font-medium text-white disabled:opacity-60"
                    >
                        Criar conta
                    </button>
                </form>

                <p className="mt-4 text-sm text-slate-500">
                    Já tem uma conta?{' '}
                    <Link href="/login" className="underline">
                        Entrar
                    </Link>
                </p>
            </div>
        </AppLayout>
    );
}
