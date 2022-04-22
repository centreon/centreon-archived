import { atom } from 'jotai';

import ExternalComponents from './models';

export const externalComponentsAtom = atom<ExternalComponents | null>(null);
