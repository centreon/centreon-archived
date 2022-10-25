import { atom } from 'jotai';

import { FederatedModule } from './models';

export const federatedModulesAtom = atom<Array<FederatedModule> | null>(null);

export const federatedWidgetsAtom = atom<Array<FederatedModule> | null>(null);
