<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CouponType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Coupons/Index', [
            'coupons' => Coupon::latest()->paginate(15),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Coupons/Create');
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        Coupon::create($this->toMoneyFields($request));

        return redirect()->route('admin.coupons.index')->with('success', 'Cupom criado.');
    }

    public function edit(Coupon $coupon): Response
    {
        return Inertia::render('Admin/Coupons/Edit', [
            'coupon' => [
                ...$coupon->toArray(),
                'value' => $coupon->type === CouponType::Fixed ? $coupon->value / 100 : $coupon->value,
                'minimum_amount' => $coupon->minimum_amount !== null ? $coupon->minimum_amount / 100 : null,
                'maximum_discount' => $coupon->maximum_discount !== null ? $coupon->maximum_discount / 100 : null,
            ],
        ]);
    }

    public function update(CouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->toMoneyFields($request));

        return redirect()->route('admin.coupons.index')->with('success', 'Cupom atualizado.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $coupon->delete();

        return back()->with('success', 'Cupom removido.');
    }

    private function toMoneyFields(CouponRequest $request): array
    {
        $data = $request->validated();
        $isPercentage = $data['type'] === CouponType::Percentage->value;

        return [
            ...$data,
            'value' => $isPercentage ? (int) $data['value'] : (int) round($data['value'] * 100),
            'minimum_amount' => isset($data['minimum_amount']) ? (int) round($data['minimum_amount'] * 100) : null,
            'maximum_discount' => isset($data['maximum_discount']) ? (int) round($data['maximum_discount'] * 100) : null,
        ];
    }
}
