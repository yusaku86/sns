import { Head } from '@inertiajs/react';
import PostCard from '@/components/post-card';
import PostForm from '@/components/post-form';

type Post = {
    id: string;
    userId: string;
    userName: string;
    content: string;
    createdAt: string;
    likesCount: number;
    likedByAuthUser: boolean;
};

export default function Timeline({ posts }: { posts: Post[] }) {
    return (
        <>
            <Head title="タイムライン" />
            <div className="mx-auto max-w-xl">
                <PostForm />
                {posts.length === 0 ? (
                    <p className="p-8 text-center text-sm text-muted-foreground">
                        フォローしているユーザーの投稿がありません。
                    </p>
                ) : (
                    posts.map((post) => <PostCard key={post.id} post={post} />)
                )}
            </div>
        </>
    );
}
