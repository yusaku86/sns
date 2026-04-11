import { useForm, usePage } from '@inertiajs/react';
import { Heart, Trash2 } from 'lucide-react';
import { store as likePost, destroy as unlikePost } from '@/routes/likes';
import { destroy as destroyPost } from '@/routes/posts';

type Post = {
    id: string;
    userId: string;
    userName: string;
    content: string;
    createdAt: string;
    likesCount: number;
    likedByAuthUser: boolean;
};

type AuthUser = { id: string } | null;

export default function PostCard({ post }: { post: Post }) {
    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;

    const { post: sendPost, delete: sendDelete, processing } = useForm();

    function handleLike() {
        if (post.likedByAuthUser) {
            sendDelete(unlikePost.url(post.id));
        } else {
            sendPost(likePost.url(post.id));
        }
    }

    function handleDelete() {
        if (confirm('この投稿を削除しますか？')) {
            sendDelete(destroyPost.url(post.id));
        }
    }

    return (
        <div className="border-b border-border p-4">
            <div className="flex items-start justify-between gap-2">
                <div className="min-w-0 flex-1">
                    <span className="text-sm font-semibold">
                        {post.userName}
                    </span>
                    <span className="ml-2 text-xs text-muted-foreground">
                        {post.createdAt}
                    </span>
                    <p className="mt-1 text-sm break-words whitespace-pre-wrap">
                        {post.content}
                    </p>
                </div>
                {authUser?.id === post.userId && (
                    <button
                        onClick={handleDelete}
                        disabled={processing}
                        className="shrink-0 text-muted-foreground hover:text-destructive"
                        aria-label="削除"
                    >
                        <Trash2 size={14} />
                    </button>
                )}
            </div>

            <div className="mt-2 flex items-center gap-1">
                {authUser ? (
                    <button
                        onClick={handleLike}
                        disabled={processing}
                        className={`flex items-center gap-1 text-xs transition-colors ${
                            post.likedByAuthUser
                                ? 'text-red-500'
                                : 'text-muted-foreground hover:text-red-500'
                        }`}
                        aria-label="いいね"
                    >
                        <Heart
                            size={14}
                            fill={
                                post.likedByAuthUser ? 'currentColor' : 'none'
                            }
                        />
                        {post.likesCount}
                    </button>
                ) : (
                    <span className="flex items-center gap-1 text-xs text-muted-foreground">
                        <Heart size={14} />
                        {post.likesCount}
                    </span>
                )}
            </div>
        </div>
    );
}
