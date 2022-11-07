import { atom } from 'jotai';

import { CustomFactorsData } from './models';

interface ThresholdsAnomalyDetectionDataAtom {
  envelopeSizeThreshold?: { data: { lines: any } } | null;
  estimatedEnvelopeThreshold?: {
    data: CustomFactorsData | null | undefined;
  } | null;
  exclusionPeriodsThreshold: {
    data?: { lines: any; timeSeries: any };
    selectedDateToDelete: Array<{ end: undefined; start: undefined }>;
  };
}

export const countedRedCirclesAtom = atom<number | null>(null);

export const showModalAnomalyDetectionAtom = atom<boolean>(false);

export const thresholdsAnomalyDetectionDataAtom =
  atom<ThresholdsAnomalyDetectionDataAtom>({
    envelopeSizeThreshold: null,
    estimatedEnvelopeThreshold: null,
    exclusionPeriodsThreshold: { data: undefined, selectedDateToDelete: [] },
  });
