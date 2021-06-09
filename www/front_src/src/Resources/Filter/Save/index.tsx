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
import { useResourceContext } from '../../Context';
import { updateFilter as updateFilterRequest } from '../api';
import { FilterState } from '../useFilter';
import memoizeComponent from '../../memoizedComponent';
import { Filter } from '../models';

import CreateFilterDialog from './CreateFilterDialog';

const useStyles = makeStyles((theme) => ({
  save: {
    alignItems: 'center',
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(2),
  },
}));

type Props = Pick<
  FilterState,
  | 'filter'
  | 'updatedFilter'
  | 'setFilter'
  | 'loadCustomFilters'
  | 'customFilters'
  | 'setEditPanelOpen'
  | 'filters'
>;

const SaveFilterMenuContent = ({
  filter,
  updatedFilter,
  setFilter,
  loadCustomFilters,
  customFilters,
  setEditPanelOpen,
  filters,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

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
    });
  };

  const confirmCreateFilter = (newFilter: Filter): void => {
    showMessage({
      message: t(labelFilterCreated),
      severity: Severity.success,
    });

    loadFiltersAndUpdateCurrent(omit(['order'], newFilter));
  };

  const updateFilter = (): void => {
    sendUpdateFilterRequest({
      filter: omit(['id'], updatedFilter),
      id: updatedFilter.id,
    }).then((savedFilter) => {
      closeSaveFilterMenu();
      showMessage({
        message: t(labelFilterSaved),
        severity: Severity.success,
      });

      loadFiltersAndUpdateCurrent(omit(['order'], savedFilter));
    });
  };

  const openEditPanel = (): void => {
    setEditPanelOpen(true);
    closeSaveFilterMenu();
  };

  const isFilterDirty = (): boolean => {
    const retrievedFilter = find(propEq('id', filter.id), filters);

    return !equals(retrievedFilter, updatedFilter);
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
          filter={updatedFilter}
          onCancel={closeCreateFilterDialog}
          onCreate={confirmCreateFilter}
        />
      )}
    </>
  );
};

const memoProps = ['filter', 'updatedFilter', 'customFilters', 'filters'];

const MemoizedSaveFilterMenuContent = memoizeComponent<Props>({
  Component: SaveFilterMenuContent,
  memoProps,
});

const SaveFilterMenu = (): JSX.Element => {
  const {
    filter,
    updatedFilter,
    setFilter,
    loadCustomFilters,
    customFilters,
    setEditPanelOpen,
    filters,
  } = useResourceContext();

  return (
    <MemoizedSaveFilterMenuContent
      customFilters={customFilters}
      filter={filter}
      filters={filters}
      loadCustomFilters={loadCustomFilters}
      setEditPanelOpen={setEditPanelOpen}
      setFilter={setFilter}
      updatedFilter={updatedFilter}
    />
  );
};

export default SaveFilterMenu;
