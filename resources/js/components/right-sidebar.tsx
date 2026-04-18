import { Link, router, usePage } from '@inertiajs/react';
import { Search } from 'lucide-react';
import { useEffect, useState } from 'react';
import { explore } from '@/routes';
import { store as followUser } from '@/routes/follows';
import { show as showHashtag } from '@/routes/hashtags';
import { show as showUser } from '@/routes/users';

type TrendingHashtag = {
    id: string;
    name: string;
    postsCount: number;
};

type SuggestedUser = {
    id: string;
    name: string;
    handle: string;
    profileImageUrl: string | null;
    isFollowedByAuthUser: boolean;
};

export default function RightSidebar() {
    const page = usePage<{
        trendingHashtags: TrendingHashtag[];
        suggestedUsers: SuggestedUser[];
        query?: string;
        auth: { user: { id: string } | null };
    }>();
    const { trendingHashtags, suggestedUsers, auth } = page.props;
    const currentQuery = page.props.query ?? '';

    const [searchInput, setSearchInput] = useState(currentQuery);

    useEffect(() => {
        setSearchInput(currentQuery);
    }, [currentQuery]);

    const handleSearch = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        const q = searchInput.trim();

        if (q === '') {
            router.visit(explore.url());
        } else {
            router.visit(explore.url({ query: { q } }));
        }
    };

    const handleFollow = (userId: string) => {
        router.post(followUser.url(userId));
    };

    return (
        <div className="space-y-6 py-4">
            {/* 検索バー */}
            <form
                onSubmit={handleSearch}
                className="flex items-center gap-2 rounded-md bg-[#eae4dc] px-3 py-3"
            >
                <Search size={17} className="shrink-0 text-[#8a8784]" />
                <input
                    type="text"
                    value={searchInput}
                    onChange={(e) => setSearchInput(e.target.value)}
                    placeholder="キーワードを検索"
                    className="flex-1 bg-transparent text-base text-[#191816] placeholder-[#8a8784] outline-none"
                />
            </form>

            {/* おすすめユーザー */}
            {auth.user && suggestedUsers.length > 0 && (
                <div>
                    <h2 className="mb-3 text-lg font-bold text-[#191816]">
                        フォロワーも知っている
                    </h2>
                    <div className="space-y-4 rounded-md bg-[#eae4dc] p-4">
                        {suggestedUsers.map((user) => (
                            <div
                                key={user.id}
                                className="flex items-center justify-between gap-2"
                            >
                                <Link
                                    href={showUser.url(user.id)}
                                    className="flex min-w-0 items-center gap-3 hover:opacity-75"
                                >
                                    {user.profileImageUrl ? (
                                        <img
                                            src={user.profileImageUrl}
                                            alt={user.name}
                                            className="h-10 w-10 shrink-0 rounded-full object-cover"
                                        />
                                    ) : (
                                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white">
                                            {user.name.charAt(0)}
                                        </div>
                                    )}
                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-semibold text-[#191816]">
                                            {user.name}
                                        </p>
                                        <p className="font-mono text-xs text-[#8a8784]">
                                            @{user.handle}
                                        </p>
                                    </div>
                                </Link>
                                <button
                                    onClick={() => handleFollow(user.id)}
                                    className="shrink-0 rounded-md border border-[#3a6c72] px-3 py-1 text-sm font-semibold text-[#3a6c72] transition-colors hover:bg-[#3a6c72] hover:text-white"
                                >
                                    フォロー
                                </button>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            {/* トレンド */}
            <div>
                <h2 className="mb-3 text-lg font-bold text-[#191816]">
                    トレンド
                </h2>
                <div className="space-y-4 rounded-md bg-[#eae4dc] p-4">
                    {trendingHashtags.length === 0 ? (
                        <p className="text-sm text-[#8a8784]">
                            トレンドはまだありません
                        </p>
                    ) : (
                        trendingHashtags.map((trend) => (
                            <Link
                                key={trend.name}
                                href={showHashtag.url(trend.name)}
                                className="block hover:opacity-75"
                            >
                                <p className="text-sm font-semibold text-[#191816]">
                                    #{trend.name}
                                </p>
                                <p className="text-xs text-[#8a8784]">
                                    {trend.postsCount.toLocaleString()}件の投稿
                                </p>
                            </Link>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
}
