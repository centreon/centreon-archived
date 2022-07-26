import { lazy } from 'react';

import { isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { ListingPage, useMemoComponent, WithPanel } from '@centreon/ui';

import Details from './Details';
import EditFiltersPanel from './Filter/Edit';
import { selectedResourcesDetailsAtom } from './Details/detailsAtoms';
import useDetails from './Details/useDetails';
import { editPanelOpenAtom } from './Filter/filterAtoms';
import useFilter from './Filter/useFilter';

const Filter = lazy(() => import('./Filter'));
const Listing = lazy(() => import('./Listing'));

const ResourcesPage = (): JSX.Element => {
  const selectedResource = useAtomValue(selectedResourcesDetailsAtom);
  const editPanelOpen = useAtomValue(editPanelOpenAtom);

  return useMemoComponent({
    Component: (
      <WithPanel open={editPanelOpen} panel={<EditFiltersPanel />}>
        <ListingPage
          filter={<Filter />}
          listing={<Listing />}
          panel={<Details />}
          panelOpen={!isNil(selectedResource?.resourceId)}
        />
      </WithPanel>
    ),
    memoProps: [selectedResource?.resourceId, editPanelOpen],
  });
};

const Resources = (): JSX.Element => {
  useDetails();
  useFilter();

  return <ResourcesPage />;
};

export default Resources;
