/* eslint-disable react/jsx-wrap-multilines */
import * as React from 'react';

import {
  Grid,
  Typography,
  Button,
  makeStyles,
  ExpansionPanel,
  ExpansionPanelSummary as MuiExpansionPanelSummary,
  ExpansionPanelDetails as MuiExpansionPanelDetails,
  withStyles,
  Menu,
  MenuItem,
} from '@material-ui/core';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import SettingsIcon from '@material-ui/icons/Settings';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectField,
  SearchField,
  SelectEntry,
  IconButton,
} from '@centreon/ui';

import {
  labelFilter,
  labelCriterias,
  labelStateFilter,
  labelResourceName,
  labelSearch,
  labelTypeOfResource,
  labelState,
  labelStatus,
  labelHostGroup,
  labelServiceGroup,
  labelClearAll,
  labelOpen,
  labelShowCriteriasFilters,
  labelSaveFilter,
  labelSaveAsNew,
  labelSave,
  labelNewFilter,
} from '../translatedLabels';
import {
  unhandledProblemsFilter,
  resourceProblemsFilter,
  allFilter,
  states as availableStates,
  resourceTypes as availableResourceTypes,
  statuses as availableStatuses,
  FilterGroup,
  filterById,
  isCustom,
} from './models';
import SearchHelpTooltip from './SearchHelpTooltip';
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from '../api/endpoint';
import { useResourceContext } from '../Context';

const ExpansionPanelSummary = withStyles((theme) => ({
  root: {
    padding: theme.spacing(0, 3, 0, 2),
    minHeight: 'auto',
    '&$expanded': {
      minHeight: 'auto',
    },
    '&$focused': {
      backgroundColor: 'unset',
    },
    justifyContent: 'flex-start',
  },
  content: {
    margin: theme.spacing(1, 0),
    '&$expanded': {
      margin: theme.spacing(1, 0),
    },
    flexGrow: 0,
  },
  focused: {},
  expanded: {},
}))(MuiExpansionPanelSummary);

const ExpansionPanelDetails = withStyles((theme) => ({
  root: {
    padding: theme.spacing(0, 0.5, 1, 2),
  },
}))(MuiExpansionPanelDetails);

const useStyles = makeStyles((theme) => ({
  filterBox: {
    padding: theme.spacing(),
    backgroundColor: theme.palette.common.white,
  },
  filterLineLabel: {
    width: 60,
    textAlign: 'center',
  },
  filterGroup: {
    minWidth: 200,
  },
  searchField: {
    width: 500,
  },
  autocompleteField: {
    minWidth: 200,
    maxWidth: 400,
  },
}));

