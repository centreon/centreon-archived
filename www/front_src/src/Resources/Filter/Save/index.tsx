import * as React from 'react';

import { equals, any, or, and, propEq, find } from 'ramda';

import { Menu, MenuItem, CircularProgress } from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';

import { IconButton, useRequest, useSnackbar, Severity } from '@centreon/ui';

import {
  labelSaveFilter,
  labelSaveAsNew,
  labelSave,
  labelFilterCreated,
  labelFilterSaved,
} from '../../translatedLabels';
import { isCustom, Filter } from '../models';
import { useResourceContext } from '../../Context';
import CreateFilterDialog from './CreateFilterDialog';
import { updateFilter as updateFilterRequest } from '../api';

const SaveFilterMenu = (): JSX.Element => {
  const [menuAnchor, setMenuAnchor] = React.useState<Element | null>(null);
  const [createFilterDialogOpen, setCreateFilterDialogOpen] = React.useState(
    false,
  );

  const {
    sendRequest: sendUpdateFilterRequest,
    sending: sendingUpdateFilterRequest,
  } = useRequest({
    request: updateFilterRequest,
  });

  const { showMessage } = useSnackbar();

  const {
    filter,
    setFilter,
    nextSearch,
    resourceTypes,
    states,
    statuses,
    hostGroups,
    serviceGroups,
    customFilters,
    loadCustomFilters,
  } = useResourceContext();

  const openSaveFilterMenu = (event: React.MouseEvent): void => {
    setMenuAnchor(event.currentTarget);
  };

  const closeSaveFilterMenu = (): void => {
    setMenuAnchor(null);
  };

  const openCreateFilterDialog = (): void => {
    closeSaveFilterMenu();
    setCreateFilterDialogOpen(true);
  };

  const closeCreateFilterDialog = (): void => {
    setCreateFilterDialogOpen(false);
  };

  const createFilter = (id: number | string): void => {
    loadCustomFilters()
      .then((filters) => {
        const updatedFilter = find<Filter>(propEq('id', id), filters);
        setFilter(updatedFilter as Filter);
      })
      .then(() => {
        showMessage({
          message: labelFilterCreated,
          severity: Severity.success,
        });
      });
  };

  const updateFilter = (): void => {
    sendUpdateFilterRequest(filter).then(() => {
      showMessage({
        message: labelFilterSaved,
        severity: Severity.success,
      });
    });
  };

  const isFilterDirty = (): boolean => {
    if (!isCustom(filter)) {
      return false;
    }

    const currentCustomFilter = customFilters?.find(
      ({ id }) => id === filter.id,
    );

    const currentCriterias = currentCustomFilter?.criterias;

    return any(([a, b]) => !equals(a, b), [
      [resourceTypes, currentCriterias?.resourceTypes],
      [states, currentCriterias?.states],
      [statuses, currentCriterias?.statuses],
      [nextSearch, currentCriterias?.search],
      [serviceGroups, currentCriterias?.serviceGroups],
      [hostGroups, currentCriterias?.hostGroups],
    ]);
  };

  const isNewFilter = filter.id === '';
  const canSaveFilter = and(isFilterDirty(), !isNewFilter);
  const canSaveFilterAsNew = or(isFilterDirty(), isNewFilter);

  return (
    <>
      <IconButton title={labelSaveFilter} onClick={openSaveFilterMenu}>
        <SettingsIcon />
      </IconButton>
      <Menu
        anchorEl={menuAnchor}
        keepMounted
        open={Boolean(menuAnchor)}
        onClose={closeSaveFilterMenu}
      >
        <MenuItem
          onClick={openCreateFilterDialog}
          disabled={!canSaveFilterAsNew}
        >
          {labelSaveAsNew}
        </MenuItem>
        <MenuItem disabled={!canSaveFilter} onClick={updateFilter}>
          <div>
            {labelSave}
            {sendingUpdateFilterRequest && <CircularProgress size={3} />}
          </div>
        </MenuItem>
      </Menu>
      <CreateFilterDialog
        open={createFilterDialogOpen}
        onConfirm={createFilter}
        filter={filter}
        onCancel={closeCreateFilterDialog}
      />
    </>
  );
};

export default SaveFilterMenu;
