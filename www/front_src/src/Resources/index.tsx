import * as React from 'react';

import { isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { ListingPage, WithPanel } from '@centreon/ui';

import Context from './Context';
import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import useFilter from './Filter/useFilter';
import useListing from './Listing/useListing';
import useActions from './Actions/useActions';
import EditFiltersPanel from './Filter/Edit';
import memoizeComponent from './memoizedComponent';
import { selectedResourceIdAtom } from './Details/detailsAtoms';
import useLoadDetails from './Details/useLoadDetails';
import useDetails from './Details/useDetails';

interface Props {
  editPanelOpen: boolean;
}

const ResourcesPage = ({ editPanelOpen }: Props): JSX.Element => {
  const selectedResourceId = useAtomValue(selectedResourceIdAtom);

  return (
    <WithPanel open={editPanelOpen} panel={<EditFiltersPanel />}>
      <ListingPage
        filter={<Filter />}
        listing={<Listing />}
        panel={<Details />}
        panelOpen={!isNil(selectedResourceId)}
      />
    </WithPanel>
  );
};

const memoProps = ['editPanelOpen'];

const MemoizedResourcesPage = memoizeComponent<Props>({
  Component: ResourcesPage,
  memoProps,
});

const Resources = (): JSX.Element => {
  const listingContext = useListing();
  const filterContext = useFilter();
  const detailsContext = useLoadDetails();
  const actionsContext = useActions();

  useDetails();

  return (
    <Context.Provider
      value={{
        ...listingContext,
        ...filterContext,
        ...detailsContext,
        ...actionsContext,
      }}
    >
      <MemoizedResourcesPage editPanelOpen={filterContext.editPanelOpen} />
    </Context.Provider>
  );
};

export default Resources;
