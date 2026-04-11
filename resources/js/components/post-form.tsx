import { useForm } from '@inertiajs/react';
import { store } from '@/routes/posts';

export default function PostForm() {
    const { data, setData, post, processing, errors, reset } = useForm({
        content: '',
    });

    const remaining = 140 - data.content.length;

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(store.url(), { onSuccess: () => reset() });
    }

    return (
        <form onSubmit={handleSubmit} className="border-b border-border p-4">
            <textarea
                value={data.content}
                onChange={(e) => setData('content', e.target.value)}
                placeholder="いまどうしてる？"
                rows={3}
                className="w-full resize-none bg-transparent text-sm outline-none placeholder:text-muted-foreground"
                maxLength={140}
            />
            {errors.content && (
                <p className="mt-1 text-xs text-destructive">
                    {errors.content}
                </p>
            )}
            <div className="mt-2 flex items-center justify-between">
                <span
                    className={`text-xs ${remaining < 20 ? 'text-destructive' : 'text-muted-foreground'}`}
                >
                    {remaining}
                </span>
                <button
                    type="submit"
                    disabled={processing || data.content.trim() === ''}
                    className="rounded-full bg-primary px-4 py-1.5 text-sm font-semibold text-primary-foreground disabled:opacity-50"
                >
                    投稿する
                </button>
            </div>
        </form>
    );
}
