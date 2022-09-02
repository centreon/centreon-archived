import { atom } from 'jotai';

import { PlatformVersions } from '../../api/models';

export const platformVersionsAtom = atom<PlatformVersions | null>(null);
