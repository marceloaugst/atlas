import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEvent, ReactNode, useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { formatPrice } from '@/lib/money';
import { SharedProps } from '@/types';
import { Cart, CheckoutSummary } from '@/types/models';

interface Props {
    cart: Cart;
    summary: CheckoutSummary;
    couponCode: string | null;
    couponError: string | null;
}

const PAYMENT_METHODS = [
    { value: 'PIX', label: 'Pix' },
    { value: 'CREDIT_CARD', label: 'Cartão de crédito' },
    { value: 'DEBIT_CARD', label: 'Cartão de débito' },
    { value: 'BOLETO', label: 'Boleto' },
];

export default function Index({ cart, summary, couponCode, couponError }: Props) {
    const [couponInput, setCouponInput] = useState(couponCode ?? '');
    const { errors: sharedErrors, auth } = usePage<SharedProps>().props;
    const pageErrors = sharedErrors as Record<string, string>;

    const { data, setData, post, processing, errors } = useForm({
        customer_name: auth.user?.name ?? '',
        customer_email: auth.user?.email ?? '',
        address: {
            name: '',
            zip_code: '',
            street: '',
            number: '',
            complement: '',
            neighborhood: '',
            city: '',
            state: '',
        },
        coupon_code: couponCode ?? '',
        payment_method: 'PIX',
    });

    function applyCoupon() {
        router.get(
            '/checkout',
            couponInput ? { coupon: couponInput } : {},
            { preserveState: true, preserveScroll: true, only: ['summary', 'couponCode', 'couponError'] },
        );
        setData('coupon_code', couponInput);
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        post('/checkout');
    }

    return (
        <AppLayout>
            <Head title="Checkout" />

            <h1 className="mb-6 text-2xl font-semibold">Finalizar compra</h1>

            <form onSubmit={submit} className="grid grid-cols-1 gap-8 lg:grid-cols-[1fr_320px]">
                <div className="space-y-6">
                    <section className="rounded-lg bg-white p-5 ring-1 ring-slate-100">
                        <h2 className="mb-4 font-medium">Seus dados</h2>
                        <div className="grid grid-cols-2 gap-4">
                            <Field label="Nome completo" error={errors.customer_name}>
                                <input
                                    className="input"
                                    value={data.customer_name}
                                    onChange={(e) => setData('customer_name', e.target.value)}
                                />
                            </Field>
                            <Field label="E-mail" error={errors.customer_email}>
                                <input
                                    type="email"
                                    className="input"
                                    value={data.customer_email}
                                    onChange={(e) => setData('customer_email', e.target.value)}
                                />
                            </Field>
                        </div>
                    </section>

                    <section className="rounded-lg bg-white p-5 ring-1 ring-slate-100">
                        <h2 className="mb-4 font-medium">Endereço de entrega</h2>
                        <div className="grid grid-cols-2 gap-4">
                            <Field label="Destinatário" error={errors['address.name']}>
                                <input
                                    className="input"
                                    value={data.address.name}
                                    onChange={(e) => setData('address', { ...data.address, name: e.target.value })}
                                />
                            </Field>
                            <Field label="CEP" error={errors['address.zip_code']}>
                                <input
                                    className="input"
                                    value={data.address.zip_code}
                                    onChange={(e) => setData('address', { ...data.address, zip_code: e.target.value })}
                                />
                            </Field>
                            <Field label="Rua" error={errors['address.street']}>
                                <input
                                    className="input"
                                    value={data.address.street}
                                    onChange={(e) => setData('address', { ...data.address, street: e.target.value })}
                                />
                            </Field>
                            <Field label="Número" error={errors['address.number']}>
                                <input
                                    className="input"
                                    value={data.address.number}
                                    onChange={(e) => setData('address', { ...data.address, number: e.target.value })}
                                />
                            </Field>
                            <Field label="Complemento" error={errors['address.complement']}>
                                <input
                                    className="input"
                                    value={data.address.complement}
                                    onChange={(e) => setData('address', { ...data.address, complement: e.target.value })}
                                />
                            </Field>
                            <Field label="Bairro" error={errors['address.neighborhood']}>
                                <input
                                    className="input"
                                    value={data.address.neighborhood}
                                    onChange={(e) => setData('address', { ...data.address, neighborhood: e.target.value })}
                                />
                            </Field>
                            <Field label="Cidade" error={errors['address.city']}>
                                <input
                                    className="input"
                                    value={data.address.city}
                                    onChange={(e) => setData('address', { ...data.address, city: e.target.value })}
                                />
                            </Field>
                            <Field label="UF" error={errors['address.state']}>
                                <input
                                    className="input"
                                    maxLength={2}
                                    value={data.address.state}
                                    onChange={(e) =>
                                        setData('address', { ...data.address, state: e.target.value.toUpperCase() })
                                    }
                                />
                            </Field>
                        </div>
                    </section>

                    <section className="rounded-lg bg-white p-5 ring-1 ring-slate-100">
                        <h2 className="mb-4 font-medium">Pagamento</h2>
                        <div className="grid grid-cols-2 gap-3">
                            {PAYMENT_METHODS.map((method) => (
                                <label
                                    key={method.value}
                                    className={`cursor-pointer rounded-lg border px-4 py-3 text-sm ${
                                        data.payment_method === method.value
                                            ? 'border-slate-900 ring-1 ring-slate-900'
                                            : 'border-slate-200'
                                    }`}
                                >
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value={method.value}
                                        checked={data.payment_method === method.value}
                                        onChange={() => setData('payment_method', method.value)}
                                        className="mr-2"
                                    />
                                    {method.label}
                                </label>
                            ))}
                        </div>
                    </section>
                </div>

                <aside className="h-fit space-y-4 rounded-lg bg-white p-5 ring-1 ring-slate-100">
                    <div>
                        <label className="text-sm text-slate-500">Cupom de desconto</label>
                        <div className="mt-1 flex gap-2">
                            <input
                                className="input"
                                placeholder="WELCOME10"
                                value={couponInput}
                                onChange={(e) => setCouponInput(e.target.value.toUpperCase())}
                            />
                            <button type="button" onClick={applyCoupon} className="rounded-lg bg-slate-100 px-4 text-sm">
                                Aplicar
                            </button>
                        </div>
                        {couponError && <p className="mt-1 text-xs text-red-600">{couponError}</p>}
                        {summary.coupon && !couponError && (
                            <p className="mt-1 text-xs text-emerald-600">Cupom {summary.coupon} aplicado.</p>
                        )}
                    </div>

                    <dl className="space-y-2 text-sm">
                        <Row label="Subtotal" value={formatPrice(summary.subtotal)} />
                        {summary.discount > 0 && <Row label="Desconto" value={`- ${formatPrice(summary.discount)}`} />}
                        <Row label="Frete" value={summary.shipping === 0 ? 'Grátis' : formatPrice(summary.shipping)} />
                        <div className="border-t border-slate-200 pt-2">
                            <Row label="Total" value={formatPrice(summary.total)} bold />
                        </div>
                    </dl>

                    {pageErrors.checkout && <p className="text-sm text-red-600">{pageErrors.checkout}</p>}

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full rounded-lg bg-slate-900 py-3 text-sm font-medium text-white disabled:opacity-60"
                    >
                        Confirmar pedido
                    </button>
                </aside>
            </form>
        </AppLayout>
    );
}

function Field({ label, error, children }: { label: string; error?: string; children: ReactNode }) {
    return (
        <label className="block text-sm">
            <span className="text-slate-500">{label}</span>
            <div className="mt-1">{children}</div>
            {error && <span className="mt-1 block text-xs text-red-600">{error}</span>}
        </label>
    );
}

function Row({ label, value, bold }: { label: string; value: string; bold?: boolean }) {
    return (
        <div className={`flex justify-between ${bold ? 'font-semibold' : 'text-slate-600'}`}>
            <span>{label}</span>
            <span>{value}</span>
        </div>
    );
}
