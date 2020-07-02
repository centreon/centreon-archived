import * as React from 'react';

import { isNil } from 'ramda';

import { makeStyles, Slide } from '@material-ui/core';

import { withSnackbar, Loader } from '@centreon/ui';

import Context from './Context';
import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import useFilter from './Filter/useFilter';
import useListing from './Listing/useListing';
import useActions from './Actions/useActions';
import useDetails from './Details/useDetails';

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

const Resources = (): JSX.Element | null => {
  const classes = useStyles();

  const listingContext = useListing();
  const filterContext = useFilter();
  const detailsContext = useDetails();
  const actionsContext = useActions();

  const { selectedDetailsEndpoints } = detailsContext;

  if (!filterContext.customFilters) {
    return null;
  }

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
                <Details />
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
