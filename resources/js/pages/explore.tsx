import { Head } from '@inertiajs/react';
import PostCard from '@/components/post-card';

type Post = {
    id: string;
    userId: string;
    userName: string;
    content: string;
    createdAt: string;
    likesCount: number;
    likedByAuthUser: boolean;
};

export default function Explore({ posts }: { posts: Post[] }) {
    return (
        <>
            <Head title="みんなの投稿" />
            <div className="mx-auto max-w-xl">
                <h1 className="border-b border-border p-4 text-lg font-bold">
                    みんなの投稿
                </h1>
                {posts.length === 0 ? (
                    <p className="p-8 text-center text-sm text-muted-foreground">
                        まだ投稿がありません。
                    </p>
                ) : (
                    posts.map((post) => <PostCard key={post.id} post={post} />)
                )}
            </div>
        </>
    );
}