const Filter = (): JSX.Element => {
  const classes = useStyles();

  const [expanded, setExpanded] = React.useState(false);
  const [anchorEl, setAnchorEl] = React.useState<Element | null>(null);

  const {
    filter,
    setFilter,
    setCurrentSearch,
    nextSearch,
    setNextSearch,
    resourceTypes,
    setResourceTypes,
    states,
    setStates,
    statuses,
    setStatuses,
    hostGroups,
    setHostGroups,
    serviceGroups,
    setServiceGroups,
    customFilters,
  } = useResourceContext();

  const toggleExpanded = (): void => {
    setExpanded(!expanded);
  };

  const getHostGroupSearchEndpoint = (searchValue): string => {
    return buildHostGroupsEndpoint({
      limit: 10,
      search: searchValue ? `name:${searchValue}` : undefined,
    });
  };

  const getServiceGroupSearchEndpoint = (searchValue): string => {
    return buildServiceGroupsEndpoint({
      limit: 10,
      search: searchValue ? `name:${searchValue}` : undefined,
    });
  };

  const getOptionsFromResult = ({ result }): Array<SelectEntry> => result;

  const setNewFilter = (): void => {
    if (isCustom(filter)) {
      return;
    }
    setFilter({ id: '', name: labelNewFilter } as FilterGroup);
  };

  const requestSearch = (): void => {
    setCurrentSearch(nextSearch);
  };

  const requestSearchOnEnterKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.keyCode === 13;

    if (enterKeyPressed) {
      requestSearch();
    }
  };

  const prepareSearch = (event): void => {
    setNextSearch(event.target.value);
    setNewFilter();
  };

  const changeFilterGroup = (event): void => {
    const filterId = event.target.value;

    const updatedFilter =
      filterById[filterId] || customFilters?.find(({ id }) => id === filterId);

    setFilter(updatedFilter);

    if (!updatedFilter.criterias) {
      return;
    }

    setResourceTypes(updatedFilter.criterias.resourceTypes);
    setStatuses(updatedFilter.criterias.statuses);
    setStates(updatedFilter.criterias.states);
    setHostGroups(updatedFilter.criterias.hostGroups);
    setServiceGroups(updatedFilter.criterias.serviceGroups);
  };

  const clearAllFilters = (): void => {
    setFilter(allFilter);
    setResourceTypes(allFilter.criterias.resourceTypes);
    setStatuses(allFilter.criterias.statuses);
    setStates(allFilter.criterias.states);
    setHostGroups(allFilter.criterias.hostGroups);
    setServiceGroups(allFilter.criterias.serviceGroups);
    setNextSearch('');
    setCurrentSearch('');
  };

  const changeResourceTypes = (_, updatedResourceTypes): void => {
    setResourceTypes(updatedResourceTypes);
    setNewFilter();
  };

  const changeStates = (_, updatedStates): void => {
    setStates(updatedStates);
    setNewFilter();
  };

  const changeStatuses = (_, updatedStatuses): void => {
    setStatuses(updatedStatuses);
    setNewFilter();
  };

  const changeHostGroups = (_, updatedHostGroups): void => {
    setHostGroups(updatedHostGroups);
  };

  const changeServiceGroups = (_, updatedServiceGroups): void => {
    setServiceGroups(updatedServiceGroups);
  };

  const openSaveFilterMenu = (event: React.MouseEvent): void => {
    setAnchorEl(event.currentTarget);
  };

  const closeSaveFilterMenu = (): void => {
    setAnchorEl(null);
  };

  return (
    <ExpansionPanel square expanded={expanded}>
      <ExpansionPanelSummary
        expandIcon={
          <ExpandMoreIcon
            color="primary"
            aria-label={labelShowCriteriasFilters}
          />
        }
        IconButtonProps={{ onClick: toggleExpanded }}
        style={{ cursor: 'default' }}
      >
        <Grid spacing={1} container alignItems="center">
          <Grid item>
            <Typography className={classes.filterLineLabel} variant="h6">
              {labelFilter}
            </Typography>
          </Grid>
          <Grid item>
            <IconButton title={labelSaveFilter} onClick={openSaveFilterMenu}>
              <SettingsIcon />
            </IconButton>
            <Menu
              anchorEl={anchorEl}
              keepMounted
              open={Boolean(anchorEl)}
              onClose={closeSaveFilterMenu}
            >
              <MenuItem onClick={closeSaveFilterMenu}>
                {labelSaveAsNew}
              </MenuItem>
              <MenuItem onClick={closeSaveFilterMenu}>{labelSave}</MenuItem>
            </Menu>
          </Grid>
          <Grid item>
            <SelectField
              className={classes.filterGroup}
              options={[
                { id: '', name: labelNewFilter },
                unhandledProblemsFilter,
                resourceProblemsFilter,
                allFilter,
                ...(customFilters as Array<FilterGroup>),
              ]}
              selectedOptionId={filter.id}
              onChange={changeFilterGroup}
              aria-label={labelStateFilter}
            />
          </Grid>
          <Grid item>
            <SearchField
              className={classes.searchField}
              EndAdornment={SearchHelpTooltip}
              value={nextSearch || ''}
              onChange={prepareSearch}
              placeholder={labelResourceName}
              onKeyDown={requestSearchOnEnterKey}
            />
          </Grid>
          <Grid item>
            <Button variant="contained" color="primary" onClick={requestSearch}>
              {labelSearch}
            </Button>
          </Grid>
        </Grid>
      </ExpansionPanelSummary>
      <ExpansionPanelDetails>
        <Grid spacing={1} container alignItems="center">
          <Grid item>
            <Typography className={classes.filterLineLabel} variant="subtitle1">
              {labelCriterias}
            </Typography>
          </Grid>
          <Grid item>
            <MultiAutocompleteField
              className={classes.autocompleteField}
              options={availableResourceTypes}
              label={labelTypeOfResource}
              onChange={changeResourceTypes}
              value={resourceTypes || []}
              openText={`${labelOpen} ${labelTypeOfResource}`}
            />
          </Grid>
          <Grid item>
            <MultiAutocompleteField
              className={classes.autocompleteField}
              options={availableStates}
              label={labelState}
              onChange={changeStates}
              value={states || []}
              openText={`${labelOpen} ${labelState}`}
            />
          </Grid>
          <Grid item>
            <MultiAutocompleteField
              className={classes.autocompleteField}
              options={availableStatuses}
              label={labelStatus}
              onChange={changeStatuses}
              value={statuses || []}
              openText={`${labelOpen} ${labelStatus}`}
            />
          </Grid>
          <Grid item>
            <MultiConnectedAutocompleteField
              className={classes.autocompleteField}
              baseEndpoint={buildHostGroupsEndpoint({ limit: 10 })}
              getSearchEndpoint={getHostGroupSearchEndpoint}
              getOptionsFromResult={getOptionsFromResult}
              label={labelHostGroup}
              onChange={changeHostGroups}
              value={hostGroups || []}
              openText={`${labelOpen} ${labelHostGroup}`}
            />
          </Grid>
          <Grid item>
            <MultiConnectedAutocompleteField
              className={classes.autocompleteField}
              baseEndpoint={buildServiceGroupsEndpoint({ limit: 10 })}
              getSearchEndpoint={getServiceGroupSearchEndpoint}
              label={labelServiceGroup}
              onChange={changeServiceGroups}
              getOptionsFromResult={getOptionsFromResult}
              value={serviceGroups || []}
              openText={`${labelOpen} ${labelServiceGroup}`}
            />
          </Grid>
          <Grid item>
            <Button color="primary" onClick={clearAllFilters}>
              {labelClearAll}
            </Button>
          </Grid>
        </Grid>
      </ExpansionPanelDetails>
    </ExpansionPanel>
  );
};

export default Filter;
