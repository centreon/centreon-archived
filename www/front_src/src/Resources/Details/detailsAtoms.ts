import { atom } from 'jotai';
import { atomWithStorage } from 'jotai/utils';

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
export const selectedResourceDetailsEndpointAtom = atomWithStorage<
  string | undefined
>('resourceDetailsEndpoint', undefined);
export const openDetailsTabIdAtom = atom<TabId>(0);
export const selectedResourceUuidAtom = atom<string | undefined>(undefined);
export const selectedResourceIdAtom = atom<number | undefined>(undefined);
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
    set(
      selectedResourceDetailsEndpointAtom,
      resource?.links?.endpoints?.details,
    );
  },
);

export const clearSelectedResourceDerivedAtom = atom(null, (_, set) => {
  set(selectedResourceDetailsEndpointAtom, undefined);
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

export const sendingDetailsAtom = atom(false);
