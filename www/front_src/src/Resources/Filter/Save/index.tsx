import * as React from 'react';

import { equals, or, and, not, isEmpty, omit, find, propEq } from 'ramda';
import { useTranslation } from 'react-i18next';

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
import { Filter } from '../models';
import { useResourceContext } from '../../Context';
import { updateFilter as updateFilterRequest } from '../api';
import useAdapters from '../api/adapters';

import CreateFilterDialog from './CreateFilterDialog';

const useStyles = makeStyles((theme) => ({
  save: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
    alignItems: 'center',
  },
}));

const SaveFilterMenu = (): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();
  const { toRawFilter, toFilter } = useAdapters();

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
    filters,
    updatedFilter,
    setFilter,
    setHostGroups,
    setServiceGroups,
    loadCustomFilters,
    customFilters,
    setEditPanelOpen,
    sorto,
    sortf,
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
      message: t(labelFilterCreated),
      severity: Severity.success,
    });

    loadFiltersAndUpdateCurrent(newFilter);
  };

  const updatedFilterWithSort = {
    ...updatedFilter,
    sort: [sortf, sorto],
  } as Filter;

  const updateFilter = (): void => {
    sendUpdateFilterRequest({
      id: updatedFilter.id,
      rawFilter: omit(['id'], toRawFilter(updatedFilterWithSort)),
    }).then((savedFilter) => {
      closeSaveFilterMenu();
      showMessage({
        message: t(labelFilterSaved),
        severity: Severity.success,
      });

      loadFiltersAndUpdateCurrent(toFilter(savedFilter));
    });
  };

  const openEditPanel = (): void => {
    setEditPanelOpen(true);
    closeSaveFilterMenu();
  };

  const isFilterDirty = (): boolean => {
    const retrievedFilter = find(propEq('id', filter.id), filters);

    return !equals(retrievedFilter, updatedFilterWithSort);
  };

  const isNewFilter = filter.id === '';
  const canSaveFilter = and(isFilterDirty(), not(isNewFilter));
  const canSaveFilterAsNew = or(isFilterDirty(), isNewFilter);

  return (
    <>
      <IconButton title={t(labelSaveFilter)} onClick={openSaveFilterMenu}>
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
          {t(labelSaveAsNew)}
        </MenuItem>
        <MenuItem disabled={!canSaveFilter} onClick={updateFilter}>
          <div className={classes.save}>
            <span>{t(labelSave)}</span>
            {sendingUpdateFilterRequest && <CircularProgress size={15} />}
          </div>
        </MenuItem>
        <MenuItem onClick={openEditPanel} disabled={isEmpty(customFilters)}>
          {t(labelEditFilters)}
        </MenuItem>
      </Menu>
      {createFilterDialogOpen && (
        <CreateFilterDialog
          open
          onCreate={confirmCreateFilter}
          filter={updatedFilterWithSort}
          onCancel={closeCreateFilterDialog}
        />
      )}
    </>
  );
};

export default SaveFilterMenu;
