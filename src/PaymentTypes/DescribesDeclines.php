<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

use Hyrograsper\LunarFortis\Enums\ReasonCode;

trait DescribesDeclines
{
    protected function declineMessage(array $data): string
    {
        $reasonCodeId = $data['reason_code_id'] ?? null;

        $reason = is_numeric($reasonCodeId)
            ? ReasonCode::fromCode((int) $reasonCodeId)
            : null;

        $verbiage = is_string($data['verbiage'] ?? null) && trim($data['verbiage']) !== ''
            ? trim($data['verbiage'])
            : null;

        $message = implode('. ', array_filter([$reason, $verbiage]));

        return $message !== '' ? $message : 'The payment was declined.';
    }
}
