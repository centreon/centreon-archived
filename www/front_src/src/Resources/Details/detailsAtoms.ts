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
  ResourceDetailsAtom,
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
  (get, set, resource: ResourceDetails | Resource) => {
    set(openDetailsTabIdAtom, detailsTabId);
    set(selectedResourceUuidAtom, resource?.uuid);
    set(selectedResourcesDetailsAtom, {
      parentResourceId: resource?.parent?.id,
      parentResourceType: resource?.parent?.type,
      resourceId: resource?.id,
      resourcesDetailsEndpoint: resource?.links?.endpoints?.details,
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

export const selectedResourcesDetailsAtom =
  atomWithStorage<ResourceDetailsAtom | null>('resource_details', null);

export const selectedResourceDetailsEndpointDerivedAtom = atom((get) => {
  const selectedResourceDetails = get(selectedResourcesDetailsAtom);

  const resourceDetailsEndPoint = replaceBasename({
    endpoint: selectedResourceDetails?.resourcesDetailsEndpoint || '',
    newWord: './',
  });

  if (!isNil(selectedResourceDetails?.parentResourceId)) {
    return `${resourcesEndpoint}/${selectedResourceDetails?.parentResourceType}s/${selectedResourceDetails?.parentResourceId}`;
  }

  return resourceDetailsEndPoint;
});

export const sendingDetailsAtom = atom(false);
