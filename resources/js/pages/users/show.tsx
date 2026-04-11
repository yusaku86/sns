import { Head, usePage } from '@inertiajs/react';
import FollowButton from '@/components/follow-button';

type UserProfile = {
    id: string;
    name: string;
    email: string;
    bio: string | null;
    postsCount: number;
    followersCount: number;
    followingCount: number;
    isFollowedByAuthUser: boolean;
};

type AuthUser = { id: string } | null;

export default function UserShow({ user }: { user: UserProfile }) {
    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;
    const isOwnProfile = authUser?.id === user.id;

    return (
        <>
            <Head title={user.name} />
            <div className="mx-auto max-w-xl">
                {/* プロフィールヘッダー */}
                <div className="border-b border-border p-6">
                    <div className="flex items-start justify-between">
                        <div>
                            <h1 className="text-xl font-bold">{user.name}</h1>
                            {user.bio && (
                                <p className="mt-2 text-sm whitespace-pre-wrap text-muted-foreground">
                                    {user.bio}
                                </p>
                            )}
                            <div className="mt-3 flex gap-4 text-sm">
                                <span>
                                    <strong>{user.postsCount}</strong>{' '}
                                    <span className="text-muted-foreground">
                                        投稿
                                    </span>
                                </span>
                                <span>
                                    <strong>{user.followersCount}</strong>{' '}
                                    <span className="text-muted-foreground">
                                        フォロワー
                                    </span>
                                </span>
                                <span>
                                    <strong>{user.followingCount}</strong>{' '}
                                    <span className="text-muted-foreground">
                                        フォロー中
                                    </span>
                                </span>
                            </div>
                        </div>

                        {authUser && !isOwnProfile && (
                            <FollowButton
                                userId={user.id}
                                isFollowing={user.isFollowedByAuthUser}
                            />
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
