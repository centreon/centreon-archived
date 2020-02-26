import React, { useEffect, useState } from 'react';

import axios from 'axios';
import isEmpty from 'lodash/isEmpty';

import { Typography, makeStyles, Paper, Grid } from '@material-ui/core';

import {
  Listing,
  SelectField,
  withErrorSnackbar,
  useErrorSnackbar,
} from '@centreon/ui';

import { listResources } from './api';
import {
  Listing as ListingEntity,
  Resource,
  unhandledProblemsFilter,
  resourcesProblemFilter,
  allFilter,
  ResourceListing,
} from './models';
import columns from './columns';
import { labelFilter, labelStateFilter } from './translatedLabels';

const useStyles = makeStyles((theme) => ({
  page: {
    backgroundColor: theme.palette.background.default,
  },
  iconSettings: {
    color: theme.palette.primary.main,
  },
  filterBox: {
    padding: theme.spacing(2),
    backgroundColor: theme.palette.common.white,
  },
  listing: {
    marginLeft: theme.spacing(1),
    marginRight: theme.spacing(1),
  },
}));

const noOp = (): void => undefined;

const Resources = (): JSX.Element => {
  const classes = useStyles();

  const [listing, setListing] = useState<ResourceListing>();
  const [filterId, setFilterId] = useState('unhandled_problems');
  const [sorto, setSorto] = useState<string>();
  const [sortf, setSortf] = useState<string>();
  const [limit, setLimit] = useState<number>(10);
  const [page, setPage] = useState<number>(1);

  const [loading, setLoading] = useState(true);

  const { showError } = useErrorSnackbar();
  const [tokenSource] = useState(axios.CancelToken.source());

  const load = (): void => {
    setLoading(true);
    const sort = sortf ? { [sortf]: sorto } : undefined;

    listResources(
      { state: filterId, sort, limit, page },
      { cancelToken: tokenSource.token },
    )
      .then((retrievedListing) => {
        setListing(retrievedListing);
      })
      .catch((error) => {
        showError(error.message);
      })
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    return (): void => {
      tokenSource.cancel();
    };
  }, []);

  useEffect(() => {
    load();
  }, [filterId, sortf, sorto, page, limit]);

  const changeFilterId = (event): void => {
    setFilterId(event.target.value);
  };

  const changeSort = ({ order, orderBy }): void => {
    setSortf(orderBy);
    setSorto(order);
  };

  const changeLimit = ({ target }): void => {
    setLimit(Number(target.value));
  };

  const changePage = (_, updatedPage): void => {
    setPage(updatedPage + 1);
  };

  return (
    <div className={classes.page}>
      <Paper elevation={1} className={classes.filterBox}>
        <Grid container direction="column" spacing={1}>
          <Grid item>
            <Typography variant="h6">{labelFilter}</Typography>
          </Grid>
          <Grid item>
            <Grid spacing={2} container alignItems="center">
              <Grid item>
                <SelectField
                  options={[
                    unhandledProblemsFilter,
                    resourcesProblemFilter,
                    allFilter,
                  ]}
                  selectedOptionId={filterId}
                  onChange={changeFilterId}
                  ariaLabel={labelStateFilter}
                />
              </Grid>
            </Grid>
          </Grid>
        </Grid>
      </Paper>
      <div className={classes.listing}>
        <Listing
          loading={loading}
          columnConfiguration={columns}
          tableData={listing?.result}
          currentPage={page - 1}
          limit={listing?.meta.limit}
          onDelete={noOp}
          onSort={changeSort}
          onDuplicate={noOp}
          onPaginationLimitChanged={changeLimit}
          onPaginate={changePage}
          sortf={sortf}
          sorto={sorto}
          totalRows={listing?.meta.total}
          checkable={false}
        />
      </div>
    </div>
  );
};

export default withErrorSnackbar(Resources);
