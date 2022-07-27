import { atom } from 'jotai';
import { atomWithStorage } from 'jotai/utils';
import { isNil } from 'ramda';

import { resourcesEndpoint } from '../api/endpoint';
import { replaceBasename } from '../helpers';
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
  (get, set, resource: Resource) => {
    set(openDetailsTabIdAtom, detailsTabId);
    set(selectedResourceUuidAtom, resource.uuid);
    set(selectedResourcesDetailsAtom, {
      resourceId: resource.id,
      resourcesDetailsEndpoint: resource?.links?.endpoints?.details,
      selectedResourceType: resource.type,
    });
  },
);

export const clearSelectedResourceDerivedAtom = atom(null, (_, set) => {
  set(selectedResourceUuidAtom, undefined);
  set(selectedResourcesDetailsAtom, null);
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

export const selectedResourcesDetailsAtom = atomWithStorage<{
  resourceId?: number;
  resourcesDetailsEndpoint?: string;
  selectedResourceType?: string;
} | null>('resource_details', null);

export const selectedResourceDetailsEndpointDerivedAtom = atom((get) => {
  const selectedResourceDetailsEndpoint = get(selectedResourcesDetailsAtom);

  const resourceDetailsEndPoint = replaceBasename({
    endpoint: selectedResourceDetailsEndpoint?.resourcesDetailsEndpoint || '',
    newWord: './',
  });

  if (!isNil(selectedResourceDetailsEndpoint?.selectedResourceType)) {
    return `${resourcesEndpoint}/${selectedResourceDetailsEndpoint?.selectedResourceType}s/${selectedResourceDetailsEndpoint?.resourceId}`;
  }

  return resourceDetailsEndPoint;
});

export const sendingDetailsAtom = atom(false);
