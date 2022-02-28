import { atomWithStorage } from 'jotai/utils';

export const itemSelectedAtom = atomWithStorage<Record<
  string,
  propsItemSelected
> | null>('itemSelectedNav', null);

export interface propsItemSelected {
  index: number | null;
  label: string;
  url?: string | null;
}
