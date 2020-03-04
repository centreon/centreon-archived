import React, { useEffect, useState } from 'react';

import axios from 'axios';

import { makeStyles } from '@material-ui/core';
import { lime, purple } from '@material-ui/core/colors';

import { Listing, withErrorSnackbar, useErrorSnackbar } from '@centreon/ui';

import { listResources } from './api';
import { ResourceListing, Filter as FilterModel } from './models';
import columns from './columns';
import Filter from './Filter';

const useStyles = makeStyles((theme) => ({
  page: {
    backgroundColor: theme.palette.background.default,
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
  const [sorto, setSorto] = useState<string>();
  const [sortf, setSortf] = useState<string>();
  const [limit, setLimit] = useState<number>(10);
  const [page, setPage] = useState<number>(1);

  const [filterId, setFilterId] = useState('unhandled_problems');
  const [searchFieldValue, setSearchFieldValue] = useState<string>();
  const [search, setSearch] = useState<string>();
  const [resourceTypes, setResourceTypes] = useState<Array<FilterModel>>();
  const [states, setStates] = useState<Array<FilterModel>>();
  const [statuses, setStatuses] = useState<Array<FilterModel>>();

  const [loading, setLoading] = useState(true);

  const { showError } = useErrorSnackbar();
  const [tokenSource] = useState(axios.CancelToken.source());

  const load = (): void => {
    setLoading(true);
    const sort = sortf ? { [sortf]: sorto } : undefined;

    listResources(
      { state: filterId, sort, limit, page, search },
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
  }, [filterId, sortf, sorto, page, limit, search]);

  const changeSort = ({ order, orderBy }): void => {
    setSortf(orderBy);
    setSorto(order);
  };

  const changeLimit = (event): void => {
    setLimit(Number(event.target.value));
  };

  const changePage = (_, updatedPage): void => {
    setPage(updatedPage + 1);
  };

  const changeFilter = (event): void => {
    setFilterId(event.target.value);
  };

  const changeResourceTypes = (_, updatedResourceTypes): void => {
    setStates(updatedResourceTypes);
  };

  const changeStates = (_, updatedStates): void => {
    setStates(updatedStates);
  };

  const changeStatuses = (_, updatedStatuses): void => {
    setStatuses(updatedStatuses);
  };

  const rowColorConditions = [
    {
      name: 'inDowntime',
      condition: ({ in_downtime }): boolean => in_downtime,
      color: purple[500],
    },
    {
      name: 'acknowledged',
      condition: ({ acknowledged }): boolean => acknowledged,
      color: lime[900],
    },
  ];

  return (
    <div className={classes.page}>
      <Filter
        onFilterChange={changeFilter}
        filterId={filterId}
        selectedResourceTypes={resourceTypes}
        onResourceTypeChange={changeResourceTypes}
        selectedStates={states}
        onStatesChange={changeStates}
        onStatusesChange={changeStatuses}
      />
      <div className={classes.listing}>
        <Listing
          loading={loading}
          columnConfiguration={columns}
          tableData={listing?.result}
          currentPage={page - 1}
          rowColorConditions={rowColorConditions}
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
