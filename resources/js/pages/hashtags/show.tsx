import { Head, usePage } from '@inertiajs/react';
import { useCallback, useRef, useState } from 'react';
import PostCard from '@/components/post-card';
import RightSidebar from '@/components/right-sidebar';
import { useInfiniteScroll } from '@/hooks/use-infinite-scroll';

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
    hashtags: string[];
};

export default function HashtagShow({
    hashtag,
    posts,
    nextCursor,
    hasMore,
}: {
    hashtag: string;
    posts: Post[];
    nextCursor: string | null;
    hasMore: boolean;
}) {
    const { version, component } = usePage();
    const [allPosts, setAllPosts] = useState<Post[]>(posts);
    const [hasMoreState, setHasMoreState] = useState(hasMore);
    const cursorRef = useRef<string | null>(nextCursor);
    const loadingRef = useRef(false);

    const loadMore = useCallback(async () => {
        if (!cursorRef.current || loadingRef.current) {
            return;
        }

        loadingRef.current = true;

        try {
            const url = new URL(
                window.location.pathname,
                window.location.origin,
            );
            url.searchParams.set('cursor', cursorRef.current);

            const response = await fetch(url.toString(), {
                credentials: 'same-origin',
                headers: {
                    'X-Inertia': 'true',
                    'X-Inertia-Version': version ?? '',
                    'X-Inertia-Partial-Component': component,
                    'X-Inertia-Partial-Data': 'posts,nextCursor,hasMore',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                return;
            }

            const data = await response.json();
            const newPosts: Post[] = data.props.posts;
            const newCursor: string | null = data.props.nextCursor;
            const newHasMore: boolean = data.props.hasMore;

            setAllPosts((prev) => [...prev, ...newPosts]);
            cursorRef.current = newCursor;
            setHasMoreState(newHasMore);
        } finally {
            loadingRef.current = false;
        }
    }, [version, component]);

    const sentinelRef = useInfiniteScroll(loadMore, hasMoreState);

    return (
        <>
            <Head title={`#${hashtag}`} />
            <div className="mx-auto flex w-full max-w-5xl gap-8 overflow-x-hidden px-4">
                {/* メインコンテンツ */}
                <div className="min-w-0 flex-1">
                    <h1 className="border-b border-[#E5E7EB] py-4 text-xl font-semibold text-[#191816]">
                        #{hashtag}
                    </h1>
                    {allPosts.length === 0 ? (
                        <p className="p-8 text-center text-sm text-muted-foreground">
                            このハッシュタグの投稿はまだありません。
                        </p>
                    ) : (
                        allPosts.map((post) => (
                            <PostCard
                                key={post.retweetId ?? post.id}
                                post={post}
                            />
                        ))
                    )}
                    <div ref={sentinelRef} />
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
