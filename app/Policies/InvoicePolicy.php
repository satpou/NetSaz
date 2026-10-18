<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function view(User $user, Invoice $invoice): bool
    {
        return $user->tenant_id === null || $user->tenant_id === $invoice->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->isTenantSuperAdmin() || $user->isTenantAdmin() || $user->isStaff();
    }

    public function update(User $user, Invoice $invoice): bool
    {
        if (! ($user->isTenantSuperAdmin() || $user->isTenantAdmin() || $user->isStaff())) {
            return false;
        }

        return $user->tenant_id === null || $user->tenant_id === $invoice->tenant_id;
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        if (! ($user->isTenantSuperAdmin() || $user->isTenantAdmin())) {
            return false;
        }

        return $user->tenant_id === null || $user->tenant_id === $invoice->tenant_id;
    }

    public function pay(User $user, Invoice $invoice): bool
    {
        if (! ($user->isTenantSuperAdmin() || $user->isTenantAdmin() || $user->isStaff())) {
            return false;
        }

        return $user->tenant_id === null || $user->tenant_id === $invoice->tenant_id;
    }

    public function manage_payments(User $user): bool
    {
        return $user->isTenantSuperAdmin() || $user->isTenantAdmin() || $user->isStaff();
    }
}
