import { atom } from 'jotai';

import { WebVersions } from './api/models';

export const webVersionsAtom = atom<WebVersions | null>(null);
