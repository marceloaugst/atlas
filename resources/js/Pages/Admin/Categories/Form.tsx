import { useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { slugify } from '@/lib/slug';
import { Category } from '@/types/models';

interface Props {
    category?: Category;
    action: string;
    method: 'post' | 'put';
}

export default function Form({ category, action, method }: Props) {
    const [slugTouched, setSlugTouched] = useState(!!category);
    const { data, setData, post, put, processing, errors } = useForm({
        name: category?.name ?? '',
        slug: category?.slug ?? '',
        description: category?.description ?? '',
        active: category?.active ?? true,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        (method === 'post' ? post : put)(action);
    }

    return (
        <form onSubmit={submit} className="max-w-lg space-y-4 rounded-lg bg-white p-6 ring-1 ring-slate-100">
            <label className="block text-sm">
                <span className="text-slate-500">Nome</span>
                <input
                    className="input mt-1"
                    value={data.name}
                    onChange={(e) => {
                        setData('name', e.target.value);
                        if (! slugTouched) setData('slug', slugify(e.target.value));
                    }}
                />
                {errors.name && <span className="mt-1 block text-xs text-red-600">{errors.name}</span>}
            </label>

            <label className="block text-sm">
                <span className="text-slate-500">Slug</span>
                <input
                    className="input mt-1"
                    value={data.slug}
                    onChange={(e) => {
                        setSlugTouched(true);
                        setData('slug', e.target.value);
                    }}
                />
                {errors.slug && <span className="mt-1 block text-xs text-red-600">{errors.slug}</span>}
            </label>

            <label className="block text-sm">
                <span className="text-slate-500">Descrição</span>
                <textarea
                    className="input mt-1"
                    rows={3}
                    value={data.description}
                    onChange={(e) => setData('description', e.target.value)}
                />
            </label>

            <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" checked={data.active} onChange={(e) => setData('active', e.target.checked)} />
                Ativa
            </label>

            <button
                type="submit"
                disabled={processing}
                className="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white disabled:opacity-60"
            >
                Salvar
            </button>
        </form>
    );
}
