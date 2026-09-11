import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent, useEffect, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { formatPrice } from '@/lib/money';
import { Product } from '@/types/models';

interface Props {
    question?: string;
    answer?: string;
    products?: Product[];
    error?: string;
}

interface Exchange {
    question: string;
    answer?: string;
    products?: Product[];
    error?: string;
}

export default function Index({ question, answer, products, error }: Props) {
    const [history, setHistory] = useState<Exchange[]>([]);
    const { data, setData, post, processing, reset } = useForm({ question: '' });

    useEffect(() => {
        if (question && (answer !== undefined || error !== undefined)) {
            setHistory((prev) => [...prev, { question, answer, products, error }]);
            reset();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [question, answer, error]);

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/assistente', { preserveScroll: true });
    }

    return (
        <AppLayout>
            <Head title="Assistente de recomendação" />

            <div className="mx-auto max-w-2xl">
                <h1 className="text-2xl font-semibold">✨ Assistente de recomendação</h1>
                <p className="mt-1 text-sm text-slate-500">
                    Descreva o que você quer aprender ou ler, e eu recomendo livros do nosso catálogo.
                </p>

                <div className="mt-6 space-y-6">
                    {history.length === 0 && (
                        <p className="rounded-lg bg-white p-4 text-sm text-slate-500 ring-1 ring-slate-100">
                            Exemplo: "Quero aprender arquitetura de software, mas já tenho conhecimento
                            intermediário de programação."
                        </p>
                    )}

                    {history.map((exchange, index) => (
                        <div key={index} className="space-y-3">
                            <div className="ml-auto max-w-md rounded-lg bg-slate-900 px-4 py-2 text-sm text-white">
                                {exchange.question}
                            </div>

                            {exchange.error ? (
                                <p className="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
                                    {exchange.error}
                                </p>
                            ) : (
                                <div className="rounded-lg bg-white p-4 text-sm ring-1 ring-slate-100">
                                    <p className="whitespace-pre-line text-slate-700">{exchange.answer}</p>

                                    {exchange.products && exchange.products.length > 0 && (
                                        <div className="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                                            {exchange.products.map((product) => (
                                                <Link
                                                    key={product.id}
                                                    href={`/livros/${product.slug}`}
                                                    className="rounded-lg p-2 ring-1 ring-slate-100 hover:ring-slate-300"
                                                >
                                                    <div className="aspect-[2/3] overflow-hidden rounded bg-slate-100">
                                                        {product.cover_url && (
                                                            <img
                                                                src={product.cover_url}
                                                                alt={product.title}
                                                                className="h-full w-full object-cover"
                                                            />
                                                        )}
                                                    </div>
                                                    <p className="mt-1 line-clamp-2 text-xs font-medium">
                                                        {product.title}
                                                    </p>
                                                    <p className="text-xs text-slate-500">
                                                        {formatPrice(product.price)}
                                                    </p>
                                                </Link>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    ))}

                    {processing && (
                        <div className="ml-auto w-fit rounded-lg bg-white px-4 py-2 text-sm text-slate-400 ring-1 ring-slate-100">
                            Pensando...
                        </div>
                    )}
                </div>

                <form onSubmit={submit} className="mt-6 flex gap-2">
                    <textarea
                        className="input flex-1"
                        rows={2}
                        placeholder="O que você quer ler ou aprender?"
                        value={data.question}
                        onChange={(e) => setData('question', e.target.value)}
                        required
                        minLength={5}
                    />
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-lg bg-slate-900 px-5 py-2 text-sm font-medium text-white disabled:opacity-60"
                    >
                        Perguntar
                    </button>
                </form>
            </div>
        </AppLayout>
    );
}
