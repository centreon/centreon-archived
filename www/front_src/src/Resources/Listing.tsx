import * as React from 'react';

import { isNil, equals } from 'ramda';
import { useSelector } from 'react-redux';

import { useTheme, fade } from '@material-ui/core';

import { Listing } from '@centreon/ui';

import { detailsTabId, graphTabId } from './Details/Body/tabs';
import { rowColorConditions } from './colors';
import { labelRowsPerPage, labelOf } from './translatedLabels';
import { getColumns } from './columns';
import { useResourceContext } from './Context';
import Actions from './Actions';

const useLoadResources = () => {
  const {
    sortf,
    sorto,
    states,
    statuses,
    resourceTypes,
    hostGroups,
    serviceGroups,
    limit,
    page,
    currentSearch,
    nextSearch,
    setListing,
    sendRequest,
    enabledAutorefresh,
  } = useResourceContext();

  const refreshIntervalRef = React.useRef<number>();

  const refreshIntervalMs = useSelector(
    (state) => state.intervals.AjaxTimeReloadMonitoring * 1000,
  );

  const load = (): void => {
    const sort = sortf ? { [sortf]: sorto } : undefined;

    sendRequest({
      states: states.map(({ id }) => id),
      statuses: statuses.map(({ id }) => id),
      resourceTypes: resourceTypes.map(({ id }) => id),
      hostGroupIds: hostGroups?.map(({ id }) => id),
      serviceGroupIds: serviceGroups?.map(({ id }) => id),
      sort,
      limit,
      page,
      search: currentSearch,
    }).then(setListing);
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
    initAutorefresh();
  }, [enabledAutorefresh]);

  React.useEffect(() => {
    return (): void => {
      clearInterval(refreshIntervalRef.current);
    };
  }, []);

  React.useEffect(() => {
    if (currentSearch !== nextSearch) {
      return;
    }
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

  return { initAutorefreshAndLoad };
};

const ResourceListing = (): JSX.Element => {
  const theme = useTheme();

  const {
    listing,
    sortf,
    setSortf,
    sorto,
    setSorto,
    setLimit,
    page,
    setPage,
    setDefaultDetailsTabIdToOpen,
    setSelectedDetailsEndpoints,
    selectedDetailsEndpoints,
    setSelectedResources,
    selectedResources,
    setResourcesToAcknowledge,
    setResourcesToSetDowntime,
    setResourcesToCheck,
    sending,
  } = useResourceContext();

  const { initAutorefreshAndLoad } = useLoadResources();

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

  const resourceDetailsOpenCondition = {
    name: 'detailsOpen',
    condition: ({ details_endpoint }): boolean =>
      equals(details_endpoint, selectedDetailsEndpoints?.details),
    color: fade(theme.palette.primary.main, 0.08),
  };

  const labelDisplayedRows = ({ from, to, count }): string =>
    `${from}-${to} ${labelOf} ${count}`;

  const columns = getColumns({
    onAcknowledge: (resource) => {
      setResourcesToAcknowledge([resource]);
    },
    onDowntime: (resource) => {
      setResourcesToSetDowntime([resource]);
    },
    onCheck: (resource) => {
      setResourcesToCheck([resource]);
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

  const loading = sending;

  return (
    <Listing
      checkable
      Actions={<Actions onRefresh={initAutorefreshAndLoad} />}
      loading={loading}
      columnConfiguration={columns}
      tableData={listing?.result}
      currentPage={page - 1}
      rowColorConditions={[
        ...rowColorConditions(theme),
        resourceDetailsOpenCondition,
      ]}
      limit={listing?.meta.limit}
      onSort={changeSort}
      onPaginationLimitChanged={changeLimit}
      onPaginate={changePage}
      sortf={sortf}
      sorto={sorto}
      labelRowsPerPage={labelRowsPerPage}
      labelDisplayedRows={labelDisplayedRows}
      totalRows={listing?.meta.total}
      onSelectRows={setSelectedResources}
      selectedRows={selectedResources}
      onRowClick={selectResource}
      innerScrollDisabled={false}
    />
  );
};

export default ResourceListing;
