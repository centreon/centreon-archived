import * as React from 'react';

import { ResourceLinks } from '../models';
import { TabId, detailsTabId } from './tabs';

export interface DetailsState {
  openDetailsTabId: TabId;
  selectedDetailsLinks?: ResourceLinks;
  setOpenDetailsTabId: React.Dispatch<React.SetStateAction<TabId>>;
  setSelectedDetailsLinks: React.Dispatch<
    React.SetStateAction<ResourceLinks | undefined>
  >;
}

const useDetails = (): DetailsState => {
  const [selectedDetailsLinks, setSelectedDetailsLinks] =
    React.useState<ResourceLinks>();

  const [openDetailsTabId, setOpenDetailsTabId] =
    React.useState<TabId>(detailsTabId);

  return {
    openDetailsTabId,
    selectedDetailsLinks,
    setOpenDetailsTabId,
    setSelectedDetailsLinks,
  };
};

export default useDetails;
