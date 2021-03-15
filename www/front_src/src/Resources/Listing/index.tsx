import * as React from 'react';

import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';

import { useTheme, fade } from '@material-ui/core';

import { MemoizedListing as Listing } from '@centreon/ui';

import { graphTabId } from '../Details/tabs';
import { rowColorConditions } from '../colors';
import {
  labelRowsPerPage,
  labelOf,
  labelNoResultsFound,
} from '../translatedLabels';
import { useResourceContext } from '../Context';
import Actions from '../Actions';
import { Resource, SortOrder } from '../models';

import { getColumns } from './columns';
import useLoadResources from './useLoadResources';

const ResourceListing = (): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();

  const {
    listing,
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
    setCriteria,
    getCriteriaValue,
  } = useResourceContext();

  const { initAutorefreshAndLoad } = useLoadResources();

  const changeSort = ({ order, orderBy }): void => {
    setCriteria({ name: 'sort', value: [orderBy, order] });
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
    name: 'detailsOpen',
    condition: ({ uuid }): boolean => equals(uuid, selectedResourceUuid),
    color: fade(theme.palette.primary.main, 0.08),
  };

  const labelDisplayedRows = ({ from, to, count }): string =>
    `${from}-${to} ${t(labelOf)} ${count}`;

  const columns = getColumns({
    actions: {
      onAcknowledge: (resource): void => {
        setResourcesToAcknowledge([resource]);
      },
      onDowntime: (resource): void => {
        setResourcesToSetDowntime([resource]);
      },
      onCheck: (resource): void => {
        setResourcesToCheck([resource]);
      },
      onDisplayGraph: (resource): void => {
        setOpenDetailsTabId(graphTabId);

        selectResource(resource);
      },
    },
    t,
  });

  const loading = sending;

  const [sortField, sortOrder] = getCriteriaValue('sort') as [
    string,
    SortOrder,
  ];

  const getId = ({ uuid }) => uuid;

  return (
    <Listing
      checkable
      Actions={<Actions onRefresh={initAutorefreshAndLoad} />}
      loading={loading}
      columnConfiguration={columns}
      tableData={listing?.result}
      currentPage={(page || 1) - 1}
      rowColorConditions={[
        ...rowColorConditions(theme),
        resourceDetailsOpenCondition,
      ]}
      limit={listing?.meta.limit}
      onSort={changeSort}
      onPaginationLimitChanged={changeLimit}
      onPaginate={changePage}
      sortf={sortField}
      sorto={sortOrder}
      labelRowsPerPage={t(labelRowsPerPage)}
      labelDisplayedRows={labelDisplayedRows}
      totalRows={listing?.meta.total}
      onSelectRows={setSelectedResources}
      selectedRows={selectedResources}
      onRowClick={selectResource}
      innerScrollDisabled={false}
      emptyDataMessage={t(labelNoResultsFound)}
      getId={getId}
      memoProps={[
        listing,
        sortField,
        sortOrder,
        page,
        selectedResources,
        selectedResourceUuid,
        sending,
      ]}
    />
  );
};

export default ResourceListing;
