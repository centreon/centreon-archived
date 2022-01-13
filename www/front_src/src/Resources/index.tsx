import * as React from 'react';

import { isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { ListingPage, useMemoComponent, WithPanel } from '@centreon/ui';

import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import EditFiltersPanel from './Filter/Edit';
import { selectedResourceIdAtom } from './Details/detailsAtoms';
import useDetails from './Details/useDetails';
import { editPanelOpenAtom } from './Filter/filterAtoms';
import useFilter from './Filter/useFilter';

const ResourcesPage = (): JSX.Element => {
  const selectedResourceId = useAtomValue(selectedResourceIdAtom);
  const editPanelOpen = useAtomValue(editPanelOpenAtom);

  return useMemoComponent({
    Component: (
      <WithPanel open={editPanelOpen} panel={<EditFiltersPanel />}>
        <ListingPage
          filter={<Filter />}
          listing={<Listing />}
          panel={<Details />}
          panelOpen={!isNil(selectedResourceId)}
        />
      </WithPanel>
    ),
    memoProps: [selectedResourceId, editPanelOpen],
  });
};

const Resources = (): JSX.Element => {
  useDetails();
  useFilter();

  return <ResourcesPage />;
};

export default Resources;
