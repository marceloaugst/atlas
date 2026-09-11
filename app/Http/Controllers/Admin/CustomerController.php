<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $customers = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->when($request->string('q')->isNotEmpty(), function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->string('q').'%')
                        ->orWhere('email', 'like', '%'.$request->string('q').'%');
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => $request->only(['q']),
        ]);
    }

    public function show(User $customer): Response
    {
        $customer->loadCount('orders');
        $orders = $customer->orders()->latest('placed_at')->get();

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }
}
