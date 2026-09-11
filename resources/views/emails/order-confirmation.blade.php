<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
    </head>
    <body style="font-family: sans-serif; color: #0f172a;">
        <h1 style="font-size: 20px;">Pedido confirmado!</h1>

        <p>Olá, {{ $order->customer_name }}. Recebemos seu pedido #{{ strtoupper(substr($order->uuid, 0, 8)) }}.</p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 16px;">
            @foreach ($order->items as $item)
                <tr>
                    <td style="padding: 4px 0;">{{ $item->quantity }}x {{ $item->product_title }}</td>
                    <td style="padding: 4px 0; text-align: right;">
                        R$ {{ number_format($item->subtotal / 100, 2, ',', '.') }}
                    </td>
                </tr>
            @endforeach
        </table>

        <p style="margin-top: 16px; font-weight: bold;">
            Total: R$ {{ number_format($order->total / 100, 2, ',', '.') }}
        </p>

        <p style="margin-top: 24px; color: #64748b; font-size: 12px;">Atlas — e-commerce de livros.</p>
    </body>
</html>
