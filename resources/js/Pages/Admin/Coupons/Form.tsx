import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Coupon } from '@/types/models';

interface Props {
    coupon?: Coupon;
    action: string;
    method: 'post' | 'put';
}

export default function Form({ coupon, action, method }: Props) {
    const { data, setData, post, put, processing, errors } = useForm({
        code: coupon?.code ?? '',
        type: coupon?.type ?? 'PERCENTAGE',
        value: coupon?.value ?? '',
        minimum_amount: coupon?.minimum_amount ?? '',
        maximum_discount: coupon?.maximum_discount ?? '',
        starts_at: coupon?.starts_at?.slice(0, 10) ?? '',
        expires_at: coupon?.expires_at?.slice(0, 10) ?? '',
        usage_limit: coupon?.usage_limit ?? '',
        active: coupon?.active ?? true,
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        (method === 'post' ? post : put)(action);
    }

    return (
        <form onSubmit={submit} className="max-w-lg space-y-4 rounded-lg bg-white p-6 ring-1 ring-slate-100">
            <label className="block text-sm">
                <span className="text-slate-500">Código</span>
                <input
                    className="input mt-1 font-mono uppercase"
                    value={data.code}
                    onChange={(e) => setData('code', e.target.value.toUpperCase())}
                />
                {errors.code && <span className="mt-1 block text-xs text-red-600">{errors.code}</span>}
            </label>

            <label className="block text-sm">
                <span className="text-slate-500">Tipo</span>
                <select
                    className="input mt-1"
                    value={data.type}
                    onChange={(e) => setData('type', e.target.value as 'PERCENTAGE' | 'FIXED')}
                >
                    <option value="PERCENTAGE">Percentual</option>
                    <option value="FIXED">Valor fixo</option>
                </select>
            </label>

            <label className="block text-sm">
                <span className="text-slate-500">
                    Valor {data.type === 'PERCENTAGE' ? '(%)' : '(R$)'}
                </span>
                <input
                    type="number"
                    step={data.type === 'PERCENTAGE' ? '1' : '0.01'}
                    min="0"
                    className="input mt-1"
                    value={data.value}
                    onChange={(e) => setData('value', e.target.value === '' ? '' : Number(e.target.value))}
                />
                {errors.value && <span className="mt-1 block text-xs text-red-600">{errors.value}</span>}
            </label>

            <label className="block text-sm">
                <span className="text-slate-500">Valor mínimo da compra (R$)</span>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    className="input mt-1"
                    value={data.minimum_amount}
                    onChange={(e) => setData('minimum_amount', e.target.value === '' ? '' : Number(e.target.value))}
                />
            </label>

            <label className="block text-sm">
                <span className="text-slate-500">Desconto máximo (R$)</span>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    className="input mt-1"
                    value={data.maximum_discount}
                    onChange={(e) => setData('maximum_discount', e.target.value === '' ? '' : Number(e.target.value))}
                />
            </label>

            <div className="grid grid-cols-2 gap-4">
                <label className="block text-sm">
                    <span className="text-slate-500">Início</span>
                    <input
                        type="date"
                        className="input mt-1"
                        value={data.starts_at}
                        onChange={(e) => setData('starts_at', e.target.value)}
                    />
                </label>
                <label className="block text-sm">
                    <span className="text-slate-500">Expira em</span>
                    <input
                        type="date"
                        className="input mt-1"
                        value={data.expires_at}
                        onChange={(e) => setData('expires_at', e.target.value)}
                    />
                    {errors.expires_at && <span className="mt-1 block text-xs text-red-600">{errors.expires_at}</span>}
                </label>
            </div>

            <label className="block text-sm">
                <span className="text-slate-500">Limite de uso</span>
                <input
                    type="number"
                    min="1"
                    className="input mt-1"
                    value={data.usage_limit}
                    onChange={(e) => setData('usage_limit', e.target.value === '' ? '' : Number(e.target.value))}
                />
            </label>

            <label className="flex items-center gap-2 text-sm">
                <input type="checkbox" checked={data.active} onChange={(e) => setData('active', e.target.checked)} />
                Ativo
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
