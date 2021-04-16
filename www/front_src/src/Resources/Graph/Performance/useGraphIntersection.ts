import * as React from 'react';

interface UseIntersection {
  isDisplaying: boolean;
  setElement: React.Dispatch<React.SetStateAction<HTMLElement | null>>;
}

export const useIntersection = (): UseIntersection => {
  const [entry, setEntry] = React.useState<IntersectionObserverEntry | null>(
    null,
  );
  const [element, setElement] = React.useState<HTMLElement | null>(null);

  const observer = React.useRef<IntersectionObserver | null>(null);

  React.useEffect(() => {
    if (observer.current) {
      observer.current.disconnect();
    }

    observer.current = new window.IntersectionObserver(
      ([newEntry]) => setEntry(newEntry),
      {
        rootMargin: '150px 0px 150px 0px',
      },
    );

    if (element) {
      observer.current.observe(element);
    }

    return () => {
      observer.current?.disconnect();
    };
  }, [element]);

  return {
    isDisplaying: Boolean(entry?.isIntersecting),
    setElement,
  };
};
