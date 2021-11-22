import { atom } from 'jotai';
import { atomWithStorage } from 'jotai/utils';

import { ResourceListing } from '../models';
import { baseKey } from '../storage';

import { defaultSelectedColumnIds } from './columns';

export const listingAtom = atom<ResourceListing | undefined>(undefined);
export const limitAtom = atomWithStorage(`${baseKey}limit`, 30);
export const pageAtom = atom<number | undefined>(undefined);
export const enabledAutorefreshAtom = atom<boolean>(true);
export const selectedColumnIdsAtom = atomWithStorage(
  `${baseKey}column-ids`,
  defaultSelectedColumnIds,
);
export const sendingAtom = atom<boolean>(false);
