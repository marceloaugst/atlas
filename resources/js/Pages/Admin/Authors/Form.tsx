import { useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { slugify } from '@/lib/slug';
import { Author } from '@/types/models';

interface Props {
    author?: Author;
    action: string;
    method: 'post' | 'put';
}

export default function Form({ author, action, method }: Props) {
    const [slugTouched, setSlugTouched] = useState(!!author);
    const { data, setData, post, put, processing, errors } = useForm({
        name: author?.name ?? '',
        slug: author?.slug ?? '',
        description: author?.description ?? '',
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
