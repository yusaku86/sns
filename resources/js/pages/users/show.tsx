import { Head, usePage } from '@inertiajs/react';
import { CalendarDays, Pencil } from 'lucide-react';
import { useState } from 'react';
import EditProfileModal from '@/components/edit-profile-modal';
import FollowButton from '@/components/follow-button';
import FollowUserListModal from '@/components/follow-user-list-modal';
import type { FollowUser } from '@/components/follow-user-list-modal';
import PostCard from '@/components/post-card';
import RightSidebar from '@/components/right-sidebar';

type UserProfile = {
    id: string;
    name: string;
    handle: string;
    bio: string | null;
    headerImageUrl: string | null;
    profileImageUrl: string | null;
    postsCount: number;
    followersCount: number;
    followingCount: number;
    isFollowedByAuthUser: boolean;
    createdAt: string | null;
};

type Post = {
    id: string;
    userId: string;
    userName: string;
    userHandle: string;
    content: string;
    createdAt: string;
    likesCount: number;
    likedByAuthUser: boolean;
    repliesCount: number;
    retweetsCount: number;
    retweetedByAuthUser: boolean;
    retweetId?: string | null;
    retweetedByUserName?: string | null;
    retweetedByUserHandle?: string | null;
    hashtags?: string[];
};

type Reply = {
    id: string;
    postId: string;
    userId: string;
    userName: string;
    userHandle: string;
    content: string;
    createdAt: string;
    postContent: string | null;
    postUserName: string | null;
    postUserHandle: string | null;
    userProfileImageUrl?: string | null;
};

type AuthUser = { id: string } | null;

type Tab = 'posts' | 'replies' | 'likes';

type FollowModal = 'followers' | 'following' | null;

function formatJoinDate(createdAt: string | null): string | null {
    if (!createdAt) {
        return null;
    }

    const date = new Date(createdAt);

    if (isNaN(date.getTime())) {
        return null;
    }

    return `${date.getFullYear()}年${date.getMonth() + 1}月から利用中`;
}

function ReplyWithContext({ reply }: { reply: Reply }) {
    const initial = reply.userName.charAt(0).toUpperCase();

    return (
        <div className="border-b border-[#E5E7EB] p-4">
            {/* 元の投稿（文脈） */}
            {reply.postContent && (
                <div className="mb-3 rounded-xl border border-[#E5E7EB] bg-[#f6f3ee] p-3">
                    <div className="flex flex-wrap items-center gap-x-1.5 gap-y-0 text-sm">
                        <span className="font-semibold text-[#191816]">
                            {reply.postUserName}
                        </span>
                        {reply.postUserHandle && (
                            <span className="font-mono text-[#8a8784]">
                                @{reply.postUserHandle}
                            </span>
                        )}
                    </div>
                    <p className="mt-1 line-clamp-3 text-sm leading-5 break-words text-[#2b2a28]">
                        {reply.postContent}
                    </p>
                </div>
            )}

            {/* リプライ本体 */}
            <div className="flex gap-3">
                {reply.userProfileImageUrl ? (
                    <img
                        src={reply.userProfileImageUrl}
                        alt={reply.userName}
                        className="h-10 w-10 shrink-0 rounded-full object-cover"
                    />
                ) : (
                    <div
                        className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white"
                        aria-hidden="true"
                    >
                        {initial}
                    </div>
                )}
                <div className="min-w-0 flex-1">
                    <div className="flex flex-wrap items-center gap-x-1.5 gap-y-0">
                        <span className="truncate text-sm font-semibold text-[#191816]">
                            {reply.userName}
                        </span>
                        <span className="shrink-0 font-mono text-sm text-[#8a8784]">
                            @{reply.userHandle}
                        </span>
                        <span className="shrink-0 text-sm text-[#8a8784]">
                            · {reply.createdAt}
                        </span>
                    </div>
                    <p className="mt-1 text-base leading-6 break-words whitespace-pre-wrap text-[#2b2a28]">
                        {reply.content}
                    </p>
                </div>
            </div>
        </div>
    );
}

