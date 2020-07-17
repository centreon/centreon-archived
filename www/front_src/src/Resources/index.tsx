/* eslint-disable @typescript-eslint/no-unused-vars */
import * as React from 'react';

import { isNil } from 'ramda';

import { makeStyles, Slide } from '@material-ui/core';

import { withSnackbar, ContentWithCircularLoading } from '@centreon/ui';

import Context from './Context';
import Filter from './Filter';
import Listing from './Listing';
import Details from './Details';
import useFilter from './Filter/useFilter';
import useListing from './Listing/useListing';
import useActions from './Actions/useActions';
import useDetails from './Details/useDetails';
import EditFiltersPanel from './Filter/Edit';

const useStyles = makeStyles((theme) => ({
  loadingIndicator: {
    width: '100%',
    heihgt: '100%',
    position: 'absolute',
    top: '50%',
    left: '50%',
  },
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

const Resources = (): JSX.Element => {
  const classes = useStyles();

  const listingContext = useListing();
  const filterContext = useFilter();
  const detailsContext = useDetails();
  const actionsContext = useActions();

  const { selectedDetailsEndpoints } = detailsContext;

  const loading = isNil(filterContext.customFilters);

  return (
    <Context.Provider
      value={{
        ...listingContext,
        ...filterContext,
        ...detailsContext,
        ...actionsContext,
      }}
    >
      <ContentWithCircularLoading loading={loading}>
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
      </ContentWithCircularLoading>
      <EditFiltersPanel />
    </Context.Provider>
  );
};

export default withSnackbar(Resources);
