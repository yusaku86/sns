import { Search } from 'lucide-react';

const suggestedUsers = [
    { name: '田中 健太', handle: 'kenta_t' },
    { name: '山本 あかり', handle: 'akari_y' },
    { name: '高橋 大輔', handle: 'daisuke_t' },
];

const trends = [
    { tag: '#デザイン思考', count: '1,247' },
    { tag: '#春の読書', count: '892' },
    { tag: '#朝活ルーティン', count: '634' },
];

export default function RightSidebar() {
    return (
        <div className="space-y-6 py-4">
            {/* 検索バー */}
            <div className="flex items-center gap-2 rounded-md bg-[#eae4dc] px-3 py-3">
                <Search size={17} className="shrink-0 text-[#8a8784]" />
                <span className="text-base text-[#8a8784]">
                    キーワードを検索
                </span>
            </div>

            {/* フォロワーも知っている */}
            <div>
                <h2 className="mb-3 text-lg font-bold text-[#191816]">
                    フォロワーも知っている
                </h2>
                <div className="space-y-4 rounded-md bg-[#eae4dc] p-4">
                    {suggestedUsers.map((user) => (
                        <div
                            key={user.handle}
                            className="flex items-center justify-between gap-2"
                        >
                            <div className="flex min-w-0 items-center gap-3">
                                <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white">
                                    {user.name.charAt(0)}
                                </div>
                                <div className="min-w-0">
                                    <p className="truncate text-sm font-semibold text-[#191816]">
                                        {user.name}
                                    </p>
                                    <p className="font-mono text-xs text-[#8a8784]">
                                        @{user.handle}
                                    </p>
                                </div>
                            </div>
                            <button className="shrink-0 rounded-md border border-[#3a6c72] px-3 py-1 text-sm font-semibold text-[#3a6c72] transition-colors hover:bg-[#3a6c72] hover:text-white">
                                フォロー
                            </button>
                        </div>
                    ))}
                </div>
            </div>

            {/* トレンド */}
            <div>
                <h2 className="mb-3 text-lg font-bold text-[#191816]">
                    トレンド
                </h2>
                <div className="space-y-4 rounded-md bg-[#eae4dc] p-4">
                    {trends.map((trend) => (
                        <div key={trend.tag}>
                            <p className="text-sm font-semibold text-[#191816]">
                                {trend.tag}
                            </p>
                            <p className="text-xs text-[#8a8784]">
                                {trend.count}件の投稿
                            </p>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
}
