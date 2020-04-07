import * as React from 'react';

import axios from 'axios';
import { useSelector } from 'react-redux';
import { isNil } from 'ramda';

import { makeStyles, useTheme, Grid, Slide } from '@material-ui/core';

import { Listing, withSnackbar, useSnackbar, Severity } from '@centreon/ui';

import { listResources } from './api';
import { ResourceListing, Resource, ResourceEndpoints } from './models';

import { defaultSortField, defaultSortOrder, getColumns } from './columns';
import Filter from './Filter';
import { filterById, FilterGroup, allFilter } from './Filter/models';
import ResourceActions from './Actions/Resource';
import GlobalActions from './Actions/Refresh';
import Details from './Details';
import { rowColorConditions } from './colors';
import { detailsTabId, graphTabId } from './Details/Body/tabs';
import useFilter from './Filter/useFilter';

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

type SortOrder = 'asc' | 'desc';

const Resources = (): JSX.Element => {
  const classes = useStyles();
  const theme = useTheme();

  const [listing, setListing] = React.useState<ResourceListing>();
  const [selectedResources, setSelectedResources] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToAcknowledge, setResourcesToAcknowledge] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToSetDowntime, setResourcesToSetDowntime] = React.useState<
    Array<Resource>
  >([]);
  const [resourcesToCheck, setResourcesToCheck] = React.useState<
    Array<Resource>
  >([]);

  const [sorto, setSorto] = React.useState<SortOrder>(defaultSortOrder);
  const [sortf, setSortf] = React.useState<string>(defaultSortField);
  const [limit, setLimit] = React.useState<number>(30);
  const [page, setPage] = React.useState<number>(1);

  const {
    filter,
    setFilter,
    currentSearch,
    setCurrentSearch,
    nextSearch,
    setNextSearch,
    resourceTypes,
    setResourceTypes,
    states,
    setStates,
    statuses,
    setStatuses,
    hostGroups,
    setHostGroups,
    serviceGroups,
    setServiceGroups,
  } = useFilter();

  const [
    selectedDetailsEndpoints,
    setSelectedDetailsEndpoints,
  ] = React.useState<ResourceEndpoints | null>(null);

  const [detailsTabIdToOpen, setDefaultDetailsTabIdToOpen] = React.useState(0);

  const [loading, setLoading] = React.useState(true);
  const [enabledAutorefresh, setEnabledAutorefresh] = React.useState(true);

  const refreshIntervalMs = useSelector(
    (state) => state.intervals.AjaxTimeReloadMonitoring * 1000,
  );
  const refreshIntervalRef = React.useRef<number>();

  const { showMessage } = useSnackbar();
  const showError = (message): void =>
    showMessage({ message, severity: Severity.error });

  const [tokenSource] = React.useState(axios.CancelToken.source());

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
        search: currentSearch,
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

  const initAutorefresh = (): void => {
    window.clearInterval(refreshIntervalRef.current);

    const interval = enabledAutorefresh
      ? window.setInterval(load, refreshIntervalMs)
      : undefined;

    refreshIntervalRef.current = interval;
  };

  const initAutorefreshAndLoad = (): void => {
    initAutorefresh();
    load();
  };

  React.useEffect(() => {
    return (): void => {
      tokenSource.cancel();
      clearInterval(refreshIntervalRef.current);
    };
  }, []);

  React.useEffect(() => {
    initAutorefreshAndLoad();
  }, [
    sortf,
    sorto,
    page,
    limit,
    currentSearch,
    states,
    statuses,
    resourceTypes,
    hostGroups,
    serviceGroups,
  ]);

  React.useEffect(() => {
    initAutorefresh();
  }, [enabledAutorefresh]);

  const prepareSearch = (event): void => {
    setNextSearch(event.target.value);
  };

  const requestSearch = (): void => {
    setCurrentSearch(nextSearch);
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
    setFilter(allFilter);
    setResourceTypes(allFilter.criterias.resourceTypes);
    setStatuses(allFilter.criterias.statuses);
    setStates(allFilter.criterias.states);
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
    if (isNil(performance_graph_endpoint)) {
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

  const toggleAutorefresh = (): void => {
    setEnabledAutorefresh(!enabledAutorefresh);
  };

  const hasSelectedResources = selectedResources.length > 0;

  const Actions = (
    <Grid container>
      <Grid item>
        <ResourceActions
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
      </Grid>
      <Grid item style={{ paddingLeft: theme.spacing(3) }}>
        <GlobalActions
          disabledRefresh={loading}
          enabledAutorefresh={enabledAutorefresh}
          onRefresh={initAutorefreshAndLoad}
          toggleAutorefresh={toggleAutorefresh}
        />
      </Grid>
    </Grid>
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
          onSearchRequest={requestSearch}
          onSearchPrepare={prepareSearch}
          currentSearch={currentSearch}
          nextSearch={nextSearch}
          onHostGroupsChange={changeHostGroups}
          selectedHostGroups={hostGroups}
          onServiceGroupsChange={changeServiceGroups}
          selectedServiceGroups={serviceGroups}
          onClearAll={clearAllFilters}
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
            Actions={Actions}
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
