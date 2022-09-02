import { atom } from 'jotai';

import { Provider } from './models';

export const tabAtom = atom(Provider.Local);
export const appliedTabAtom = atom(Provider.Local);
