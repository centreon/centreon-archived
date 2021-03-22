import * as React from 'react';

import { isNil, ifElse, pathEq, always, pathOr } from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  getUrlQueryParameters,
  setUrlQueryParameters,
  useRequest,
  getData,
} from '@centreon/ui';

import { resourcesEndpoint } from '../api/endpoint';
import {
  labelNoResourceFound,
  labelSomethingWentWrong,
} from '../translatedLabels';
import { Resource } from '../models';

import { detailsTabId, getTabIdFromLabel, getTabLabelFromId } from './tabs';
import { TabId } from './tabs/models';
import {
  DetailsUrlQueryParameters,
  ResourceDetails,
  ServicesTabParameters,
  GraphTabParameters,
  TabParameters,
} from './models';
import { getStoredOrDefaultPanelWidth, storePanelWidth } from './storedDetails';

export interface DetailsState {
  selectResource: (resource: Resource) => void;
  clearSelectedResource: () => void;
  getSelectedResourceDetailsEndpoint: () => string | undefined;
  selectedResourceUuid?: string;
  setSelectedResourceUuid: React.Dispatch<
    React.SetStateAction<string | undefined>
  >;
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
  tabParameters: TabParameters;
  setServicesTabParameters: (parameters: ServicesTabParameters) => void;
  setGraphTabParameters: (parameters: GraphTabParameters) => void;
  panelWidth: number;
  setPanelWidth: React.Dispatch<React.SetStateAction<number>>;
  selectedResourceParentId?: number;
}

const useDetails = (): DetailsState => {
  const [openDetailsTabId, setOpenDetailsTabId] = React.useState<TabId>(
    detailsTabId,
  );
  const [
    selectedResourceUuid,
    setSelectedResourceUuid,
  ] = React.useState<string>();
  const [selectedResourceId, setSelectedResourceId] = React.useState<number>();
  const [
    selectedResourceParentId,
    setSelectedResourceParentId,
  ] = React.useState<number>();
  const [
    selectedResourceType,
    setSelectedResourceType,
  ] = React.useState<string>();
  const [
    selectedResourceParentType,
    setSelectedResourceParentType,
  ] = React.useState<string>();
  const [details, setDetails] = React.useState<ResourceDetails>();
  const [tabParameters, setTabParameters] = React.useState<TabParameters>({});
  const [panelWidth, setPanelWidth] = React.useState(
    getStoredOrDefaultPanelWidth(550),
  );

  const { t } = useTranslation();

  const { sendRequest } = useRequest<ResourceDetails>({
    request: getData,
    getErrorMessage: ifElse(
      pathEq(['response', 'status'], 404),
      always(t(labelNoResourceFound)),
      pathOr(t(labelSomethingWentWrong), ['response', 'data', 'message']),
    ),
  });

  const selectResource = (resource: Resource): void => {
    setOpenDetailsTabId(detailsTabId);
    setSelectedResourceUuid(resource.uuid);
    setSelectedResourceId(resource.id);
    setSelectedResourceType(resource.type);
    setSelectedResourceParentType(resource?.parent?.type);
    setSelectedResourceParentId(resource?.parent?.id);
  };

  React.useEffect(() => {
    const urlQueryParameters = getUrlQueryParameters();

    const detailsUrlQueryParameters = urlQueryParameters.details as DetailsUrlQueryParameters;

    if (isNil(detailsUrlQueryParameters)) {
      return;
    }

    const {
      uuid,
      id,
      parentId,
      type,
      parentType,
      tab,
      tabParameters: tabParametersFromUrl,
    } = detailsUrlQueryParameters;

    if (!isNil(tab)) {
      setOpenDetailsTabId(getTabIdFromLabel(tab));
    }

    setSelectedResourceUuid(uuid);
    setSelectedResourceId(id);
    setSelectedResourceParentId(parentId);
    setSelectedResourceType(type);
    setSelectedResourceParentType(parentType);
    setTabParameters(tabParametersFromUrl || {});
  }, []);

  React.useEffect(() => {
    setUrlQueryParameters([
      {
        name: 'details',
        value: {
          uuid: selectedResourceUuid,
          id: selectedResourceId,
          parentId: selectedResourceParentId,
          type: selectedResourceType,
          parentType: selectedResourceParentType,
          tab: getTabLabelFromId(openDetailsTabId),
          tabParameters,
        },
      },
    ]);
  }, [
    openDetailsTabId,
    selectedResourceId,
    selectedResourceType,
    selectedResourceParentType,
    selectedResourceParentType,
    tabParameters,
  ]);

  const getSelectedResourceDetailsEndpoint = (): string | undefined => {
    if (!isNil(selectedResourceParentId)) {
      return `${resourcesEndpoint}/${selectedResourceParentType}s/${selectedResourceParentId}/${selectedResourceType}s/${selectedResourceId}`;
    }

    return `${resourcesEndpoint}/${selectedResourceType}s/${selectedResourceId}`;
  };

  const clearSelectedResource = (): void => {
    setSelectedResourceUuid(undefined);
    setSelectedResourceId(undefined);
    setSelectedResourceParentId(undefined);
    setSelectedResourceParentType(undefined);
    setSelectedResourceType(undefined);
  };

  const loadDetails = (): void => {
    if (isNil(selectedResourceId)) {
      return;
    }

    sendRequest(getSelectedResourceDetailsEndpoint())
      .then(setDetails)
      .catch(() => {
        clearSelectedResource();
      });
  };

  React.useEffect(() => {
    setDetails(undefined);
    loadDetails();
  }, [selectedResourceUuid]);

  React.useEffect(() => {
    storePanelWidth(panelWidth);
  }, [panelWidth]);

  const setServicesTabParameters = (
    parameters: ServicesTabParameters,
  ): void => {
    setTabParameters({ ...tabParameters, services: parameters });
  };

  const setGraphTabParameters = (parameters: GraphTabParameters): void => {
    setTabParameters({ ...tabParameters, graph: parameters });
  };

  return {
    selectResource,
    clearSelectedResource,
    selectedResourceUuid,
    selectedResourceId,
    selectedResourceParentId,
    setSelectedResourceUuid,
    setSelectedResourceId,
    setSelectedResourceType,
    setSelectedResourceParentId,
    setSelectedResourceParentType,
    openDetailsTabId,
    setOpenDetailsTabId,
    getSelectedResourceDetailsEndpoint,
    details,
    loadDetails,
    tabParameters,
    setServicesTabParameters,
    setGraphTabParameters,
    panelWidth,
    setPanelWidth,
  };
};

export default useDetails;
