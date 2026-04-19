import { Link, router, useForm, usePage } from '@inertiajs/react';
import { Heart, MessageCircle, Repeat2, Share, Trash2 } from 'lucide-react';
import PostImages from '@/components/post-images';
import type { PostImageData } from '@/components/post-images';
import { show as showHashtag } from '@/routes/hashtags';
import { store as likePost, destroy as unlikePost } from '@/routes/likes';
import { show as showPost, destroy as destroyPost } from '@/routes/posts';
import {
    store as retweetPost,
    destroy as unretweetPost,
} from '@/routes/retweets';
import { show as showUser } from '@/routes/users';

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
    userProfileImageUrl?: string | null;
    images?: PostImageData[];
};

type AuthUser = { id: string } | null;

export default function PostCard({ post }: { post: Post }) {
    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;

    const { post: sendPost, delete: sendDelete, processing } = useForm();

    function handleLike() {
        if (post.likedByAuthUser) {
            sendDelete(unlikePost.url(post.id), {
                preserveScroll: true,
                preserveState: false,
            });
        } else {
            sendPost(likePost.url(post.id), {
                preserveScroll: true,
                preserveState: false,
            });
        }
    }

    function handleRetweet() {
        if (post.retweetedByAuthUser) {
            sendDelete(unretweetPost.url(post.id), {
                preserveScroll: true,
                preserveState: false,
            });
        } else {
            sendPost(retweetPost.url(post.id), {
                preserveScroll: true,
                preserveState: false,
            });
        }
    }

    function handleDelete() {
        if (confirm('この投稿を削除しますか？')) {
            sendDelete(destroyPost.url(post.id));
        }
    }

    const initial = post.userName.charAt(0).toUpperCase();

    return (
        <div
            className="w-full cursor-pointer border-b border-[#E5E7EB] p-4 transition-colors hover:bg-[#eae4dc]"
            onClick={() => router.visit(showPost.url(post.id))}
        >
            {post.retweetedByUserName && (
                <div className="mb-2 flex items-center gap-1.5 text-xs text-[#8a8784]">
                    <Repeat2 size={13} />
                    <span>
                        {post.retweetedByUserName}さんがリツイートしました
                    </span>
                </div>
            )}
            <div className="flex gap-3">
                {/* アバター 48px */}
                <Link
                    href={showUser.url(post.userId)}
                    onClick={(e) => e.stopPropagation()}
                    className="shrink-0"
                >
                    {post.userProfileImageUrl ? (
                        <img
                            src={post.userProfileImageUrl}
                            alt={post.userName}
                            className="h-12 w-12 rounded-full object-cover"
                        />
                    ) : (
                        <div
                            className="flex h-12 w-12 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white"
                            aria-hidden="true"
                        >
                            {initial}
                        </div>
                    )}
                </Link>

                {/* コンテンツ */}
                <div className="min-w-0 flex-1">
                    {/* ヘッダー行: 名前 · @handle · 時刻 */}
                    <div className="flex items-center justify-between gap-2">
                        <div className="flex min-w-0 flex-wrap items-center gap-x-1.5 gap-y-0">
                            <span className="truncate text-sm font-semibold text-[#191816]">
                                {post.userName}
                            </span>
                            <span className="shrink-0 font-mono text-sm text-[#8a8784]">
                                @{post.userHandle}
                            </span>
                            <span className="shrink-0 text-sm text-[#8a8784]">
                                · {post.createdAt}
                            </span>
                        </div>
                        {authUser?.id === post.userId && (
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleDelete();
                                }}
                                disabled={processing}
                                className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[#8a8784] transition-colors hover:bg-[#eae4dc] hover:text-[#b36b09]"
                                aria-label="削除"
                            >
                                <Trash2 size={16} />
                            </button>
                        )}
                    </div>

                    {/* 本文 */}
                    <p className="mt-1 text-base leading-6 break-words whitespace-pre-wrap text-[#2b2a28]">
                        {post.content.split(/(#[\w\p{L}]+)/u).map((part, i) =>
                            part.startsWith('#') ? (
                                <Link
                                    key={i}
                                    href={showHashtag.url(part.slice(1))}
                                    className="text-[#3a6c72] hover:underline"
                                    onClick={(e) => e.stopPropagation()}
                                >
                                    {part}
                                </Link>
                            ) : (
                                part
                            ),
                        )}
                    </p>

                    {/* 画像 */}
                    {post.images && post.images.length > 0 && (
                        <PostImages images={post.images} />
                    )}

                    {/* アクション行 */}
                    <div className="mt-3 flex items-center gap-6">
                        {/* リプライ */}
                        <Link
                            href={showPost.url(post.id)}
                            onClick={(e) => e.stopPropagation()}
                            className="flex items-center gap-1.5 text-sm text-[#8a8784] transition-colors hover:text-[#3a6c72]"
                        >
                            <MessageCircle size={16} />
                            <span>{post.repliesCount}</span>
                        </Link>

                        {/* リツイート */}
                        {authUser ? (
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleRetweet();
                                }}
                                disabled={processing}
                                className={`flex items-center gap-1.5 text-sm transition-colors disabled:opacity-50 ${
                                    post.retweetedByAuthUser
                                        ? 'text-[#3a6c72]'
                                        : 'text-[#8a8784] hover:text-[#3a6c72]'
                                }`}
                                aria-label="リツイート"
                            >
                                <Repeat2 size={18} />
                                <span>{post.retweetsCount}</span>
                            </button>
                        ) : (
                            <span className="flex items-center gap-1.5 text-sm text-[#8a8784]">
                                <Repeat2 size={18} />
                                <span>{post.retweetsCount}</span>
                            </span>
                        )}

                        {/* いいね */}
                        {authUser ? (
                            <button
                                onClick={(e) => {
                                    e.stopPropagation();
                                    handleLike();
                                }}
                                disabled={processing}
                                className={`flex items-center gap-1.5 text-sm transition-colors disabled:opacity-50 ${
                                    post.likedByAuthUser
                                        ? 'text-[#b36b09]'
                                        : 'text-[#8a8784] hover:text-[#b36b09]'
                                }`}
                                aria-label="いいね"
                            >
                                <Heart
                                    size={16}
                                    fill={
                                        post.likedByAuthUser
                                            ? 'currentColor'
                                            : 'none'
                                    }
                                />
                                <span>{post.likesCount}</span>
                            </button>
                        ) : (
                            <span className="flex items-center gap-1.5 text-sm text-[#8a8784]">
                                <Heart size={16} />
                                <span>{post.likesCount}</span>
                            </span>
                        )}

                        {/* シェア（ダミー） */}
                        <span className="flex items-center text-sm text-[#8a8784]">
                            <Share size={16} />
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
