<?php
// app/Http/Controllers/PaymentMethodController.php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use App\Services\AuditService;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->middleware('auth');
        $this->middleware('permission:manage_payment_methods');
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $query = PaymentMethod::query();

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('type')) {
            $query->where('type', $request->get('type'));
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->get('status'));
        }

        $paymentMethods = $query->withCount('paymentTransactions')->paginate(15);

        return view('payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        return view('payment-methods.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_methods,code',
            'type' => 'required|in:cash,card,e-wallet,bank_transfer,check',
            'description' => 'nullable|string',
            'config' => 'nullable|json',
        ]);

        $data = $request->all();
        if ($request->has('config')) {
            $data['config'] = json_decode($request->config, true);
        }

        $paymentMethod = PaymentMethod::create($data);

        $this->auditService->log('CREATE', 'payment_methods', $paymentMethod->id, null, $paymentMethod->toArray());

        return redirect()->route('payment-methods.index')
            ->with('success', 'Payment method created successfully');
    }

    public function show(PaymentMethod $paymentMethod)
    {
        $paymentMethod->load('paymentTransactions');

        $stats = [
            'total_transactions' => $paymentMethod->getTotalTransactions(),
            'total_amount' => $paymentMethod->getTotalAmount(),
            'monthly_amount' => $paymentMethod->getTotalAmount(now()->startOfMonth(), now()->endOfMonth()),
        ];

        return view('payment-methods.show', compact('paymentMethod', 'stats'));
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        return view('payment-methods.edit', compact('paymentMethod'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:payment_methods,code,' . $paymentMethod->id,
            'type' => 'required|in:cash,card,e-wallet,bank_transfer,check',
            'description' => 'nullable|string',
            'config' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $oldValues = $paymentMethod->toArray();
        
        $data = $request->all();
        if ($request->has('config')) {
            $data['config'] = json_decode($request->config, true);
        }

        $paymentMethod->update($data);

        $this->auditService->log('UPDATE', 'payment_methods', $paymentMethod->id, $oldValues, $paymentMethod->toArray());

        return redirect()->route('payment-methods.show', $paymentMethod)
            ->with('success', 'Payment method updated successfully');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        // Check if payment method has transactions
        if ($paymentMethod->paymentTransactions()->exists()) {
            return redirect()->route('payment-methods.index')
                ->with('error', 'Cannot delete payment method with existing transactions.');
        }

        $oldValues = $paymentMethod->toArray();
        $paymentMethod->delete();

        $this->auditService->log('DELETE', 'payment_methods', $paymentMethod->id, $oldValues, null);

        return redirect()->route('payment-methods.index')
            ->with('success', 'Payment method deleted successfully');
    }

    public function toggleStatus(PaymentMethod $paymentMethod)
    {
        $oldValues = $paymentMethod->toArray();
        $paymentMethod->update(['is_active' => !$paymentMethod->is_active]);

        $this->auditService->log(
            $paymentMethod->is_active ? 'ACTIVATE' : 'DEACTIVATE',
            'payment_methods',
            $paymentMethod->id,
            $oldValues,
            $paymentMethod->toArray()
        );

        $status = $paymentMethod->is_active ? 'activated' : 'deactivated';
        return redirect()->back()
            ->with('success', "Payment method {$status} successfully");
    }
}