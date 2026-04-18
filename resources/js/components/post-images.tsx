import { useState } from 'react';
import ImageModal from '@/components/image-modal';

export type PostImageData = {
    id: string;
    url: string;
    order: number;
};

type Props = {
    images: PostImageData[];
};

export default function PostImages({ images }: Props) {
    const [modalIndex, setModalIndex] = useState<number | null>(null);

    if (images.length === 0) {
        return null;
    }

    function openModal(index: number, e: React.MouseEvent) {
        e.stopPropagation();
        setModalIndex(index);
    }

    return (
        <>
            <ImageSlider images={images} onImageClick={openModal} />
            {modalIndex !== null && (
                <ImageModal
                    images={images}
                    initialIndex={modalIndex}
                    onClose={() => setModalIndex(null)}
                />
            )}
        </>
    );
}

type SliderProps = {
    images: PostImageData[];
    onImageClick: (index: number, e: React.MouseEvent) => void;
};

function ImageSlider({ images, onImageClick }: SliderProps) {
    const [page, setPage] = useState(0);

    const perPage = 4;
    const totalPages = Math.ceil(images.length / perPage);
    const pageImages = images.slice(page * perPage, page * perPage + perPage);
    const count = pageImages.length;

    const globalOffset = page * perPage;

    function prev(e: React.MouseEvent) {
        e.stopPropagation();
        setPage((p) => Math.max(0, p - 1));
    }

    function next(e: React.MouseEvent) {
        e.stopPropagation();
        setPage((p) => Math.min(totalPages - 1, p + 1));
    }

    return (
        <div className="relative mt-3">
            <Grid
                images={pageImages}
                globalOffset={globalOffset}
                onImageClick={onImageClick}
                count={count}
            />

            {/* ページネーション（5枚以上の時のみ表示） */}
            {totalPages > 1 && (
                <>
                    {page > 0 && (
                        <button
                            onClick={prev}
                            className="absolute top-1/2 left-1 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70"
                            aria-label="前のページ"
                        >
                            ‹
                        </button>
                    )}
                    {page < totalPages - 1 && (
                        <button
                            onClick={next}
                            className="absolute top-1/2 right-1 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white hover:bg-black/70"
                            aria-label="次のページ"
                        >
                            ›
                        </button>
                    )}
                    <div className="mt-2 flex justify-center gap-1.5">
                        {Array.from({ length: totalPages }).map((_, i) => (
                            <button
                                key={i}
                                onClick={(e) => {
                                    e.stopPropagation();
                                    setPage(i);
                                }}
                                className={`h-1.5 rounded-full transition-all ${
                                    i === page
                                        ? 'w-4 bg-[#3a6c72]'
                                        : 'w-1.5 bg-[#c0bab5]'
                                }`}
                                aria-label={`ページ ${i + 1}`}
                            />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
}

type GridProps = {
    images: PostImageData[];
    globalOffset: number;
    onImageClick: (index: number, e: React.MouseEvent) => void;
    count: number;
};

function Grid({ images, globalOffset, onImageClick, count }: GridProps) {
    const gridClass =
        count === 1
            ? 'grid grid-cols-1'
            : count === 2
              ? 'grid grid-cols-2 gap-1'
              : 'grid grid-cols-2 gap-1';

    return (
        <div className={gridClass}>
            {images.map((img, localIndex) => {
                const globalIndex = globalOffset + localIndex;
                const isLast = localIndex === images.length - 1;
                // 3枚の場合、最後の1枚は2列にまたがる
                const colSpan = count === 3 && isLast ? 'col-span-2' : '';

                return (
                    <button
                        key={img.id}
                        onClick={(e) => onImageClick(globalIndex, e)}
                        className={`overflow-hidden rounded-lg ${colSpan}`}
                    >
                        <img
                            src={img.url}
                            alt={`投稿画像 ${globalIndex + 1}`}
                            className={`w-full object-cover ${
                                count === 1 ? 'max-h-80 object-contain' : 'h-40'
                            }`}
                        />
                    </button>
                );
            })}
        </div>
    );
}
