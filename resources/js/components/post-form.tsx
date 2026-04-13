import { useForm, usePage } from '@inertiajs/react';
import { store } from '@/routes/posts';

type AuthUser = {
    id: string;
    name: string;
    profile_image_url?: string | null;
} | null;

export default function PostForm() {
    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;

    const { data, setData, post, processing, errors, reset } = useForm({
        content: '',
    });

    const remaining = 140 - data.content.length;

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(store.url(), { onSuccess: () => reset() });
    }

    const initial = authUser?.name.charAt(0).toUpperCase() ?? '?';

    return (
        <form onSubmit={handleSubmit} className="border-b border-[#E5E7EB] p-4">
            <div className="flex gap-3">
                {/* アバター 48px */}
                {authUser?.profile_image_url ? (
                    <img
                        src={authUser.profile_image_url}
                        alt={authUser.name}
                        className="h-12 w-12 shrink-0 rounded-full object-cover"
                    />
                ) : (
                    <div
                        className="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#3a6c72] text-sm font-semibold text-white"
                        aria-hidden="true"
                    >
                        {initial}
                    </div>
                )}

                {/* 入力エリア */}
                <div className="min-w-0 flex-1">
                    <textarea
                        value={data.content}
                        onChange={(e) => setData('content', e.target.value)}
                        placeholder="いま何を考えていますか？"
                        rows={3}
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
                            className="h-10 rounded-md bg-[#3a6c72] px-5 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-50"
                        >
                            投稿
                        </button>
                    </div>
                </div>
            </div>
        </form>
    );
}
