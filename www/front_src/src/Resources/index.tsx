import * as React from 'react';

import { isNil } from 'ramda';

import { withSnackbar, ListingPage, WithPanel } from '@centreon/ui';

import Context from './Context';
import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import useFilter from './Filter/useFilter';
import useListing from './Listing/useListing';
import useActions from './Actions/useActions';
import useDetails from './Details/useDetails';
import EditFiltersPanel from './Filter/Edit';

const Resources = (): JSX.Element => {
  const listingContext = useListing();
  const filterContext = useFilter();
  const detailsContext = useDetails();
  const actionsContext = useActions();

  const { selectedDetailsLinks } = detailsContext;

  return (
    <Context.Provider
      value={{
        ...listingContext,
        ...filterContext,
        ...detailsContext,
        ...actionsContext,
      }}
    >
      <WithPanel
        open={filterContext.editPanelOpen}
        panel={<EditFiltersPanel />}
      >
        <ListingPage
          filters={<Filter />}
          listing={<Listing />}
          panel={<Details />}
          panelOpen={!isNil(selectedDetailsLinks)}
        />
      </WithPanel>
    </Context.Provider>
  );
};

export default withSnackbar(Resources);
