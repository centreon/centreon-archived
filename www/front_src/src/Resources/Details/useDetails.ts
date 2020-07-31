import * as React from 'react';

import { ResourceEndpoints } from '../models';
import { TabId, detailsTabId } from './tabs';

export interface DetailsState {
  selectedDetailsEndpoints: ResourceEndpoints | null;
  setSelectedDetailsEndpoints: React.Dispatch<
    React.SetStateAction<ResourceEndpoints | null>
  >;
  detailsTabIdToOpen: TabId;
  setDefaultDetailsTabIdToOpen: React.Dispatch<React.SetStateAction<TabId>>;
}

const useDetails = (): DetailsState => {
  const [
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
  ] = React.useState<ResourceEndpoints | null>(null);

  const [detailsTabIdToOpen, setDefaultDetailsTabIdToOpen] = React.useState<
    TabId
  >(detailsTabId);

  return {
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
    detailsTabIdToOpen,
    setDefaultDetailsTabIdToOpen,
  };
};

export default useDetails;
