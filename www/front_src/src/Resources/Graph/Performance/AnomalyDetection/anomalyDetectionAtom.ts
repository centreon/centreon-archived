import { atom } from 'jotai';

export const countedRedCirclesAtom = atom<number | null>(null);

export const showModalAnomalyDetectionAtom = atom<boolean>(false);
