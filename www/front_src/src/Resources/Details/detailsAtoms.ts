import { atom } from 'jotai';
import { atomWithStorage } from 'jotai/utils';
import { isNil } from 'ramda';

import { resourcesEndpoint } from '../api/endpoint';
import { Resource } from '../models';

import {
  GraphTabParameters,
  ResourceDetails,
  ServicesTabParameters,
  TabParameters,
} from './models';
import { detailsTabId } from './tabs';
import { CustomTimePeriod, TimePeriodId } from './tabs/Graph/models';
import { TabId } from './tabs/models';

export const panelWidthStorageAtom = atomWithStorage(
  'centreon-resource-status-details-21.10',
  550,
);

export const openDetailsTabIdAtom = atom<TabId>(0);
export const selectedResourceUuidAtom = atom<string | undefined>(undefined);
export const selectedResourceIdAtom = atom<number | undefined>(undefined);
export const selectedResourceParentIdAtom = atom<number | undefined>(undefined);
export const selectedResourceTypeAtom = atom<string | undefined>(undefined);
export const selectedResourceParentTypeAtom = atom<string | undefined>(
  undefined,
);
export const detailsAtom = atom<ResourceDetails | undefined>(undefined);
export const tabParametersAtom = atom<TabParameters>({});
export const defaultSelectedTimePeriodIdAtom = atom<TimePeriodId | undefined>(
  undefined,
);
export const defaultSelectedCustomTimePeriodAtom = atom<
  CustomTimePeriod | undefined
>(undefined);

export const selectResourceDerivedAtom = atom(
  null,
  (_, set, resource: Resource) => {
    set(openDetailsTabIdAtom, detailsTabId);
    set(selectedResourceUuidAtom, resource.uuid);
    set(selectedResourceIdAtom, resource.id);
    set(selectedResourceTypeAtom, resource.type);
    set(selectedResourceParentTypeAtom, resource.parent?.type);
    set(selectedResourceParentIdAtom, resource.parent?.id);
  },
);

export const clearSelectedResourceDerivedAtom = atom(null, (_, set) => {
  set(selectedResourceUuidAtom, undefined);
  set(selectedResourceIdAtom, undefined);
  set(selectedResourceTypeAtom, undefined);
  set(selectedResourceParentTypeAtom, undefined);
  set(selectedResourceParentIdAtom, undefined);
});

export const setServicesTabParametersDerivedAtom = atom(
  null,
  (get, set, parameters: ServicesTabParameters) => {
    set(tabParametersAtom, { ...get(tabParametersAtom), services: parameters });
  },
);

export const setGraphTabParametersDerivedAtom = atom(
  null,
  (get, set, parameters: GraphTabParameters) => {
    set(tabParametersAtom, { ...get(tabParametersAtom), graph: parameters });
  },
);

export const selectedResourceDetailsEndpointDerivedAtom = atom((get) => {
  const selectedResourceParentId = get(selectedResourceParentIdAtom);
  const selectedResourceParentType = get(selectedResourceParentTypeAtom);
  const selectedResourceType = get(selectedResourceTypeAtom);
  const selectedResourceId = get(selectedResourceIdAtom);

  if (!isNil(selectedResourceParentId)) {
    return `${resourcesEndpoint}/${selectedResourceParentType}s/${selectedResourceParentId}/${selectedResourceType}s/${selectedResourceId}`;
  }

  return `${resourcesEndpoint}/${selectedResourceType}s/${selectedResourceId}`;
});
