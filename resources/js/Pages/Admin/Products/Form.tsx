import { useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';
import { slugify } from '@/lib/slug';
import { Author, Category } from '@/types/models';

export interface ProductFormValues {
    id?: number;
    title: string;
    slug: string;
    category_id: number | '';
    author_ids: number[];
    description: string;
    isbn: string;
    publisher: string;
    published_at: string;
    pages: number | '';
    cover_url: string;
    price: number | '';
    stock: number | '';
    active: boolean;
    featured: boolean;
}

interface Props {
    product?: ProductFormValues;
    categories: Category[];
    authors: Author[];
    action: string;
    method: 'post' | 'put';
}

export default function Form({ product, categories, authors, action, method }: Props) {
    const [slugTouched, setSlugTouched] = useState(!!product);
    const { data, setData, post, put, processing, errors } = useForm({
        title: product?.title ?? '',
        slug: product?.slug ?? '',
        category_id: product?.category_id ?? '',
        author_ids: product?.author_ids ?? [],
        description: product?.description ?? '',
        isbn: product?.isbn ?? '',
        publisher: product?.publisher ?? '',
        published_at: product?.published_at?.slice(0, 10) ?? '',
        pages: product?.pages ?? '',
        cover_url: product?.cover_url ?? '',
        price: product?.price ?? '',
        stock: product?.stock ?? '',
        active: product?.active ?? true,
        featured: product?.featured ?? false,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        (method === 'post' ? post : put)(action);
    }

    function toggleAuthor(id: number) {
        setData(
            'author_ids',
            data.author_ids.includes(id) ? data.author_ids.filter((a) => a !== id) : [...data.author_ids, id],
        );
    }

    return (
        <form onSubmit={submit} className="max-w-3xl space-y-6 rounded-lg bg-white p-6 ring-1 ring-slate-100">
            <div className="grid grid-cols-2 gap-4">
                <label className="col-span-2 block text-sm">
                    <span className="text-slate-500">Título</span>
                    <input
                        className="input mt-1"
                        value={data.title}
                        onChange={(e) => {
                            setData('title', e.target.value);
                            if (! slugTouched) setData('slug', slugify(e.target.value));
                        }}
                    />
                    {errors.title && <span className="mt-1 block text-xs text-red-600">{errors.title}</span>}
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
                    <span className="text-slate-500">Categoria</span>
                    <select
                        className="input mt-1"
                        value={data.category_id}
                        onChange={(e) => setData('category_id', Number(e.target.value))}
                    >
                        <option value="">Selecione</option>
                        {categories.map((category) => (
                            <option key={category.id} value={category.id}>
                                {category.name}
                            </option>
                        ))}
                    </select>
                    {errors.category_id && <span className="mt-1 block text-xs text-red-600">{errors.category_id}</span>}
                </label>

                <label className="block text-sm">
                    <span className="text-slate-500">Preço (R$)</span>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        className="input mt-1"
                        value={data.price}
                        onChange={(e) => setData('price', e.target.value === '' ? '' : Number(e.target.value))}
                    />
                    {errors.price && <span className="mt-1 block text-xs text-red-600">{errors.price}</span>}
                </label>

                <label className="block text-sm">
                    <span className="text-slate-500">Estoque</span>
                    <input
                        type="number"
                        min="0"
                        className="input mt-1"
                        value={data.stock}
                        onChange={(e) => setData('stock', e.target.value === '' ? '' : Number(e.target.value))}
                    />
                    {errors.stock && <span className="mt-1 block text-xs text-red-600">{errors.stock}</span>}
                </label>

                <label className="block text-sm">
                    <span className="text-slate-500">ISBN</span>
                    <input className="input mt-1" value={data.isbn} onChange={(e) => setData('isbn', e.target.value)} />
                    {errors.isbn && <span className="mt-1 block text-xs text-red-600">{errors.isbn}</span>}
                </label>

                <label className="block text-sm">
                    <span className="text-slate-500">Editora</span>
                    <input
                        className="input mt-1"
                        value={data.publisher}
                        onChange={(e) => setData('publisher', e.target.value)}
                    />
                </label>

                <label className="block text-sm">
                    <span className="text-slate-500">Publicado em</span>
                    <input
                        type="date"
                        className="input mt-1"
                        value={data.published_at}
                        onChange={(e) => setData('published_at', e.target.value)}
                    />
                </label>

                <label className="block text-sm">
                    <span className="text-slate-500">Páginas</span>
                    <input
                        type="number"
                        min="1"
                        className="input mt-1"
                        value={data.pages}
                        onChange={(e) => setData('pages', e.target.value === '' ? '' : Number(e.target.value))}
                    />
                </label>

                <label className="col-span-2 block text-sm">
                    <span className="text-slate-500">URL da capa</span>
                    <input
                        className="input mt-1"
                        value={data.cover_url}
                        onChange={(e) => setData('cover_url', e.target.value)}
                    />
                    {errors.cover_url && <span className="mt-1 block text-xs text-red-600">{errors.cover_url}</span>}
                </label>

                <label className="col-span-2 block text-sm">
                    <span className="text-slate-500">Descrição</span>
                    <textarea
                        className="input mt-1"
                        rows={4}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                    />
                </label>
            </div>

            <div>
                <p className="mb-2 text-sm text-slate-500">Autores</p>
                <div className="flex flex-wrap gap-2">
                    {authors.map((author) => (
                        <label
                            key={author.id}
                            className={`cursor-pointer rounded-full border px-3 py-1 text-sm ${
                                data.author_ids.includes(author.id)
                                    ? 'border-slate-900 bg-slate-900 text-white'
                                    : 'border-slate-200 text-slate-600'
                            }`}
                        >
                            <input
                                type="checkbox"
                                className="hidden"
                                checked={data.author_ids.includes(author.id)}
                                onChange={() => toggleAuthor(author.id)}
                            />
                            {author.name}
                        </label>
                    ))}
                </div>
            </div>

            <div className="flex gap-6">
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.active} onChange={(e) => setData('active', e.target.checked)} />
                    Ativo
                </label>
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={data.featured}
                        onChange={(e) => setData('featured', e.target.checked)}
                    />
                    Destaque
                </label>
            </div>

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