export default function UserShow({
    user,
    posts,
    replies,
    likedPosts,
    followers,
    following,
}: {
    user: UserProfile;
    posts: Post[];
    replies: Reply[];
    likedPosts: Post[];
    followers: FollowUser[] | undefined;
    following: FollowUser[] | undefined;
}) {
    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;
    const isOwnProfile = authUser?.id === user.id;

    const [activeTab, setActiveTab] = useState<Tab>('posts');
    const [openModal, setOpenModal] = useState<FollowModal>(null);

    const initial = user.name.charAt(0).toUpperCase();
    const joinDate = formatJoinDate(user.createdAt);

    const tabs: { key: Tab; label: string; count: number }[] = [
        { key: 'posts', label: '投稿', count: posts.length },
        { key: 'replies', label: 'リプライ', count: replies.length },
        { key: 'likes', label: 'いいね', count: likedPosts.length },
    ];

    return (
        <>
            <Head title={user.name} />
            <div className="mx-auto flex max-w-5xl gap-8 px-4">
                {/* メインコンテンツ */}
                <div className="min-w-0 flex-1">
                    {/* ヘッダー画像 */}
                    <div className="relative">
                        {user.headerImageUrl ? (
                            <img
                                src={user.headerImageUrl}
                                alt={`${user.name}のヘッダー画像`}
                                className="h-40 w-full rounded-b-none object-cover"
                            />
                        ) : (
                            <div className="h-40 w-full bg-[#3a6c72]/20" />
                        )}
                    </div>

                    {/* プロフィールヘッダー */}
                    <div className="border-b border-[#E5E7EB] px-4 pb-4">
                        <div className="flex items-end justify-between gap-4">
                            {/* アバター 96px（ヘッダー画像に半分かかる） */}
                            <div className="relative z-10 -mt-12 flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-full border-4 border-[#f6f3ee] bg-[#3a6c72] text-2xl font-semibold text-white">
                                {user.profileImageUrl ? (
                                    <img
                                        src={user.profileImageUrl}
                                        alt={`${user.name}のプロフィール画像`}
                                        className="h-full w-full object-cover"
                                    />
                                ) : (
                                    <span aria-hidden="true">{initial}</span>
                                )}
                            </div>

                            {/* ボタン群 */}
                            <div className="flex items-center gap-2 pt-2">
                                {isOwnProfile ? (
                                    <EditProfileModal user={user}>
                                        <button
                                            type="button"
                                            className="flex h-9 items-center gap-1.5 rounded-full border border-[#3a6c72] px-4 text-sm font-semibold text-[#3a6c72] transition-colors hover:bg-[#3a6c72]/10"
                                            aria-label="プロフィールを修正"
                                        >
                                            <Pencil size={14} />
                                            プロフィールを修正
                                        </button>
                                    </EditProfileModal>
                                ) : (
                                    authUser && (
                                        <FollowButton
                                            userId={user.id}
                                            isFollowing={
                                                user.isFollowedByAuthUser
                                            }
                                        />
                                    )
                                )}
                            </div>
                        </div>

                        {/* プロフィール情報 */}
                        <div className="mt-3">
                            <h1 className="text-xl font-semibold text-[#191816]">
                                {user.name}
                            </h1>
                            <p className="font-mono text-sm text-[#8a8784]">
                                @{user.handle}
                            </p>

                            {user.bio && (
                                <p className="mt-2 text-base leading-6 whitespace-pre-wrap text-[#2b2a28]">
                                    {user.bio}
                                </p>
                            )}

                            {/* 参加日 */}
                            {joinDate && (
                                <div className="mt-2 flex items-center gap-1.5 text-sm text-[#8a8784]">
                                    <CalendarDays size={15} />
                                    <span>{joinDate}</span>
                                </div>
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
                                <button
                                    type="button"
                                    onClick={() => setOpenModal('followers')}
                                    className="hover:underline"
                                >
                                    <span className="text-base font-semibold text-[#191816]">
                                        {user.followersCount}
                                    </span>
                                    <span className="ml-1 text-sm text-[#8a8784]">
                                        フォロワー
                                    </span>
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setOpenModal('following')}
                                    className="hover:underline"
                                >
                                    <span className="text-base font-semibold text-[#191816]">
                                        {user.followingCount}
                                    </span>
                                    <span className="ml-1 text-sm text-[#8a8784]">
                                        フォロー中
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* タブナビゲーション */}
                    <div className="flex border-b border-[#E5E7EB]">
                        {tabs.map((tab) => (
                            <button
                                key={tab.key}
                                type="button"
                                onClick={() => setActiveTab(tab.key)}
                                className={`flex h-11 flex-1 items-center justify-center gap-1.5 text-sm font-medium transition-colors ${
                                    activeTab === tab.key
                                        ? 'border-b-2 border-[#3a6c72] text-[#3a6c72]'
                                        : 'text-[#8a8784] hover:bg-[#eae4dc] hover:text-[#2b2a28]'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {/* タブコンテンツ */}
                    {activeTab === 'posts' && (
                        <div>
                            {posts.length === 0 ? (
                                <p className="p-8 text-center text-sm text-[#8a8784]">
                                    まだ投稿がありません。
                                </p>
                            ) : (
                                posts.map((post) => (
                                    <PostCard key={post.id} post={post} />
                                ))
                            )}
                        </div>
                    )}

                    {activeTab === 'replies' && (
                        <div>
                            {replies.length === 0 ? (
                                <p className="p-8 text-center text-sm text-[#8a8784]">
                                    まだリプライがありません。
                                </p>
                            ) : (
                                replies.map((reply) => (
                                    <ReplyWithContext
                                        key={reply.id}
                                        reply={reply}
                                    />
                                ))
                            )}
                        </div>
                    )}

                    {activeTab === 'likes' && (
                        <div>
                            {likedPosts.length === 0 ? (
                                <p className="p-8 text-center text-sm text-[#8a8784]">
                                    まだいいねがありません。
                                </p>
                            ) : (
                                likedPosts.map((post) => (
                                    <PostCard key={post.id} post={post} />
                                ))
                            )}
                        </div>
                    )}
                </div>

                {/* 右サイドバー */}
                <aside className="hidden w-72 shrink-0 lg:block">
                    <div className="sticky top-4">
                        <RightSidebar />
                    </div>
                </aside>
            </div>

            {/* フォロワー/フォロー中モーダル */}
            <FollowUserListModal
                title={`${user.name}のフォロワー`}
                users={followers}
                authUserId={authUser?.id}
                open={openModal === 'followers'}
                onOpenChange={(open) => setOpenModal(open ? 'followers' : null)}
            />
            <FollowUserListModal
                title={`${user.name}のフォロー中`}
                users={following}
                authUserId={authUser?.id}
                open={openModal === 'following'}
                onOpenChange={(open) => setOpenModal(open ? 'following' : null)}
            />
        </>
    );
}
