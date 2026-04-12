import { Head } from '@inertiajs/react';
import PostCard from '@/components/post-card';
import RightSidebar from '@/components/right-sidebar';

type Post = {
    id: string;
    userId: string;
    userName: string;
    userHandle: string;
    content: string;
    createdAt: string;
    likesCount: number;
    likedByAuthUser: boolean;
    retweetId?: string | null;
    retweetedByUserName?: string | null;
    retweetedByUserHandle?: string | null;
};

export default function Explore({ posts }: { posts: Post[] }) {
    return (
        <>
            <Head title="みんなの投稿" />
            <div className="mx-auto flex max-w-5xl gap-8 px-4">
                {/* メインコンテンツ */}
                <div className="min-w-0 flex-1">
                    <h1 className="border-b border-[#E5E7EB] py-4 text-xl font-semibold text-[#191816]">
                        みんなの投稿
                    </h1>
                    {posts.length === 0 ? (
                        <p className="p-8 text-center text-sm text-muted-foreground">
                            まだ投稿がありません。
                        </p>
                    ) : (
                        posts.map((post) => (
                            <PostCard
                                key={post.retweetId ?? post.id}
                                post={post}
                            />
                        ))
                    )}
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
