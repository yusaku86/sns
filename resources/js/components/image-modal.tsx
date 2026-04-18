import { useEffect, useState } from 'react';
import type { PostImageData } from '@/components/post-images';

type Props = {
    images: PostImageData[];
    initialIndex: number;
    onClose: () => void;
};

export default function ImageModal({ images, initialIndex, onClose }: Props) {
    const [index, setIndex] = useState(initialIndex);

    useEffect(() => {
        function handleKey(e: KeyboardEvent) {
            if (e.key === 'Escape') {
                onClose();
            }

            if (e.key === 'ArrowLeft') {
                setIndex((i) => Math.max(0, i - 1));
            }

            if (e.key === 'ArrowRight') {
                setIndex((i) => Math.min(images.length - 1, i + 1));
            }
        }
        window.addEventListener('keydown', handleKey);

        return () => window.removeEventListener('keydown', handleKey);
    }, [images.length, onClose]);

    const current = images[index];

    return (
        <div
            className="fixed inset-0 z-50 flex items-center justify-center bg-black/80"
            onClick={(e) => {
                e.stopPropagation();
                onClose();
            }}
        >
            {/* 閉じるボタン */}
            <button
                className="absolute top-4 right-4 flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/20"
                onClick={onClose}
                aria-label="閉じる"
            >
                ✕
            </button>

            {/* 前へ */}
            {index > 0 && (
                <button
                    className="absolute top-1/2 left-4 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-2xl text-white hover:bg-white/20"
                    onClick={(e) => {
                        e.stopPropagation();
                        setIndex((i) => i - 1);
                    }}
                    aria-label="前の画像"
                >
                    ‹
                </button>
            )}

            {/* 画像 */}
            <img
                src={current.url}
                alt={`画像 ${index + 1} / ${images.length}`}
                className="max-h-[90vh] max-w-[90vw] rounded-lg object-contain"
                onClick={(e) => e.stopPropagation()}
            />

            {/* 次へ */}
            {index < images.length - 1 && (
                <button
                    className="absolute top-1/2 right-4 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-2xl text-white hover:bg-white/20"
                    onClick={(e) => {
                        e.stopPropagation();
                        setIndex((i) => i + 1);
                    }}
                    aria-label="次の画像"
                >
                    ›
                </button>
            )}

            {/* ドットインジケーター */}
            {images.length > 1 && (
                <div className="absolute bottom-4 flex gap-1.5">
                    {images.map((_, i) => (
                        <button
                            key={i}
                            onClick={(e) => {
                                e.stopPropagation();
                                setIndex(i);
                            }}
                            className={`h-2 rounded-full transition-all ${
                                i === index ? 'w-5 bg-white' : 'w-2 bg-white/40'
                            }`}
                            aria-label={`画像 ${i + 1}`}
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
