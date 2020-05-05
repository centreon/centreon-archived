import * as React from 'react';

import { isNil } from 'ramda';

import { makeStyles, Slide } from '@material-ui/core';

import { withSnackbar } from '@centreon/ui';

import { ResourceEndpoints } from './models';
import Context from './Context';
import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import useFilter from './Filter/useFilter';
import useListing from './Listing/useListing';
import useActions from './Actions/useActions';

const useStyles = makeStyles((theme) => ({
  page: {
    display: 'grid',
    gridTemplateRows: 'auto 1fr',
    backgroundColor: theme.palette.background.default,
    overflowY: 'hidden',
  },
  body: {
    display: 'grid',
    gridTemplateRows: '1fr',
    gridTemplateColumns: '1fr 550px',
  },
  panel: {
    gridArea: '1 / 2',
    zIndex: 3,
  },
  filter: {
    zIndex: 4,
  },
  listing: {
    marginLeft: theme.spacing(2),
    marginRight: theme.spacing(2),
    gridArea: '1 / 1 / 1 / span 2',
  },
}));

const useDetails = () => {
  const [
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
  ] = React.useState<ResourceEndpoints | null>(null);

  const [detailsTabIdToOpen, setDefaultDetailsTabIdToOpen] = React.useState(0);

  return {
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
    detailsTabIdToOpen,
    setDefaultDetailsTabIdToOpen,
  };
};

const Resources = (): JSX.Element => {
  const classes = useStyles();

  const listingContext = useListing();
  const filterContext = useFilter();
  const detailsContext = useDetails();
  const actionsContext = useActions();

  const {
    detailsTabIdToOpen,
    setDefaultDetailsTabIdToOpen,
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
  } = detailsContext;

  const clearSelectedResource = (): void => {
    setSelectedDetailsEndpoints(null);
  };

  const selectDetailsTabToOpen = (id): void => {
    setDefaultDetailsTabIdToOpen(id);
  };

  return (
    <Context.Provider
      value={{
        ...listingContext,
        ...filterContext,
        ...detailsContext,
        ...actionsContext,
      }}
    >
      <div className={classes.page}>
        <div className={classes.filter}>
          <Filter />
        </div>
        <div className={classes.body}>
          {selectedDetailsEndpoints && (
            <Slide
              direction="left"
              in={!isNil(selectedDetailsEndpoints)}
              timeout={{
                enter: 150,
                exit: 50,
              }}
            >
              <div className={classes.panel}>
                <Details
                  endpoints={selectedDetailsEndpoints}
                  openTabId={detailsTabIdToOpen}
                  onClose={clearSelectedResource}
                  onSelectTab={selectDetailsTabToOpen}
                />
              </div>
            </Slide>
          )}
          <div className={classes.listing}>
            <Listing />
          </div>
        </div>
      </div>
    </Context.Provider>
  );
};

export default withSnackbar(Resources);
