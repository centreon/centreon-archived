import { atom } from 'jotai';

export const imageAtom = atom<string | null>(null);

const loadImage = (imagePath: string): Promise<string> =>
  new Promise((resolve, reject) => {
    const image = new Image();

    image.src = imagePath;
    image.onload = (): void => resolve(imagePath);
    image.onerror = reject;
  });

export const loadImageDerivedAtom = atom(
  null,
  (_, set, imagePath: string): void => {
    loadImage(imagePath)
      .then((image) => set(imageAtom, image))
      .catch(() => undefined);
  },
);
