import { atomWithStorage } from 'jotai/utils';

import { Page } from '../models';

export const selectedNavigationItemsAtom = atomWithStorage<Record<
  string,
  Page
> | null>('selectedNavigationItems', null);
