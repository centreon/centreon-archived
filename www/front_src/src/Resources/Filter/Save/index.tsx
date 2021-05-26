import * as React from 'react';

import { equals, or, and, not, isEmpty } from 'ramda';

import {
  Menu,
  MenuItem,
  CircularProgress,
  makeStyles,
} from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';

import { IconButton, useRequest, useSnackbar, Severity } from '@centreon/ui';

import {
  labelSaveFilter,
  labelSaveAsNew,
  labelSave,
  labelFilterCreated,
  labelFilterSaved,
  labelEditFilters,
} from '../../translatedLabels';
import { isCustom, Filter } from '../models';
import { useResourceContext } from '../../Context';
import CreateFilterDialog from './CreateFilterDialog';
import { updateFilter as updateFilterRequest } from '../api';

const useStyles = makeStyles((theme) => ({
  save: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
  },
}));

const SaveFilterMenu = (): JSX.Element => {
  const classes = useStyles();

  const [menuAnchor, setMenuAnchor] = React.useState<Element | null>(null);
  const [createFilterDialogOpen, setCreateFilterDialogOpen] =
    React.useState(false);

  const {
    sendRequest: sendUpdateFilterRequest,
    sending: sendingUpdateFilterRequest,
  } = useRequest({
    request: updateFilterRequest,
  });

  const { showMessage } = useSnackbar();

  const {
    filter,
    updatedFilter,
    setFilter,
    setHostGroups,
    setServiceGroups,
    loadCustomFilters,
    customFilters,
    setEditPanelOpen,
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

  const loadFiltersAndUpdateCurrent = (newFilter: Filter): void => {
    closeCreateFilterDialog();

    loadCustomFilters().then(() => {
      setFilter(newFilter);

      // update criterias with deletable objects
      setHostGroups(newFilter.criterias.hostGroups);
      setServiceGroups(newFilter.criterias.serviceGroups);
    });
  };

  const confirmCreateFilter = (newFilter: Filter): void => {
    showMessage({
      message: labelFilterCreated,
      severity: Severity.success,
    });

    loadFiltersAndUpdateCurrent(newFilter);
  };

  const updateFilter = (): void => {
    sendUpdateFilterRequest(updatedFilter).then((savedFilter) => {
      closeSaveFilterMenu();
      showMessage({
        message: labelFilterSaved,
        severity: Severity.success,
      });

      loadFiltersAndUpdateCurrent(savedFilter);
    });
  };

  const openEditPanel = (): void => {
    setEditPanelOpen(true);
    closeSaveFilterMenu();
  };

  const isFilterDirty = (): boolean => {
    if (!isCustom(filter)) {
      return false;
    }

    return !equals(filter, updatedFilter);
  };

  const isNewFilter = filter.id === '';
  const canSaveFilter = and(isFilterDirty(), not(isNewFilter));
  const canSaveFilterAsNew = or(isFilterDirty(), isNewFilter);

  return (
    <>
      <IconButton title={labelSaveFilter} onClick={openSaveFilterMenu}>
        <SettingsIcon />
      </IconButton>
      <Menu
        keepMounted
        anchorEl={menuAnchor}
        open={Boolean(menuAnchor)}
        onClose={closeSaveFilterMenu}
      >
        <MenuItem
          disabled={!canSaveFilterAsNew}
          onClick={openCreateFilterDialog}
        >
          {labelSaveAsNew}
        </MenuItem>
        <MenuItem disabled={!canSaveFilter} onClick={updateFilter}>
          <div className={classes.save}>
            <span>{labelSave}</span>
            {sendingUpdateFilterRequest && <CircularProgress size={15} />}
          </div>
        </MenuItem>
        <MenuItem disabled={isEmpty(customFilters)} onClick={openEditPanel}>
          {labelEditFilters}
        </MenuItem>
      </Menu>
      {createFilterDialogOpen && (
        <CreateFilterDialog
          open
          filter={updatedFilter}
          onCancel={closeCreateFilterDialog}
          onCreate={confirmCreateFilter}
        />
      )}
    </>
  );
};

export default SaveFilterMenu;
