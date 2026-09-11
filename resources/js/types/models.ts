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

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}
