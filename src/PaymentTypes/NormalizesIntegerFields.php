<?php

namespace Hyrograsper\LunarFortis\PaymentTypes;

trait NormalizesIntegerFields
{
    /**
     * @param  array<string>  $fields
     */
    protected function normalizeIntegerFields(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $this->wholeNumberOrNull($data[$field]);
            }
        }

        return $data;
    }

    protected function wholeNumberOrNull(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }

        if (! is_string($value)) {
            return null;
        }

        if (! preg_match('/^\d+$/', $value)) {
            return null;
        }

        return (int) $value;
    }
}
