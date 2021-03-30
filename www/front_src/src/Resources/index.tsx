import * as React from 'react';

import { isNil } from 'ramda';

import { withSnackbar, ListingPage } from '@centreon/ui';
import WithPanel from '@centreon/ui/src/Panel/WithPanel';

import Context from './Context';
import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import useFilter from './Filter/useFilter';
import useListing from './Listing/useListing';
import useActions from './Actions/useActions';
import useDetails from './Details/useDetails';
import EditFiltersPanel from './Filter/Edit';
import memoizeComponent from './memoizedComponent';

interface Props {
  editPanelOpen: boolean;
  selectedResourceId?: number;
}

const ResourcesPage = ({
  editPanelOpen,
  selectedResourceId,
}: Props): JSX.Element => (
  <WithPanel panel={<EditFiltersPanel />} open={editPanelOpen}>
    <ListingPage
      panelOpen={!isNil(selectedResourceId)}
      filters={<Filter />}
      listing={<Listing />}
      panel={<Details />}
    />
  </WithPanel>
);

const memoProps = ['editPanelOpen', 'selectedResourceId'];

const MemoizedResourcesPage = memoizeComponent<Props>({
  memoProps,
  Component: ResourcesPage,
});

const Resources = (): JSX.Element => {
  const listingContext = useListing();
  const filterContext = useFilter();
  const detailsContext = useDetails();
  const actionsContext = useActions();

  const { selectedResourceId } = detailsContext;

  return (
    <Context.Provider
      value={{
        ...listingContext,
        ...filterContext,
        ...detailsContext,
        ...actionsContext,
      }}
    >
      <MemoizedResourcesPage
        editPanelOpen={filterContext.editPanelOpen}
        selectedResourceId={selectedResourceId}
      />
    </Context.Provider>
  );
};

export default withSnackbar(Resources);
