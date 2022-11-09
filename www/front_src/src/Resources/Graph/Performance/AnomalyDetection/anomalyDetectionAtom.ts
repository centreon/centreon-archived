import { atom } from 'jotai';

interface ThresholdsAnomalyDetectionDataAtom {
  exclusionPeriodsThreshold: {
    data: Array<{ isConfirmed; lines: any; timeSeries: any }>;
    selectedDateToDelete: Array<{ end: undefined; start: undefined }>;
  };
}

export const countedRedCirclesAtom = atom<number | null>(null);

export const showModalAnomalyDetectionAtom = atom<boolean>(false);

export const thresholdsAnomalyDetectionDataAtom =
  atom<ThresholdsAnomalyDetectionDataAtom>({
    exclusionPeriodsThreshold: {
      data: [{ isConfirmed: false, lines: [], timeSeries: [] }],
      selectedDateToDelete: [],
    },
  });
