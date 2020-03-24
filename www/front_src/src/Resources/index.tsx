import React, { useEffect, useState } from 'react';

import axios from 'axios';

import { makeStyles } from '@material-ui/core';

import { Listing, withSnackbar, useSnackbar, Severity } from '@centreon/ui';

import { listResources } from './api';
import { ResourceListing, Resource } from './models';
import getColumns from './columns';
import Filter from './Filter';
import {
  filterById,
  unhandledProblemsFilter,
  Filter as FilterModel,
  FilterGroup,
} from './Filter/models';
import Actions from './Actions';
import Details from './Details';
import { rowColorConditions } from './colors';

const useStyles = makeStyles((theme) => ({
  page: {
    backgroundColor: theme.palette.background.default,
  },
  panel: {
    position: 'absolute',
    right: 0,
    height: '100%',
    width: 500,
    zIndex: 3,
  },
  listing: {
    marginLeft: theme.spacing(1),
    marginRight: theme.spacing(1),
  },
}));

const defaultFilter = unhandledProblemsFilter;
const { criterias } = defaultFilter;
const defaultResourceTypes = criterias?.resourceTypes;
const defaultStatuses = criterias?.statuses;
const defaultStates = criterias?.states;

type SortOrder = 'asc' | 'desc';

const Resources = (): JSX.Element => {
  const classes = useStyles();

  const [listing, setListing] = useState<ResourceListing>();
  const [selectedResources, setSelectedResources] = useState<Array<Resource>>(
    [],
  );
  const [resourcesToAcknowledge, setResourcesToAcknoweledge] = React.useState<
    Array<Resource>
  >([]);

  const [sorto, setSorto] = useState<SortOrder>();
  const [sortf, setSortf] = useState<string>();
  const [limit, setLimit] = useState<number>(10);
  const [page, setPage] = useState<number>(1);

  const [filter, setFilter] = useState(defaultFilter);
  const [search, setSearch] = useState<string>();
  const [resourceTypes, setResourceTypes] = useState<Array<FilterModel>>(
    defaultResourceTypes,
  );
  const [states, setStates] = useState<Array<FilterModel>>(defaultStates);
  const [statuses, setStatuses] = useState<Array<FilterModel>>(defaultStatuses);
  const [hostGroups, setHostGroups] = useState<Array<FilterModel>>();
  const [serviceGroups, setServiceGroups] = useState<Array<FilterModel>>();

  const [selectedResourceId, setSelectedResourceId] = useState<string | null>(
    null,
  );

  const [loading, setLoading] = useState(true);

  const { showMessage } = useSnackbar();
  const showError = (message): void =>
    showMessage({ message, severity: Severity.error });

  const [tokenSource] = useState(axios.CancelToken.source());

  const load = (): void => {
    setLoading(true);
    const sort = sortf ? { [sortf]: sorto } : undefined;

    listResources(
      {
        states: states.map(({ id }) => id),
        statuses: statuses.map(({ id }) => id),
        resourceTypes: resourceTypes.map(({ id }) => id),
        hostGroupIds: hostGroups?.map(({ id }) => id),
        serviceGroupIds: serviceGroups?.map(({ id }) => id),
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

  const setEmptyFilter = (): void => {
    setFilter({ id: '', name: '' } as FilterGroup);
  };

  const changeResourceTypes = (_, updatedResourceTypes): void => {
    setResourceTypes(updatedResourceTypes);
    setEmptyFilter();
  };

  const changeStates = (_, updatedStates): void => {
    setStates(updatedStates);
    setEmptyFilter();
  };

  const changeStatuses = (_, updatedStatuses): void => {
    setStatuses(updatedStatuses);
    setEmptyFilter();
  };

  const changeHostGroups = (_, updatedHostGroups): void => {
    setHostGroups(updatedHostGroups);
  };

  const changeServiceGroups = (_, updatedServiceGroups): void => {
    setServiceGroups(updatedServiceGroups);
  };

  const changeFilter = (event): void => {
    const filterId = event.target.value;

    const updatedFilter = filterById[filterId];
    setFilter(updatedFilter);

    if (!updatedFilter.criterias) {
      return;
    }

    setResourceTypes(updatedFilter.criterias.resourceTypes);
    setStatuses(updatedFilter.criterias.statuses);
    setStates(updatedFilter.criterias.states);
  };

  const clearAllFilters = (): void => {
    setFilter(defaultFilter);
    setResourceTypes(defaultFilter.criterias.resourceTypes);
    setStatuses(defaultFilter.criterias.statuses);
    setStates(defaultFilter.criterias.states);
  };

  const selectResources = (resources): void => {
    setSelectedResources(resources);
  };

  const confirmAndLoad = (): void => {
    load();
    selectResources([]);
    setResourcesToAcknoweledge([]);
  };

  const prepareToAcknowledge = (resources): void => {
    setResourcesToAcknoweledge(resources);
  };

  const prepareSelectedToAcknowledge = (): void => {
    prepareToAcknowledge(selectedResources);
  };

  const cancelAcknowledge = (): void => {
    prepareToAcknowledge([]);
  };

  const selectResource = ({ id }): void => {
    setSelectedResourceId(id);
  };

  const clearSelectedResource = (): void => {
    setSelectedResourceId(null);
  };

  const columns = getColumns({
    onAcknowledge: (resource) => {
      prepareToAcknowledge([resource]);
    },
  });

  const hasSelectedResources = selectedResources.length > 0;

  const ResourceActions = (
    <Actions
      disabled={!hasSelectedResources}
      resourcesToAcknowledge={resourcesToAcknowledge}
      onPrepareToAcknowledge={prepareSelectedToAcknowledge}
      onCancelAcknowledge={cancelAcknowledge}
      onSuccess={confirmAndLoad}
    />
  );

  return (
    <div className={classes.page}>
      <Filter
        filter={filter}
        onFilterGroupChange={changeFilter}
        selectedResourceTypes={resourceTypes}
        onResourceTypesChange={changeResourceTypes}
        selectedStates={states}
        onStatesChange={changeStates}
        selectedStatuses={statuses}
        onStatusesChange={changeStatuses}
        onSearchRequest={doSearch}
        onHostGroupsChange={changeHostGroups}
        selectedHostGroups={hostGroups}
        onServiceGroupsChange={changeServiceGroups}
        selectedServiceGroups={serviceGroups}
        onClearAll={clearAllFilters}
        currentSearch={search}
      />
      <div className={classes.panel}>
        <Details
          resourceId={selectedResourceId}
          onClose={clearSelectedResource}
        />
      </div>
      <div className={classes.listing}>
        <Listing
          checkable
          Actions={ResourceActions}
          loading={loading}
          columnConfiguration={columns}
          tableData={listing?.result}
          currentPage={page - 1}
          rowColorConditions={rowColorConditions}
          limit={listing?.meta.limit}
          onSort={changeSort}
          onPaginationLimitChanged={changeLimit}
          onPaginate={changePage}
          sortf={sortf}
          sorto={sorto}
          totalRows={listing?.meta.total}
          onSelectRows={selectResources}
          selectedRows={selectedResources}
          onRowClick={selectResource}
        />
      </div>
    </div>
  );
};

export default withSnackbar(Resources);
