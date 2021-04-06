import * as React from 'react';

import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';

import { useTheme, fade } from '@material-ui/core';

import { Listing } from '@centreon/ui';

import { graphTabId } from '../Details/tabs';
import { rowColorConditions } from '../colors';
import {
  labelRowsPerPage,
  labelOf,
  labelNoResultsFound,
} from '../translatedLabels';
import { useResourceContext } from '../Context';
import Actions from '../Actions';
import { Resource } from '../models';

import useLoadResources from './useLoadResources';
import { getColumns } from './columns';

const ResourceListing = (): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

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
    setSelectedResourceUuid,
    setSelectedResourceId,
    setSelectedResourceParentId,
    setSelectedResourceType,
    setSelectedResourceParentType,
    selectedResourceUuid,
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

  const selectResource = ({ uuid, id, type, parent }: Resource): void => {
    setSelectedResourceUuid(uuid);
    setSelectedResourceId(id);
    setSelectedResourceParentId(parent?.id);
    setSelectedResourceType(type);
    setSelectedResourceParentType(parent?.type);
  };

  const resourceDetailsOpenCondition = {
    color: fade(theme.palette.primary.main, 0.08),
    condition: ({ uuid }): boolean => equals(uuid, selectedResourceUuid),
    name: 'detailsOpen',
  };

  const labelDisplayedRows = ({ from, to, count }): string =>
    `${from}-${to} ${t(labelOf)} ${count}`;

  const columns = getColumns({
    actions: {
      onAcknowledge: (resource): void => {
        setResourcesToAcknowledge([resource]);
      },
      onCheck: (resource): void => {
        setResourcesToCheck([resource]);
      },
      onDisplayGraph: (resource): void => {
        setOpenDetailsTabId(graphTabId);

        selectResource(resource);
      },
      onDowntime: (resource): void => {
        setResourcesToSetDowntime([resource]);
      },
    },
    t,
  });

  const loading = sending;

  const getId = ({ uuid }) => uuid;

  return (
    <Listing
      checkable
      Actions={<Actions onRefresh={initAutorefreshAndLoad} />}
      columnConfiguration={columns}
      currentPage={(page || 1) - 1}
      emptyDataMessage={t(labelNoResultsFound)}
      getId={getId}
      innerScrollDisabled={false}
      labelDisplayedRows={labelDisplayedRows}
      labelRowsPerPage={t(labelRowsPerPage)}
      limit={listing?.meta.limit}
      loading={loading}
      rowColorConditions={[
        ...rowColorConditions(theme),
        resourceDetailsOpenCondition,
      ]}
      selectedRows={selectedResources}
      sortf={sortf}
      sorto={sorto}
      tableData={listing?.result}
      totalRows={listing?.meta.total}
      onPaginate={changePage}
      onPaginationLimitChanged={changeLimit}
      onRowClick={selectResource}
      onSelectRows={setSelectedResources}
      onSort={changeSort}
    />
  );
};

export default ResourceListing;
