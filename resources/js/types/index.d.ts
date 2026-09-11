export interface CartSummary {
    count: number;
    subtotal: number;
}

export interface AuthUser {
    id: number;
    name: string;
    email: string;
}

export interface SharedProps {
    cart: CartSummary;
    auth: {
        user: AuthUser | null;
    };
    [key: string]: unknown;
}
