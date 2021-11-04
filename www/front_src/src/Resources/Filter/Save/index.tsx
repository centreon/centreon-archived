import * as React from 'react';

import {
  or,
  and,
  not,
  isEmpty,
  omit,
  find,
  propEq,
  pipe,
  symmetricDifference,
} from 'ramda';
import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import {
  Menu,
  MenuItem,
  CircularProgress,
  makeStyles,
} from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';

import { IconButton, useRequest, useSnackbar } from '@centreon/ui';

import {
  labelSaveFilter,
  labelSaveAsNew,
  labelSave,
  labelFilterCreated,
  labelFilterSaved,
  labelEditFilters,
} from '../../translatedLabels';
import { useResourceContext } from '../../Context';
import { updateFilter as updateFilterRequest } from '../api';
import memoizeComponent from '../../memoizedComponent';
import { Filter } from '../models';
import {
  applyFilterDerivedAtom,
  currentFilterAtom,
  customFiltersAtom,
  editPanelOpenAtom,
  filtersDerivedAtom,
} from '../filterAtoms';

import CreateFilterDialog from './CreateFilterDialog';

const areValuesEqual = pipe(symmetricDifference, isEmpty) as (a, b) => boolean;

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

  const { t } = useTranslation();

  const { loadCustomFilters } = useResourceContext();

  const [menuAnchor, setMenuAnchor] = React.useState<Element | null>(null);
  const [createFilterDialogOpen, setCreateFilterDialogOpen] =
    React.useState(false);

  const {
    sendRequest: sendUpdateFilterRequest,
    sending: sendingUpdateFilterRequest,
  } = useRequest({
    request: updateFilterRequest,
  });

  const currentFilter = useAtomValue(currentFilterAtom);
  const customFilters = useAtomValue(customFiltersAtom);
  const filters = useAtomValue(filtersDerivedAtom);
  const applyFilter = useUpdateAtom(applyFilterDerivedAtom);
  const setEditPanelOpen = useUpdateAtom(editPanelOpenAtom);

  const { showSuccessMessage } = useSnackbar();

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

    loadCustomFilters?.().then(() => {
      applyFilter(newFilter);
    });
  };

  const confirmCreateFilter = (newFilter: Filter): void => {
    showSuccessMessage(t(labelFilterCreated));

    loadFiltersAndUpdateCurrent(omit(['order'], newFilter));
  };

  const updateFilter = (): void => {
    sendUpdateFilterRequest({
      filter: omit(['id'], currentFilter),
      id: currentFilter.id,
    }).then((savedFilter) => {
      closeSaveFilterMenu();
      showSuccessMessage(t(labelFilterSaved));

      loadFiltersAndUpdateCurrent(omit(['order'], savedFilter));
    });
  };

  const openEditPanel = (): void => {
    setEditPanelOpen(true);
    closeSaveFilterMenu();
  };

  const isFilterDirty = (): boolean => {
    const retrievedFilter = find(propEq('id', currentFilter.id), filters);

    return !areValuesEqual(
      currentFilter.criterias,
      retrievedFilter?.criterias || [],
    );
  };

  const isNewFilter = currentFilter.id === '';
  const canSaveFilter = and(isFilterDirty(), not(isNewFilter));
  const canSaveFilterAsNew = or(isFilterDirty(), isNewFilter);

  return (
    <>
      <IconButton title={t(labelSaveFilter)} onClick={openSaveFilterMenu}>
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
          {t(labelSaveAsNew)}
        </MenuItem>
        <MenuItem disabled={!canSaveFilter} onClick={updateFilter}>
          <div className={classes.save}>
            <span>{t(labelSave)}</span>
            {sendingUpdateFilterRequest && <CircularProgress size={15} />}
          </div>
        </MenuItem>
        <MenuItem disabled={isEmpty(customFilters)} onClick={openEditPanel}>
          {t(labelEditFilters)}
        </MenuItem>
      </Menu>
      {createFilterDialogOpen && (
        <CreateFilterDialog
          open
          filter={currentFilter}
          onCancel={closeCreateFilterDialog}
          onCreate={confirmCreateFilter}
        />
      )}
    </>
  );
};

export default SaveFilterMenu;
