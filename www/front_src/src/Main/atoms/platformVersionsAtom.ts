import { atom } from 'jotai';

import { PlatformVersions } from '../../api/models';

export const platformVersionsAtom = atom<PlatformVersions | null>({
  modules: {},
  web: {
    version: '21.10.0-beta.1',
  },
  widgets: {
    'centreon-performance-graph': {
      version: '21.10.0-beta.1',
    },
    'centreon-text-widget': {
      version: '21.10.0-beta.1',
    },
    'centreon-website-widget': {
      version: '21.10.0-beta.1',
    },
  },
});
