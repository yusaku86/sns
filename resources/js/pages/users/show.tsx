import { Head, usePage } from '@inertiajs/react';
import FollowButton from '@/components/follow-button';
import RightSidebar from '@/components/right-sidebar';

type UserProfile = {
    id: string;
    name: string;
    handle: string;
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

    const initial = user.name.charAt(0).toUpperCase();

    return (
        <>
            <Head title={user.name} />
            <div className="mx-auto flex max-w-5xl gap-8 px-4">
                {/* メインコンテンツ */}
                <div className="min-w-0 flex-1">
                    {/* プロフィールヘッダー */}
                    <div className="border-b border-[#E5E7EB] py-6">
                        <div className="flex items-start justify-between gap-4">
                            {/* アバター 96px */}
                            <div
                                className="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-[#3a6c72] text-2xl font-semibold text-white"
                                aria-hidden="true"
                            >
                                {initial}
                            </div>

                            {/* プロフィール情報 */}
                            <div className="min-w-0 flex-1">
                                <div className="flex items-start justify-between gap-2">
                                    <div>
                                        <h1 className="text-xl font-semibold text-[#191816]">
                                            {user.name}
                                        </h1>
                                        <p className="font-mono text-sm text-[#8a8784]">
                                            @{user.handle}
                                        </p>
                                    </div>
                                    {authUser && !isOwnProfile && (
                                        <FollowButton
                                            userId={user.id}
                                            isFollowing={
                                                user.isFollowedByAuthUser
                                            }
                                        />
                                    )}
                                </div>

                                {user.bio && (
                                    <p className="mt-2 text-base leading-6 whitespace-pre-wrap text-[#2b2a28]">
                                        {user.bio}
                                    </p>
                                )}

                                {/* 統計情報 */}
                                <div className="mt-3 flex gap-6">
                                    <div>
                                        <span className="text-base font-semibold text-[#191816]">
                                            {user.postsCount}
                                        </span>
                                        <span className="ml-1 text-sm text-[#8a8784]">
                                            投稿
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-base font-semibold text-[#191816]">
                                            {user.followersCount}
                                        </span>
                                        <span className="ml-1 text-sm text-[#8a8784]">
                                            フォロワー
                                        </span>
                                    </div>
                                    <div>
                                        <span className="text-base font-semibold text-[#191816]">
                                            {user.followingCount}
                                        </span>
                                        <span className="ml-1 text-sm text-[#8a8784]">
                                            フォロー中
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 右サイドバー */}
                <aside className="hidden w-72 shrink-0 lg:block">
                    <div className="sticky top-4">
                        <RightSidebar />
                    </div>
                </aside>
            </div>
        </>
    );
}
