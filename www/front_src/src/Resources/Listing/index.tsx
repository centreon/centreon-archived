import * as React from 'react';

import { isNil, equals } from 'ramda';

import { useTheme, fade } from '@material-ui/core';

import { Listing } from '@centreon/ui';

import { detailsTabId, graphTabId } from '../Details/tabs';
import { rowColorConditions } from '../colors';
import {
  labelRowsPerPage,
  labelOf,
  labelNoResultsFound,
} from '../translatedLabels';
import { getColumns } from './columns';
import { useResourceContext } from '../Context';
import Actions from '../Actions';
import useLoadResources from './useLoadResources';

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
    timeline_endpoint,
  }): void => {
    if (isNil(performance_graph_endpoint)) {
      setDefaultDetailsTabIdToOpen(detailsTabId);
    }
    setSelectedDetailsEndpoints({
      details: details_endpoint,
      statusGraph: status_graph_endpoint,
      performanceGraph: performance_graph_endpoint,
      timeline: timeline_endpoint,
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
      timeline_endpoint,
    }) => {
      setDefaultDetailsTabIdToOpen(graphTabId);
      setSelectedDetailsEndpoints({
        details: details_endpoint,
        statusGraph: status_graph_endpoint,
        performanceGraph: performance_graph_endpoint,
        timeline: timeline_endpoint,
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
      emptyDataMessage={labelNoResultsFound}
    />
  );
};

export default ResourceListing;
