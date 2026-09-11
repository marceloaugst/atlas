export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    active: boolean;
}

export interface Author {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

export interface Product {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    isbn: string | null;
    publisher: string | null;
    published_at: string | null;
    pages: number | null;
    cover_url: string | null;
    price: number;
    stock: number;
    active: boolean;
    featured: boolean;
    category: Category;
    authors: Author[];
}

export interface CartItem {
    id: number;
    cart_id: number;
    product_id: number;
    quantity: number;
    unit_price: number;
    product: Product;
}

export interface Cart {
    id: number;
    items: CartItem[];
}

export interface GoogleBook {
    googleBooksId: string;
    title: string;
    authors: string[];
    isbn: string | null;
    publisher: string | null;
    publishedAt: string | null;
    pages: number | null;
    description: string | null;
    coverUrl: string | null;
    categories: string[];
}

export interface Coupon {
    id: number;
    code: string;
    type: 'PERCENTAGE' | 'FIXED';
    value: number;
    minimum_amount: number | null;
    maximum_discount: number | null;
    starts_at: string | null;
    expires_at: string | null;
    usage_limit: number | null;
    usage_count: number;
    active: boolean;
}

export interface CheckoutSummary {
    subtotal: number;
    discount: number;
    shipping: number;
    total: number;
    coupon: string | null;
}

export interface OrderItem {
    id: number;
    product_id: number;
    product_title: string;
    unit_price: number;
    quantity: number;
    subtotal: number;
}

export interface Order {
    uuid: string;
    status: string;
    subtotal: number;
    discount: number;
    shipping: number;
    total: number;
    customer_name: string;
    customer_email: string;
    placed_at: string;
    items?: OrderItem[];
    items_count?: number;
    address: {
        street: string;
        number: string;
        neighborhood: string;
        city: string;
        state: string;
        zip_code: string;
    };
    payment: {
        method: string;
        status: string;
    } | null;
    user?: { id: number; name: string; email: string } | null;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}
