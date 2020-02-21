import React, { useEffect, useState } from 'react';

import { Typography, makeStyles, Paper, Grid } from '@material-ui/core';

import { Listing, SelectField } from '@centreon/ui';

import { listResources } from './api';
import { Listing as ListingEntity } from './models';
import columns from './columns';

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

  const [listing, setListing] = useState<ListingEntity>({
    result: [],
    meta: { page: 1, total: 0, limit: 10 },
  });

  useEffect(() => {
    listResources().then((retrievedListing) => setListing(retrievedListing));
  }, []);

  return (
    <div className={classes.page}>
      <Paper elevation={1} className={classes.filterBox}>
        <Grid container direction="column" spacing={1}>
          <Grid item>
            <Typography variant="h6">Filter</Typography>
          </Grid>
          <Grid item>
            <Grid spacing={2} container alignItems="center">
              <Grid item>
                <SelectField
                  options={[{ id: 0, name: 'Unhandled problems' }]}
                  selectedOptionId={0}
                />
              </Grid>
            </Grid>
          </Grid>
        </Grid>
      </Paper>
      <div className={classes.listing}>
        <Listing
          columnConfiguration={columns}
          tableData={listing.result}
          currentPage={listing.meta.page - 1}
          limit={listing.meta.limit}
          onDelete={noOp}
          onSort={noOp}
          onDuplicate={noOp}
          onPaginationLimitChanged={noOp}
          totalRows={listing.meta.total}
          selectedRows={[]}
          checkable
        />
      </div>
    </div>
  );
};

export default Resources;
