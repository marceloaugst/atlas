<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Pix = 'PIX';
    case CreditCard = 'CREDIT_CARD';
    case DebitCard = 'DEBIT_CARD';
    case Boleto = 'BOLETO';

    public function label(): string
    {
        return match ($this) {
            self::Pix => 'Pix',
            self::CreditCard => 'Cartão de crédito',
            self::DebitCard => 'Cartão de débito',
            self::Boleto => 'Boleto',
        };
    }
}
