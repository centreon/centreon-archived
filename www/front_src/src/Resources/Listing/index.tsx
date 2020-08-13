import * as React from 'react';

import {
  isNil,
  equals,
  pick,
  pipe,
  values,
  reject,
  isEmpty,
  path,
  prop,
} from 'ramda';

import { useTheme, fade } from '@material-ui/core';

import { Listing } from '@centreon/ui';

import { detailsTabId, graphTabId, getVisibleTabs } from '../Details/tabs';
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
import { Resource } from '../models';

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
    setOpenDetailsTabId,
    setSelectedDetailsLinks,
    openDetailsTabId,
    selectedDetailsLinks,
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
    performance_graph_endpoint,
    timeline_endpoint,
    parent,
    configuration_uri,
    logs_uri,
    reporting_uri,
  }: Resource): void => {
    const uris = {
      resource: {
        configuration: configuration_uri,
        logs: logs_uri,
        reporting: reporting_uri,
      },
      parent: {
        configuration: parent?.configuration_uri,
        logs: parent?.logs_uri,
        reporting: parent?.reporting_uri,
      },
    };

    const links = {
      endpoints: {
        details: details_endpoint,
        performanceGraph: performance_graph_endpoint,
        timeline: timeline_endpoint,
      },
      uris,
    };

    const isOpenTabVisible = getVisibleTabs(links)
      .map(prop('id'))
      .includes(openDetailsTabId);

    if (!isOpenTabVisible) {
      setOpenDetailsTabId(detailsTabId);
    }

    setSelectedDetailsLinks(links);
  };

  const resourceDetailsOpenCondition = {
    name: 'detailsOpen',
    condition: ({ details_endpoint }): boolean =>
      equals(
        details_endpoint,
        path(['endpoints', 'details'], selectedDetailsLinks),
      ),
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
    onDisplayGraph: (resource) => {
      setOpenDetailsTabId(graphTabId);

      selectResource(resource);
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
