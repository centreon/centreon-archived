import { equals, includes, not, isNil, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';

import { useTheme, alpha } from '@mui/material';

import { MemoizedListing as Listing, useSnackbar } from '@centreon/ui';

import { graphTabId } from '../Details/tabs';
import { rowColorConditions } from '../colors';
import Actions from '../Actions';
import { Resource, SortOrder } from '../models';
import { labelSelectAtLeastOneColumn, labelStatus } from '../translatedLabels';
import {
  openDetailsTabIdAtom,
  selectedResourceUuidAtom,
  selectedResourcesDetailsAtom,
} from '../Details/detailsAtoms';
import {
  resourcesToAcknowledgeAtom,
  resourcesToCheckAtom,
  resourcesToSetDowntimeAtom,
  selectedResourcesAtom,
} from '../Actions/actionsAtoms';
import {
  getCriteriaValueDerivedAtom,
  searchAtom,
  setCriteriaAndNewFilterDerivedAtom,
} from '../Filter/filterAtoms';

import { getColumns, defaultSelectedColumnIds } from './columns';
import useLoadResources from './useLoadResources';
import {
  enabledAutorefreshAtom,
  limitAtom,
  listingAtom,
  pageAtom,
  selectedColumnIdsAtom,
  sendingAtom,
} from './listingAtoms';

export const okStatuses = ['OK', 'UP'];

const ResourceListing = (): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();
  const { showWarningMessage } = useSnackbar();

  const [selectedResourceUuid, setSelectedResourceUuid] = useAtom(
    selectedResourceUuidAtom,
  );
  const [page, setPage] = useAtom(pageAtom);
  const [selectedColumnIds, setSelectedColumnIds] = useAtom(
    selectedColumnIdsAtom,
  );
  const [selectedResources, setSelectedResources] = useAtom(
    selectedResourcesAtom,
  );
  const [selectedResourceDetails, setSelectedResourceDetails] = useAtom(
    selectedResourcesDetailsAtom,
  );
  const listing = useAtomValue(listingAtom);
  const sending = useAtomValue(sendingAtom);
  const enabledAutoRefresh = useAtomValue(enabledAutorefreshAtom);
  const getCriteriaValue = useAtomValue(getCriteriaValueDerivedAtom);
  const search = useAtomValue(searchAtom);

  const setOpenDetailsTabId = useUpdateAtom(openDetailsTabIdAtom);
  const setLimit = useUpdateAtom(limitAtom);
  const setResourcesToAcknowledge = useUpdateAtom(resourcesToAcknowledgeAtom);
  const setResourcesToSetDowntime = useUpdateAtom(resourcesToSetDowntimeAtom);
  const setResourcesToCheck = useUpdateAtom(resourcesToCheckAtom);
  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const { initAutorefreshAndLoad } = useLoadResources();

  const changeSort = ({ sortField, sortOrder }): void => {
    setCriteriaAndNewFilter({
      apply: true,
      name: 'sort',
      value: [sortField, sortOrder],
    });
  };

  const changeLimit = (value): void => {
    setLimit(Number(value));
  };

  const changePage = (updatedPage): void => {
    setPage(updatedPage + 1);
  };

  const selectResource = ({ id, links, uuid }: Resource): void => {
    setSelectedResourceUuid(uuid);
    setSelectedResourceDetails({
      resourceId: id,
      resourcesDetailsEndpoint: links?.endpoints?.details,
    });
  };

  const resourceDetailsOpenCondition = {
    color: alpha(theme.palette.primary.main, 0.12),
    condition: ({ id }): boolean => {
      if (isEmpty(selectedResourceDetails) || isNil(selectedResourceDetails)) {
        return false;
      }

      const { parentResourceId } = selectedResourceDetails;

      return parentResourceId
        ? equals(id, parentResourceId)
        : equals(id, selectedResourceDetails?.resourceId);
    },
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

  const getId = ({ uuid }: Resource): string => uuid;

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
      rowCondition: ({ status }): boolean => includes(status.name, okStatuses),
    },
    {
      label: `${t(labelStatus).toLowerCase()}:NOK`,
      rowCondition: ({ status }): boolean =>
        not(includes(status.name, okStatuses)),
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
      headerMemoProps={[search]}
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
        enabledAutoRefresh,
        selectedResourceDetails,
      ]}
      predefinedRowsSelection={predefinedRowsSelection}
      rowColorConditions={[
        resourceDetailsOpenCondition,
        ...rowColorConditions(theme),
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
