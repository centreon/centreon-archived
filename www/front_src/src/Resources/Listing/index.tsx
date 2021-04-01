import * as React from 'react';

import { equals } from 'ramda';
import { useTranslation } from 'react-i18next';

import { useTheme, fade } from '@material-ui/core';

import { MemoizedListing as Listing } from '@centreon/ui';

import { graphTabId } from '../Details/tabs';
import { rowColorConditions } from '../colors';
import { useResourceContext } from '../Context';
import Actions from '../Actions';
import { Resource, SortOrder } from '../models';

import { getColumns, defaultSelectedColumnIds } from './columns';
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
    selectedColumnIds,
    setSelectedColumnIds,
  } = useResourceContext();

  const { initAutorefreshAndLoad } = useLoadResources();

  const changeSort = ({ sortField, sortOrder }): void => {
    setCriteria({ name: 'sort', value: [sortField, sortOrder] });
  };

  const changeLimit = (value): void => {
    setLimit(Number(value));
  };

  const changePage = (updatedPage): void => {
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

  const resetColumns = (): void => {
    setSelectedColumnIds(defaultSelectedColumnIds);
  };

  return (
    <Listing
      checkable
      actions={<Actions onRefresh={initAutorefreshAndLoad} />}
      loading={loading}
      columns={columns}
      onSelectColumns={setSelectedColumnIds}
      columnConfiguration={{
        sortable: true,
        selectedColumnIds,
      }}
      onResetColumns={resetColumns}
      rows={listing?.result}
      currentPage={(page || 1) - 1}
      rowColorConditions={[
        ...rowColorConditions(theme),
        resourceDetailsOpenCondition,
      ]}
      limit={listing?.meta.limit}
      onSort={changeSort}
      onLimitChange={changeLimit}
      onPaginate={changePage}
      sortField={sortField}
      sortOrder={sortOrder}
      totalRows={listing?.meta.total}
      onSelectRows={setSelectedResources}
      selectedRows={selectedResources}
      onRowClick={selectResource}
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
