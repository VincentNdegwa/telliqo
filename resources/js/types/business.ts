export interface BusinessCategory {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    description: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Business {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    category_id: number;
    category?: BusinessCategory;
    email: string;
    phone: string | null;
    address: string | null;
    city: string | null;
    state: string | null;
    country: string | null;
    postal_code: string | null;
    website: string | null;
    logo: string | null;
    is_active: boolean;
    onboarding_completed_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface BusinessUser {
    id: number;
    business_id: number;
    user_id: number;
    role: 'owner' | 'admin' | 'member';
    is_active: boolean;
    invited_at: string | null;
    joined_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface BusinessFormData {
    name: string;
    description: string;
    category_id: number | null;
    email: string;
    phone: string;
    address: string;
    city: string;
    state: string;
    country: string;
    postal_code: string;
    website: string;
}
