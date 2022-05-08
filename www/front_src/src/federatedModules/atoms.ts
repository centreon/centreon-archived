import { atom } from 'jotai';

import { FederatedComponent } from './models';

export const federatedComponentsAtom = atom<Array<FederatedComponent> | null>(
  null,
);
