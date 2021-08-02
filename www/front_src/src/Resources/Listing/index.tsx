import * as React from 'react';

import { equals, includes, not } from 'ramda';
import { useTranslation } from 'react-i18next';

import { useTheme, fade } from '@material-ui/core';

import { MemoizedListing as Listing, useSnackbar } from '@centreon/ui';

import { graphTabId } from '../Details/tabs';
import { rowColorConditions } from '../colors';
import { useResourceContext } from '../Context';
import Actions from '../Actions';
import { Resource, SortOrder } from '../models';
import { labelSelectAtLeastOneColumn, labelStatus } from '../translatedLabels';

import { getColumns, defaultSelectedColumnIds } from './columns';
import useLoadResources from './useLoadResources';

export const okStatuses = ['OK', 'UP'];

const ResourceListing = (): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();
  const { showWarningMessage } = useSnackbar();

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
    setCriteriaAndNewFilter,
    getCriteriaValue,
    selectedColumnIds,
    setSelectedColumnIds,
  } = useResourceContext();

  const { initAutorefreshAndLoad } = useLoadResources();

  const changeSort = ({ sortField, sortOrder }): void => {
    setCriteriaAndNewFilter({ name: 'sort', value: [sortField, sortOrder] });
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
    color: fade(theme.palette.primary.main, 0.08),
    condition: ({ uuid }): boolean => equals(uuid, selectedResourceUuid),
    name: 'detailsOpen',
  };

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

  const [sortField, sortOrder] = getCriteriaValue('sort') as [
    string,
    SortOrder,
  ];

  const getId = ({ uuid }) => uuid;

  const resetColumns = (): void => {
    setSelectedColumnIds(defaultSelectedColumnIds);
  };

  const selectColumns = (updatedColumnIds: Array<string>): void => {
    if (updatedColumnIds.length === 0) {
      showWarningMessage(t(labelSelectAtLeastOneColumn));

      return;
    }

    setSelectedColumnIds(updatedColumnIds);
  };

  const predefinedRowsSelection = [
    {
      label: `${t(labelStatus).toLowerCase()}:OK`,
      rowCondition: ({ status }) => includes(status.name, okStatuses),
    },
    {
      label: `${t(labelStatus).toLowerCase()}:NOK`,
      rowCondition: ({ status }) => not(includes(status.name, okStatuses)),
    },
  ];

  return (
    <Listing
      checkable
      actions={<Actions onRefresh={initAutorefreshAndLoad} />}
      columnConfiguration={{
        selectedColumnIds,
        sortable: true,
      }}
      columns={columns}
      currentPage={(page || 1) - 1}
      getId={getId}
      limit={listing?.meta.limit}
      loading={loading}
      memoProps={[
        listing,
        sortField,
        sortOrder,
        page,
        selectedResources,
        selectedResourceUuid,
        sending,
      ]}
      predefinedRowsSelection={predefinedRowsSelection}
      rowColorConditions={[
        ...rowColorConditions(theme),
        resourceDetailsOpenCondition,
      ]}
      rows={listing?.result}
      selectedRows={selectedResources}
      sortField={sortField}
      sortOrder={sortOrder}
      totalRows={listing?.meta.total}
      onLimitChange={changeLimit}
      onPaginate={changePage}
      onResetColumns={resetColumns}
      onRowClick={selectResource}
      onSelectColumns={selectColumns}
      onSelectRows={setSelectedResources}
      onSort={changeSort}
    />
  );
};

export default ResourceListing;
