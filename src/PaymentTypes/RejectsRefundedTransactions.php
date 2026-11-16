<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

trait RejectsRefundedTransactions
{
    /** @return array<int, string> */
    protected function verificationExpand(): array
    {
        return ['transaction_references'];
    }

    protected function hasRefund(array $data): bool
    {
        $references = $data['transaction_references'] ?? [];

        if (! is_array($references)) {
            return false;
        }

        foreach ($references as $reference) {
            if (is_array($reference) && ($reference['reference_type'] ?? null) === 'refund') {
                return true;
            }
        }

        return false;
    }
}
