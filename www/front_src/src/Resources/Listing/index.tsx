import * as React from 'react';

import { equals } from 'ramda';

import { useTheme, fade } from '@material-ui/core';

import { Listing } from '@centreon/ui';

import { graphTabId } from '../Details/tabs';
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
    setSelectedResourceId,
    setSelectedResourceParentId,
    setSelectedResourceType,
    setSelectedResourceParentType,
    selectedResourceId,
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

  const selectResource = ({ id, type, parent }: Resource): void => {
    setSelectedResourceId(id);
    setSelectedResourceParentId(parent?.id);
    setSelectedResourceType(type);
    setSelectedResourceParentType(parent?.type);
  };

  const resourceDetailsOpenCondition = {
    name: 'detailsOpen',
    condition: ({ id }): boolean => equals(id, selectedResourceId),
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
