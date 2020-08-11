import * as React from 'react';

import { ResourceEndpoints, ResourceLinks } from '../models';
import { TabId, detailsTabId } from './tabs';

export interface DetailsState {
  selectedDetailsLinks?: ResourceLinks;
  setSelectedDetailsLinks: React.Dispatch<
    React.SetStateAction<ResourceLinks | undefined>
  >;

  selectedDetailsEndpoints: ResourceEndpoints | null;
  setSelectedDetailsEndpoints: React.Dispatch<
    React.SetStateAction<ResourceEndpoints | null>
  >;
  openDetailsTabId: TabId;
  setOpenDetailsTabId: React.Dispatch<React.SetStateAction<TabId>>;
}

const useDetails = (): DetailsState => {
  const [selectedDetailsLinks, setSelectedDetailsLinks] = React.useState<
    ResourceLinks
  >();

  const [
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
  ] = React.useState<ResourceEndpoints | null>(null);

  const [openDetailsTabId, setOpenDetailsTabId] = React.useState<TabId>(
    detailsTabId,
  );

  return {
    selectedDetailsLinks,
    setSelectedDetailsLinks,
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
    openDetailsTabId,
    setOpenDetailsTabId,
  };
};

export default useDetails;
