import React, { useEffect, useState } from 'react';

import axios from 'axios';
import { isNil } from 'ramda';

import { makeStyles, useTheme, Slide } from '@material-ui/core';

import { Listing, withSnackbar, useSnackbar, Severity } from '@centreon/ui';

import { listResources } from './api';
import { ResourceListing, Resource, ResourceEndpoints } from './models';

import { defaultSortField, defaultSortOrder, getColumns } from './columns';
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
import { detailsTabId, graphTabId } from './Details/Body/tabs';

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
    marginLeft: theme.spacing(2),
    marginRight: theme.spacing(2),
  },
  listing: {
    marginLeft: theme.spacing(2),
    marginRight: theme.spacing(2),
    gridArea: '1 / 1 / 1 / span 2',
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
  const theme = useTheme();

  const [listing, setListing] = useState<ResourceListing>();
  const [selectedResources, setSelectedResources] = useState<Array<Resource>>(
    [],
  );
  const [resourcesToAcknowledge, setResourcesToAcknowledge] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToSetDowntime, setResourcesToSetDowntime] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToCheck, setResourcesToCheck] = React.useState<
    Array<Resource>
  >([]);

  const [sorto, setSorto] = useState<SortOrder>(defaultSortOrder);
  const [sortf, setSortf] = useState<string>(defaultSortField);
  const [limit, setLimit] = useState<number>(30);
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

  const [
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
  ] = useState<ResourceEndpoints | null>(null);

  const [detailsTabIdToOpen, setDefaultDetailsTabIdToOpen] = useState(0);

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
        setListing(undefined);
        showError(error.response?.data?.message || error.message);
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

  const confirmAction = (): void => {
    selectResources([]);
    setResourcesToAcknowledge([]);
    setResourcesToSetDowntime([]);
    setResourcesToCheck([]);
  };

  const prepareToAcknowledge = (resources): void => {
    setResourcesToAcknowledge(resources);
  };

  const prepareSelectedToAcknowledge = (): void => {
    prepareToAcknowledge(selectedResources);
  };

  const cancelAcknowledge = (): void => {
    prepareToAcknowledge([]);
  };

  const prepareToSetDowntime = (resources): void => {
    setResourcesToSetDowntime(resources);
  };

  const prepareSelectedToSetDowntime = (): void => {
    prepareToSetDowntime(selectedResources);
  };

  const cancelSetDowntime = (): void => {
    prepareToSetDowntime([]);
  };

  const prepareToCheck = (resources): void => {
    setResourcesToCheck(resources);
  };

  const prepareSelectedToCheck = (): void => {
    prepareToCheck(selectedResources);
  };

  const columns = getColumns({
    onAcknowledge: (resource) => {
      prepareToAcknowledge([resource]);
    },
    onDowntime: (resource) => {
      prepareToSetDowntime([resource]);
    },
    onCheck: (resource) => {
      prepareToCheck([resource]);
    },
    onDisplayGraph: ({
      details_endpoint,
      status_graph_endpoint,
      performance_graph_endpoint,
    }) => {
      setDefaultDetailsTabIdToOpen(graphTabId);
      setSelectedDetailsEndpoints({
        details: details_endpoint,
        statusGraph: status_graph_endpoint,
        performanceGraph: performance_graph_endpoint,
      });
    },
  });

  const selectResource = ({
    details_endpoint,
    status_graph_endpoint,
    performance_graph_endpoint,
  }): void => {
    if (isNil(selectedDetailsEndpoints)) {
      setDefaultDetailsTabIdToOpen(detailsTabId);
    }
    setSelectedDetailsEndpoints({
      details: details_endpoint,
      statusGraph: status_graph_endpoint,
      performanceGraph: performance_graph_endpoint,
    });
  };

  const clearSelectedResource = (): void => {
    setSelectedDetailsEndpoints(null);
  };

  const hasSelectedResources = selectedResources.length > 0;

  const ResourceActions = (
    <Actions
      disabled={!hasSelectedResources}
      resourcesToAcknowledge={resourcesToAcknowledge}
      onPrepareToAcknowledge={prepareSelectedToAcknowledge}
      onCancelAcknowledge={cancelAcknowledge}
      resourcesToSetDowntime={resourcesToSetDowntime}
      onPrepareToSetDowntime={prepareSelectedToSetDowntime}
      onCancelSetDowntime={cancelSetDowntime}
      resourcesToCheck={resourcesToCheck}
      onPrepareToCheck={prepareSelectedToCheck}
      onSuccess={confirmAction}
    />
  );

  return (
    <div className={classes.page}>
      <div className={classes.filter}>
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
              />
            </div>
          </Slide>
        )}
        <div className={classes.listing}>
          <Listing
            checkable
            Actions={ResourceActions}
            loading={loading}
            columnConfiguration={columns}
            tableData={listing?.result}
            currentPage={page - 1}
            rowColorConditions={rowColorConditions(theme)}
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
            innerScrollDisabled={false}
          />
        </div>
      </div>
    </div>
  );
};

export default withSnackbar(Resources);
