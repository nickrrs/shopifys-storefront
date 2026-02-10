import { onBeforeUnmount } from 'vue';

type InfiniteScrollOptions = {
    onLoadMore: () => void | Promise<void>;
    rootMargin?: string;
};

export function useInfiniteScroll(options: InfiniteScrollOptions) {
    const { onLoadMore, rootMargin = '100px' } = options;
    const observers = new Map<string, IntersectionObserver>();

    const observe = (el: HTMLElement | null, key: string) => {
        if (!el) return;

        const existing = observers.get(key);
        if (existing) existing.disconnect();

        const scrollContainer = el.closest('[data-scroll-container]');

        const observer = new IntersectionObserver(
            (entries) => {
                if (entries[0]?.isIntersecting) {
                    onLoadMore();
                }
            },
            {
                root: scrollContainer,
                rootMargin,
            },
        );

        observer.observe(el);
        observers.set(key, observer);
    };

    const unobserve = (key: string) => {
        const observer = observers.get(key);
        if (observer) {
            observer.disconnect();
            observers.delete(key);
        }
    };

    const disconnectAll = () => {
        observers.forEach((observer) => observer.disconnect());
        observers.clear();
    };

    onBeforeUnmount(disconnectAll);

    return { observe, unobserve, disconnectAll };
}
