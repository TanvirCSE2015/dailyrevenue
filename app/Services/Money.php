<?php

namespace App\Services;

use InvalidArgumentException;

class Money
{
    public static function cents(mixed $value): int
    {
        if (! is_string($value) && ! is_int($value) && ! is_float($value)) {
            throw new InvalidArgumentException('Amount must be a number with at most 2 decimal places.');
        }

        $text = trim((string) $value);
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/D', $text)) {
            throw new InvalidArgumentException('Amount must be a number with at most 2 decimal places.');
        }

        [$whole, $fraction] = array_pad(explode('.', $text, 2), 2, '');
        if (strlen($whole) > 8) {
            throw new InvalidArgumentException('Amount is too large.');
        }

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public static function jsonNumber(int $cents): int|float
    {
        return $cents % 100 === 0 ? intdiv($cents, 100) : $cents / 100;
    }

    public static function accountList(mixed $input, int $totalCents): array
    {
        if ($input === null || $input === '' || $input === []) {
            return [];
        }
        if (! is_array($input) || ! array_is_list($input) || count($input) > 20) {
            throw new InvalidArgumentException('accountList must be an array of at most 20 accounts.');
        }

        $accounts = [];
        $sum = 0;
        foreach ($input as $index => $entry) {
            if (! is_array($entry) || ! is_string($entry['accountNumber'] ?? null)
                || trim($entry['accountNumber']) === '') {
                throw new InvalidArgumentException('Each accountList entry needs an accountNumber.');
            }
            try {
                $cents = self::cents($entry['amount'] ?? null);
            } catch (InvalidArgumentException $error) {
                throw new InvalidArgumentException('Invalid accountList amount at position '.($index + 1).'.', 0, $error);
            }
            $sum += $cents;
            $accounts[] = [
                'accountNumber' => trim((string) $entry['accountNumber']),
                'amount' => self::jsonNumber($cents),
            ];
        }

        if ($sum !== $totalCents) {
            throw new InvalidArgumentException('accountList amounts must exactly equal the payment amount.');
        }

        return $accounts;
    }
}
