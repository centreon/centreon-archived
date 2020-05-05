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
} from '@material-ui/core';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectField,
  SearchField,
  SelectEntry,
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
} from './models';
import SearchHelpTooltip from '../SearchHelpTooltip';
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
    justifyContent: 'flex-start',
  },
  content: {
    margin: theme.spacing(1, 0),
    '&$expanded': {
      margin: theme.spacing(1, 0),
    },
    flexGrow: 0,
  },
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
  collapseWrapper: {
    margin: 0,
    padding: theme.spacing(1),
    '&$expanded': {
      margin: 0,
      padding: theme.spacing(1),
    },
  },
  expanded: {},
}));

const Filter = (): JSX.Element => {
  const classes = useStyles();

  const [expanded, setExpanded] = React.useState(false);

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

  const requestSearch = (): void => {
    setCurrentSearch(nextSearch);
  };

  const requestSearchOnEnterKey = (event: KeyboardEvent): void => {
    const enterKeyPressed = event.keyCode === 13;

    if (enterKeyPressed) {
      requestSearch();
    }
  };

  const prepareSearch = (event): void => {
    setNextSearch(event.target.value);
  };

  const changeFilterGroup = (event): void => {
    const filterId = event.target.value;

    const updatedFilter = filterById[filterId];
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

  const setEmptyFilter = (): void => {
    setFilter({ id: '', name: '' } as FilterGroup);
  };

  const changeResourceTypes = (_, updatedResourceTypes): void => {
    setResourceTypes(updatedResourceTypes);
    setEmptyFilter();
  };

  const changeStates = (_, updatedStates): void => {
    setStates(updatedStates);
    setEmptyFilter();
  };

  const changeStatuses = (_, updatedStatuses): void => {
    setStatuses(updatedStatuses);
    setEmptyFilter();
  };

  const changeHostGroups = (_, updatedHostGroups): void => {
    setHostGroups(updatedHostGroups);
  };

  const changeServiceGroups = (_, updatedServiceGroups): void => {
    setServiceGroups(updatedServiceGroups);
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
            <SelectField
              className={classes.filterGroup}
              options={[
                unhandledProblemsFilter,
                resourceProblemsFilter,
                allFilter,
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
