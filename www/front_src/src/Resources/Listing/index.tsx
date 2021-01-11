import * as React from 'react';

import { equals, isNil } from 'ramda';
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
    filter,
    setNewFilter,
  } = useResourceContext();

  const { initAutorefreshAndLoad } = useLoadResources();

  React.useEffect(() => {
    if (isNil(filter)) {
      return;
    }

    const [filterSortf, filterSorto] = filter.sort;

    setSortf(filterSortf);
    setSorto(filterSorto);
  }, [filter]);

  const changeSort = ({ order, orderBy }): void => {
    setSortf(orderBy);
    setSorto(order);
    setNewFilter([orderBy, order]);
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
      sortf={sortf}
      sorto={sorto}
      labelRowsPerPage={t(labelRowsPerPage)}
      labelDisplayedRows={labelDisplayedRows}
      totalRows={listing?.meta.total}
      onSelectRows={setSelectedResources}
      selectedRows={selectedResources}
      onRowClick={selectResource}
      innerScrollDisabled={false}
      emptyDataMessage={t(labelNoResultsFound)}
    />
  );
};

export default ResourceListing;
