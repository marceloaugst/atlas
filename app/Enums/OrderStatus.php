<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Processing = 'PROCESSING';
    case Shipped = 'SHIPPED';
    case Delivered = 'DELIVERED';
    case Canceled = 'CANCELED';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Aguardando pagamento',
            self::Paid => 'Pago',
            self::Processing => 'Em processamento',
            self::Shipped => 'Enviado',
            self::Delivered => 'Entregue',
            self::Canceled => 'Cancelado',
        };
    }
}
