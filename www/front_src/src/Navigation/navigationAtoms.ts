import { atom } from 'jotai';

import Navigation from './models';

const navigationAtom = atom<Navigation | null>(null);

export default navigationAtom;
