export interface CartSummary {
    count: number;
    subtotal: number;
}

export interface SharedProps {
    cart: CartSummary;
    [key: string]: unknown;
}
