import * as React from 'react';

import {
  getUrlQueryParameters,
  setUrlQueryParameters,
  useRequest,
  getData,
} from '@centreon/ui';

import { isNil } from 'ramda';
import {
  TabId,
  detailsTabId,
  getTabIdFromLabel,
  getTabLabelFromId,
} from './tabs';
import { DetailsUrlQueryParameters, ResourceDetails } from './models';
import { resourcesEndpoint } from '../api/endpoint';

export interface DetailsState {
  clearSelectedResource: () => void;
  getSelectedResourceDetailsEndpoint: () => string | undefined;
  selectedResourceId?: number;
  setSelectedResourceId: React.Dispatch<
    React.SetStateAction<number | undefined>
  >;
  setSelectedResourceType: React.Dispatch<
    React.SetStateAction<string | undefined>
  >;
  setSelectedResourceParentId: React.Dispatch<
    React.SetStateAction<number | undefined>
  >;
  setSelectedResourceParentType: React.Dispatch<
    React.SetStateAction<string | undefined>
  >;
  openDetailsTabId: TabId;
  setOpenDetailsTabId: React.Dispatch<React.SetStateAction<TabId>>;
  details?: ResourceDetails;
  loadDetails: () => void;
}

const useDetails = (): DetailsState => {
  const [openDetailsTabId, setOpenDetailsTabId] = React.useState<TabId>(
    detailsTabId,
  );
  const [selectedResourceId, setSelectedResourceId] = React.useState<number>();
  const [
    selectedResourceParentId,
    setSelectedResourceParentId,
  ] = React.useState<number>();
  const [selectedResourceType, setSelectedResourceType] = React.useState<
    string
  >();
  const [
    selectedResourceParentType,
    setSelectedResourceParentType,
  ] = React.useState<string>();
  const [details, setDetails] = React.useState<ResourceDetails>();

  const { sendRequest } = useRequest<ResourceDetails>({
    request: getData,
  });

  React.useEffect(() => {
    const urlQueryParameters = getUrlQueryParameters();

    const detailsUrlQueryParameters = urlQueryParameters.details as DetailsUrlQueryParameters;

    if (isNil(detailsUrlQueryParameters)) {
      return;
    }

    const { id, parentId, type, parentType, tab } = detailsUrlQueryParameters;

    if (!isNil(tab)) {
      setOpenDetailsTabId(getTabIdFromLabel(tab));
    }

    setSelectedResourceId(id);
    setSelectedResourceParentId(parentId);
    setSelectedResourceType(type);
    setSelectedResourceParentType(parentType);
  }, []);

  React.useEffect(() => {
    setUrlQueryParameters([
      {
        name: 'details',
        value: {
          id: selectedResourceId,
          parentId: selectedResourceParentId,
          type: selectedResourceType,
          parentType: selectedResourceParentType,
          tab: getTabLabelFromId(openDetailsTabId),
        },
      },
    ]);
  }, [
    openDetailsTabId,
    selectedResourceId,
    selectedResourceType,
    selectedResourceParentType,
    selectedResourceParentType,
  ]);

  const getSelectedResourceDetailsEndpoint = (): string | undefined => {
    if (!isNil(selectedResourceParentId)) {
      return `${resourcesEndpoint}/${selectedResourceParentType}s/${selectedResourceParentId}/${selectedResourceType}s/${selectedResourceId}`;
    }

    return `${resourcesEndpoint}/${selectedResourceType}s/${selectedResourceId}`;
  };

  const loadDetails = (): void => {
    if (isNil(selectedResourceId)) {
      return;
    }

    sendRequest(getSelectedResourceDetailsEndpoint()).then(setDetails);
  };

  React.useEffect(() => {
    setDetails(undefined);
    loadDetails();
  }, [selectedResourceId]);

  const clearSelectedResource = (): void => {
    setSelectedResourceId(undefined);
    setSelectedResourceParentId(undefined);
    setSelectedResourceParentType(undefined);
    setSelectedResourceType(undefined);
  };

  return {
    clearSelectedResource,
    selectedResourceId,
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
    openDetailsTabId,
    setOpenDetailsTabId,
    getSelectedResourceDetailsEndpoint,
    details,
    loadDetails,
  };
};

export default useDetails;
