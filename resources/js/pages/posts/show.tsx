import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { store as storeReply } from '@/routes/replies';
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
    userProfileImageUrl?: string | null;
};

type Reply = {
    id: string;
    postId: string;
    userId: string;
    userName: string;
    userHandle: string;
    content: string;
    createdAt: string;
};

type AuthUser = { id: string; name: string } | null;

function ReplyForm({ postId }: { postId: string }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        content: '',
    });

    const remaining = 140 - data.content.length;

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(storeReply.url(postId), { onSuccess: () => reset() });
    }

    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;
    const initial = authUser?.name?.charAt(0).toUpperCase() ?? '?';

    return (
        <form onSubmit={handleSubmit} className="border-b border-[#E5E7EB] p-4">
            <div className="flex gap-3">
                <div
                    className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white"
                    aria-hidden="true"
                >
                    {initial}
                </div>
                <div className="min-w-0 flex-1">
                    <textarea
                        value={data.content}
                        onChange={(e) => setData('content', e.target.value)}
                        placeholder="返信する..."
                        rows={2}
                        className="w-full resize-none rounded-md bg-[#eae4dc] p-3 text-base leading-6 text-[#2b2a28] outline-none placeholder:text-[#8a8784] focus:ring-0"
                        maxLength={140}
                    />
                    {errors.content && (
                        <p className="mt-1 text-xs text-[#b36b09]">
                            {errors.content}
                        </p>
                    )}
                    <div className="mt-2 flex items-center justify-between">
                        <span
                            className={`text-sm ${remaining < 20 ? 'text-[#b36b09]' : 'text-[#8a8784]'}`}
                        >
                            {remaining}
                        </span>
                        <button
                            type="submit"
                            disabled={processing || data.content.trim() === ''}
                            className="h-9 rounded-md bg-[#3a6c72] px-4 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            返信
                        </button>
                    </div>
                </div>
            </div>
        </form>
    );
}

function ReplyCard({ reply }: { reply: Reply }) {
    const initial = reply.userName.charAt(0).toUpperCase();

    return (
        <div className="border-b border-[#E5E7EB] p-4">
            <div className="flex gap-3">
                <div
                    className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white"
                    aria-hidden="true"
                >
                    {initial}
                </div>
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

export default function PostShow({
    post,
    replies,
}: {
    post: Post;
    replies: Reply[];
}) {
    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;
    const initial = post.userName.charAt(0).toUpperCase();

    return (
        <>
            <Head title={`${post.userName}の投稿`} />
            <div className="mx-auto w-full max-w-2xl">
                {/* 元の投稿 */}
                <div className="border-b border-[#E5E7EB] p-4">
                    <div className="flex gap-3">
                        <Link
                            href={showUser.url(post.userId)}
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
                        <div className="min-w-0 flex-1">
                            <div className="flex flex-wrap items-center gap-x-1.5 gap-y-0">
                                <span className="truncate text-sm font-semibold text-[#191816]">
                                    {post.userName}
                                </span>
                                <span className="shrink-0 font-mono text-sm text-[#8a8784]">
                                    @{post.userHandle}
                                </span>
                            </div>
                            <p className="mt-3 text-xl leading-7 break-words whitespace-pre-wrap text-[#2b2a28]">
                                {post.content}
                            </p>
                            <p className="mt-3 text-sm text-[#8a8784]">
                                {post.createdAt}
                            </p>
                            <div className="mt-3 border-t border-[#E5E7EB] pt-3 text-sm text-[#8a8784]">
                                <span>
                                    <strong className="text-[#191816]">
                                        {post.repliesCount}
                                    </strong>{' '}
                                    返信
                                </span>
                                <span className="ml-4">
                                    <strong className="text-[#191816]">
                                        {post.likesCount}
                                    </strong>{' '}
                                    いいね
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {/* 返信フォーム（ログイン済みのみ） */}
                {authUser && <ReplyForm postId={post.id} />}

                {/* 返信一覧 */}
                {replies.length === 0 ? (
                    <p className="p-8 text-center text-sm text-[#8a8784]">
                        まだ返信がありません。
                    </p>
                ) : (
                    replies.map((reply) => (
                        <ReplyCard key={reply.id} reply={reply} />
                    ))
                )}
            </div>
        </>
    );
}
