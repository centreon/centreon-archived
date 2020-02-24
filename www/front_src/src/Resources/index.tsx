import React, { useEffect, useState } from 'react';

import axios from 'axios';

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
} from './models';
import columns from './columns';
import { labelFilter } from './translatedLabels';

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

  const [listing, setListing] = useState<ListingEntity<Resource> | undefined>(
    undefined,
  );
  const [filterId, setFilterId] = useState('unhandled_problems');
  const [sort, setSort] = useState<{ [orderBy: string]: string }>({});
  const [loading, setLoading] = useState(true);
  const { showError } = useErrorSnackbar();
  const [tokenSource] = useState(axios.CancelToken.source());

  const load = (): void => {
    setLoading(true);
    listResources({ state: filterId, sort }, { cancelToken: tokenSource.token })
      .then((retrievedListing) => setListing(retrievedListing))
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
  }, [filterId]);

  useEffect(() => {
    if (!sort) {
      return;
    }

    load();
  }, [sort]);

  const changeFilterId = (event): void => {
    setFilterId(event.target.value);
  };

  const changeSort = ({ order, orderBy }): void => {
    setSort({ [orderBy]: order });
  };

  const getSortf = (): string => {
    const [sorto] = Object.keys(sort);

    return sorto;
  };

  const getSorto = (): string => {
    return sort[getSortf()];
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
          currentPage={listing ? listing.meta.page - 1 : 0}
          limit={listing?.meta.limit}
          onDelete={noOp}
          onSort={changeSort}
          onDuplicate={noOp}
          onPaginationLimitChanged={noOp}
          sortf={getSortf()}
          sorto={getSorto()}
          totalRows={listing?.meta.total}
          selectedRows={[]}
          checkable
        />
      </div>
    </div>
  );
};

export default withErrorSnackbar(Resources);
