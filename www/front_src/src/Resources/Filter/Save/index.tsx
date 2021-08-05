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
import { FilterState } from '../useFilter';
import memoizeComponent from '../../memoizedComponent';
import { Filter } from '../models';

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

type Props = Pick<
  FilterState,
  | 'currentFilter'
  | 'loadCustomFilters'
  | 'customFilters'
  | 'setEditPanelOpen'
  | 'filters'
  | 'appliedFilter'
  | 'search'
  | 'applyFilter'
>;

const SaveFilterMenuContent = ({
  currentFilter,
  applyFilter,
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

    loadCustomFilters().then(() => {
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

const memoProps = [
  'updatedFilter',
  'customFilters',
  'appliedFilter',
  'filters',
  'currentFilter',
  'search',
];

const MemoizedSaveFilterMenuContent = memoizeComponent<Props>({
  Component: SaveFilterMenuContent,
  memoProps,
});

const SaveFilterMenu = (): JSX.Element => {
  const {
    filterWithParsedSearch,
    applyFilter,
    loadCustomFilters,
    customFilters,
    setEditPanelOpen,
    filters,
    appliedFilter,
    search,
  } = useResourceContext();

  return (
    <MemoizedSaveFilterMenuContent
      appliedFilter={appliedFilter}
      applyFilter={applyFilter}
      currentFilter={filterWithParsedSearch}
      customFilters={customFilters}
      filters={filters}
      loadCustomFilters={loadCustomFilters}
      search={search}
      setEditPanelOpen={setEditPanelOpen}
    />
  );
};

export default SaveFilterMenu;
