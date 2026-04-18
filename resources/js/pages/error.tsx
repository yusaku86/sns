import { Link } from '@inertiajs/react';

interface Props {
    status: number;
}

const messages: Record<
    number,
    { title: string; description: string; info: string }
> = {
    404: {
        title: 'ページが見つかりません',
        description:
            '猫も一緒にお昼寝中…お探しのページは夢の中に行ってしまったようです。',
        info: 'URLが正しいかご確認いただくか、下のボタンからトップページへお戻りください。',
    },
    500: {
        title: 'サーバーエラー',
        description:
            'サーバーで問題が発生しました。しばらく時間をおいてから再度お試しください。',
        info: '下のボタンからトップページへお戻りください。',
    },
    503: {
        title: 'サービス停止中',
        description:
            'ただいまメンテナンス中です。しばらく時間をおいてから再度お試しください。',
        info: '下のボタンからトップページへお戻りください。',
    },
};

export default function Error({ status }: Props) {
    const { title, description, info } = messages[status] ?? messages[404];

    return (
        <div
            style={{
                backgroundColor: '#f5f1e8',
                fontFamily: 'PT Sans, sans-serif',
            }}
            className="flex min-h-screen flex-col items-center justify-center px-4"
        >
            {/* 404 heading */}
            <div className="mb-2 flex items-center" style={{ gap: '0.5rem' }}>
                <span
                    style={{
                        fontFamily: 'Gravitas One, serif',
                        color: '#1f1a18',
                        fontSize: 'clamp(5rem, 12vw, 9rem)',
                        lineHeight: 1,
                    }}
                >
                    4
                </span>

                {/* center "0" replaced by cat image */}
                <img
                    src="/images/sleeping-cat.png"
                    alt="眠り猫"
                    style={{
                        width: 'clamp(5rem, 12vw, 9rem)',
                        height: 'clamp(5rem, 12vw, 9rem)',
                        objectFit: 'contain',
                        flexShrink: 0,
                    }}
                />

                <span
                    style={{
                        fontFamily: 'Gravitas One, serif',
                        color: '#1f1a18',
                        fontSize: 'clamp(5rem, 12vw, 9rem)',
                        lineHeight: 1,
                    }}
                >
                    4
                </span>
            </div>

            {/* underline */}
            <div
                style={{
                    width: '7.5rem',
                    height: '6px',
                    backgroundColor: '#d4824a',
                    borderRadius: '4px',
                    marginBottom: '1.5rem',
                }}
            />

            {/* title */}
            <h1
                style={{
                    fontFamily: 'PT Serif, serif',
                    color: '#1f1a18',
                    fontSize: 'clamp(1.5rem, 3vw, 2.25rem)',
                    fontWeight: 700,
                    marginBottom: '0.75rem',
                    textAlign: 'center',
                }}
            >
                {title}
            </h1>

            {/* description */}
            <p
                style={{
                    fontFamily: 'PT Sans, sans-serif',
                    color: '#3b2f2a',
                    fontSize: 'clamp(0.9rem, 1.8vw, 1.2rem)',
                    marginBottom: '1.5rem',
                    textAlign: 'center',
                    maxWidth: '600px',
                }}
            >
                {description}
            </p>

            {/* info box */}
            <div
                style={{
                    backgroundColor: '#efe8de',
                    borderRadius: '12px',
                    padding: '1rem 2rem',
                    maxWidth: '520px',
                    textAlign: 'center',
                    color: '#3b2f2a',
                    fontFamily: 'PT Sans, sans-serif',
                    fontSize: 'clamp(0.8rem, 1.4vw, 1rem)',
                    marginBottom: '2rem',
                    lineHeight: 1.6,
                }}
            >
                {info}
            </div>

            {/* button */}
            <Link
                href="/"
                style={{
                    backgroundColor: '#d4824a',
                    color: '#f5f1e8',
                    borderRadius: '10px',
                    padding: '0.85rem 2.5rem',
                    fontFamily: 'PT Sans, sans-serif',
                    fontSize: '1rem',
                    fontWeight: 700,
                    textDecoration: 'none',
                    display: 'inline-flex',
                    alignItems: 'center',
                    gap: '0.5rem',
                    marginBottom: '1.5rem',
                }}
            >
                <span>⌂</span>
                <span>トップページへ戻る</span>
            </Link>

            {/* footer */}
            <p
                style={{
                    fontFamily: 'Caveat, cursive',
                    color: '#b85f2b',
                    fontSize: '1.1rem',
                }}
            >
                …起きたらまた会いましょう 🐾
            </p>
        </div>
    );
}
