import { atom } from 'jotai';

import { PlatformInstallationStatus } from '../../api/models';

export const platformInstallationStatusAtom =
  atom<PlatformInstallationStatus | null>(null);
