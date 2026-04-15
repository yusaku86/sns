export type User = {
    id: string;
    name: string;
    email: string;
    email_verified_at: string | null;
    profile_image_url: string | null;
    two_factor_enabled: boolean;
};

export type Auth = {
    user: User;
};

export type TwoFactorSetupData = {
    svg: string;
    url: string;
};

export type TwoFactorSecretKey = {
    secretKey: string;
};
