import { useForm, usePage } from '@inertiajs/react';
import { ImagePlus, X } from 'lucide-react';
import { useRef, useState } from 'react';
import { store } from '@/routes/posts';

type AuthUser = {
    id: string;
    name: string;
    profile_image_url?: string | null;
} | null;

export default function PostForm() {
    const { auth } = usePage().props as { auth: { user: AuthUser } };
    const authUser = auth?.user;

    const { data, setData, post, processing, errors, reset } = useForm<{
        content: string;
        images: File[];
    }>({
        content: '',
        images: [],
    });

    const [previews, setPreviews] = useState<string[]>([]);
    const fileInputRef = useRef<HTMLInputElement>(null);

    const remaining = 140 - data.content.length;

    function handleFiles(files: FileList | null) {
        if (!files) {
            return;
        }

        const next = [...data.images, ...Array.from(files)].slice(0, 8);
        setData('images', next);
        setPreviews((prev) => {
            prev.forEach((url) => URL.revokeObjectURL(url));

            return next.map((f) => URL.createObjectURL(f));
        });
    }

    function removeImage(index: number) {
        const next = data.images.filter((_, i) => i !== index);
        setData('images', next);
        setPreviews((prev) => {
            prev.forEach((url) => URL.revokeObjectURL(url));

            return next.map((f) => URL.createObjectURL(f));
        });
    }

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(store.url(), {
            forceFormData: true,
            onSuccess: () => {
                reset();
                setPreviews((prev) => {
                    prev.forEach((url) => URL.revokeObjectURL(url));

                    return [];
                });
            },
        });
    }

    const canSubmit = data.content.trim() !== '' || data.images.length > 0;
    const initial = authUser?.name.charAt(0).toUpperCase() ?? '?';

    return (
        <form onSubmit={handleSubmit} className="border-b border-[#E5E7EB] p-4">
            <div className="flex gap-3">
                {/* アバター */}
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
                    {errors.images && (
                        <p className="mt-1 text-xs text-[#b36b09]">
                            {errors.images}
                        </p>
                    )}

                    {/* 画像プレビュー */}
                    {previews.length > 0 && (
                        <div className="mt-2 grid grid-cols-4 gap-1">
                            {previews.map((src, i) => (
                                <div key={i} className="relative">
                                    <img
                                        src={src}
                                        alt={`プレビュー ${i + 1}`}
                                        className="h-20 w-full rounded-md object-cover"
                                    />
                                    <button
                                        type="button"
                                        onClick={() => removeImage(i)}
                                        className="absolute top-0.5 right-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-black/60 text-xs text-white hover:bg-black/80"
                                        aria-label={`画像 ${i + 1} を削除`}
                                    >
                                        <X size={10} />
                                    </button>
                                </div>
                            ))}
                        </div>
                    )}

                    <div className="mt-2 flex items-center justify-between">
                        <div className="flex items-center gap-3">
                            {/* 画像追加ボタン */}
                            {data.images.length < 8 && (
                                <button
                                    type="button"
                                    onClick={() =>
                                        fileInputRef.current?.click()
                                    }
                                    className="flex items-center gap-1 text-sm text-[#3a6c72] hover:opacity-70"
                                    aria-label="画像を追加"
                                >
                                    <ImagePlus size={20} />
                                </button>
                            )}
                            <input
                                ref={fileInputRef}
                                type="file"
                                accept="image/jpeg,image/png,image/gif,image/webp"
                                multiple
                                className="hidden"
                                onChange={(e) => handleFiles(e.target.files)}
                            />
                            <span
                                className={`text-sm ${remaining < 20 ? 'text-[#b36b09]' : 'text-[#8a8784]'}`}
                            >
                                {remaining}
                            </span>
                        </div>
                        <button
                            type="submit"
                            disabled={processing || !canSubmit}
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
