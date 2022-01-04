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
import { useAtom } from 'jotai';
import { Menu, MenuItem, CircularProgress } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import SettingsIcon from '@mui/icons-material/Settings';

import { IconButton, useRequest, useSnackbar } from '@centreon/ui';

import {
  labelSaveFilter,
  labelSaveAsNew,
  labelSave,
  labelFilterCreated,
  labelFilterSaved,
  labelEditFilters,
} from '../../translatedLabels';
import { listCustomFilters, updateFilter as updateFilterRequest } from '../api';
import { Filter } from '../models';
import {
  applyFilterDerivedAtom,
  currentFilterAtom,
  customFiltersAtom,
  editPanelOpenAtom,
  filtersDerivedAtom,
  sendingFilterAtom,
} from '../filterAtoms';
import { listCustomFiltersDecoder } from '../api/decoders';

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

  const [menuAnchor, setMenuAnchor] = React.useState<Element | null>(null);
  const [createFilterDialogOpen, setCreateFilterDialogOpen] =
    React.useState(false);

  const { sendRequest: sendListCustomFiltersRequest, sending } = useRequest({
    decoder: listCustomFiltersDecoder,
    request: listCustomFilters,
  });

  const {
    sendRequest: sendUpdateFilterRequest,
    sending: sendingUpdateFilterRequest,
  } = useRequest({
    request: updateFilterRequest,
  });

  const [customFilters, setCustomFilters] = useAtom(customFiltersAtom);
  const currentFilter = useAtomValue(currentFilterAtom);
  const filters = useAtomValue(filtersDerivedAtom);
  const applyFilter = useUpdateAtom(applyFilterDerivedAtom);
  const setEditPanelOpen = useUpdateAtom(editPanelOpenAtom);
  const setSendingFilter = useUpdateAtom(sendingFilterAtom);

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

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    return sendListCustomFiltersRequest().then(({ result }) => {
      setCustomFilters(result.map(omit(['order'])));

      return result;
    });
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

  React.useEffect(() => {
    setSendingFilter(sending);
  }, [sending]);

  const isNewFilter = currentFilter.id === '';
  const canSaveFilter = and(isFilterDirty(), not(isNewFilter));
  const canSaveFilterAsNew = or(isFilterDirty(), isNewFilter);

  return (
    <>
      <IconButton
        aria-label={t(labelSaveFilter)}
        size="large"
        title={t(labelSaveFilter)}
        onClick={openSaveFilterMenu}
      >
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
