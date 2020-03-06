import React, { useEffect, useState } from 'react';

import axios from 'axios';

import { makeStyles } from '@material-ui/core';

import { lime, purple } from '@material-ui/core/colors';

import { Listing, withErrorSnackbar, useErrorSnackbar } from '@centreon/ui';

import { listResources } from './api';
import { ResourceListing } from './models';
import columns from './columns';
import Filter from './Filter';
import {
  filterById,
  unhandledProblemsFilter,
  Filter as FilterModel,
} from './Filter/models';

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

const defaultFilter = unhandledProblemsFilter;

const Resources = (): JSX.Element => {
  const classes = useStyles();

  const [listing, setListing] = useState<ResourceListing>();
  const [sorto, setSorto] = useState<string>();
  const [sortf, setSortf] = useState<string>();
  const [limit, setLimit] = useState<number>(10);
  const [page, setPage] = useState<number>(1);

  const [filter, setFilter] = useState(defaultFilter);
  const [search, setSearch] = useState<string>();
  const [resourceTypes, setResourceTypes] = useState<Array<FilterModel>>(
    defaultFilter.criterias.resourceTypes,
  );
  const [states, setStates] = useState<Array<FilterModel>>(
    defaultFilter.criterias.states,
  );
  const [statuses, setStatuses] = useState<Array<FilterModel>>(
    defaultFilter.criterias.statuses,
  );
  const [hostGroups, setHostGroups] = useState<Array<FilterModel>>();
  const [serviceGroups, setServiceGroups] = useState<Array<FilterModel>>();

  const [loading, setLoading] = useState(true);

  const { showError } = useErrorSnackbar();
  const [tokenSource] = useState(axios.CancelToken.source());

  const load = (): void => {
    setLoading(true);
    const sort = sortf ? { [sortf]: sorto } : undefined;

    listResources(
      {
        states: states.map(({ name }) => name),
        statuses: statuses.map(({ name }) => name),
        resourceTypes: resourceTypes.map(({ name }) => name),
        sort,
        limit,
        page,
        search,
      },
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
  }, [
    sortf,
    sorto,
    page,
    limit,
    search,
    states,
    statuses,
    resourceTypes,
    hostGroups,
    serviceGroups,
  ]);

  const doSearch = (value): void => {
    setSearch(value);
  };

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

  const changeResourceTypes = (_, updatedResourceTypes): void => {
    setResourceTypes(updatedResourceTypes);
  };

  const changeStates = (_, updatedStates): void => {
    setStates(updatedStates);
  };

  const changeStatuses = (_, updatedStatuses): void => {
    setStatuses(updatedStatuses);
  };

  const changeHostGroups = (_, updatedHostGroups): void => {
    setHostGroups(updatedHostGroups);
  };

  const changeServiceGroups = (_, updatedServiceGroups): void => {
    setServiceGroups(updatedServiceGroups);
  };

  const changeFilter = (event): void => {
    const filterId = event.target.value;
    setFilter(filterById[filterId]);
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
        filter={filter}
        onFilterChange={changeFilter}
        selectedResourceTypes={resourceTypes}
        onResourceTypeChange={changeResourceTypes}
        selectedStates={states}
        onStatesChange={changeStates}
        selectedStatuses={statuses}
        onStatusesChange={changeStatuses}
        onSearchRequest={doSearch}
        onHostgroupsChange={changeHostGroups}
        selectedHostGroups={hostGroups}
        onServiceGroupsChange={changeServiceGroups}
        selectedServiceGroups={serviceGroups}
        search={search}
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
