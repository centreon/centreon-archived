import { atomWithStorage } from 'jotai/utils';

export const detailsCardsAtom = atomWithStorage<Array<string>>(
  'centreon-resource-status-details-card-21.10',
  [],
);
