import * as React from 'react';

import { ResourceLinks } from '../models';
import { TabId, detailsTabId } from './tabs';

export interface DetailsState {
  selectedDetailsLinks?: ResourceLinks;
  setSelectedDetailsLinks: React.Dispatch<
    React.SetStateAction<ResourceLinks | undefined>
  >;
  openDetailsTabId: TabId;
  setOpenDetailsTabId: React.Dispatch<React.SetStateAction<TabId>>;
}

const useDetails = (): DetailsState => {
  const [selectedDetailsLinks, setSelectedDetailsLinks] = React.useState<
    ResourceLinks
  >();

  const [openDetailsTabId, setOpenDetailsTabId] = React.useState<TabId>(
    detailsTabId,
  );

  return {
    selectedDetailsLinks,
    setSelectedDetailsLinks,
    openDetailsTabId,
    setOpenDetailsTabId,
  };
};

export default useDetails;
