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
    qr_code_path: string | null;
    qr_code_url: string | null;
    custom_thank_you_message: string | null;
    brand_color_primary: string;
    brand_color_secondary: string;
    auto_approve_feedback: boolean;
    require_customer_name: boolean;
    feedback_email_notifications: boolean;
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

export type FeedbackSentiment = 'positive' | 'neutral' | 'negative';
export type ModerationStatus = 'pending' | 'approved' | 'rejected';

export interface Feedback {
    id: number;
    business_id: number;
    business?: Business;
    customer_name: string | null;
    customer_email: string | null;
    rating: number; // 1-5
    comment: string | null;
    sentiment: FeedbackSentiment | null;
    moderation_status: ModerationStatus;
    is_public: boolean;
    replied_at: string | null;
    reply_text: string | null;
    submitted_at: string;
    ip_address: string | null;
    user_agent: string | null;
    created_at: string;
    updated_at: string;
}

export interface FeedbackFormData {
    customer_name?: string;
    customer_email?: string;
    rating: number;
    comment?: string;
}

export interface FeedbackStats {
    total_count: number;
    average_rating: number;
    rating_distribution: {
        1: number;
        2: number;
        3: number;
        4: number;
        5: number;
    };
    sentiment_distribution: {
        positive: number;
        neutral: number;
        negative: number;
    };
    recent_feedback: Feedback[];
}

export interface QRCodeSettings {
    size: number;
    foreground_color: string;
    background_color: string;
    include_logo: boolean;
}

export interface BusinessSettings {
    custom_thank_you_message: string | null;
    brand_color_primary: string;
    brand_color_secondary: string;
    auto_approve_feedback: boolean;
    require_customer_name: boolean;
    feedback_email_notifications: boolean;
}
