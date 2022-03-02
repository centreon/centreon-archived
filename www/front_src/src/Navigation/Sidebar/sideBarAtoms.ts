import { atomWithStorage } from 'jotai/utils';

export const navigationItemSelectedAtom = atomWithStorage<Record<
  string,
  propsnavigationItemSelected
> | null>('navigationItemSelected', null);

export interface propsnavigationItemSelected {
  index: number | null;
  label: string;
  url?: string | null;
}
