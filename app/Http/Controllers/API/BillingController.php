<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function unpaidInvoices(Request $request, Customer $customer)
    {
        abort_unless($customer->tenant_id === $request->user()->tenant_id, 403);

        $invoices = $customer->invoices()->where('status', 'unpaid')->get();
        return response()->json($invoices);
    }

    public function customerPaymentHistory(Request $request, Customer $customer)
    {
        abort_unless($customer->tenant_id === $request->user()->tenant_id, 403);

        $payments = $customer->payments()->with('invoice')->get();
        return response()->json($payments);
    }
}
