import { atom } from 'jotai';

import { Resource } from '../models';

export const selectedResourcesAtom = atom<Array<Resource>>([]);
export const resourcesToAcknowledgeAtom = atom<Array<Resource>>([]);
export const resourcesToSetDowntimeAtom = atom<Array<Resource>>([]);
export const resourcesToCheckAtom = atom<Array<Resource>>([]);
export const resourcesToDisacknowledgeAtom = atom<Array<Resource>>([]);
