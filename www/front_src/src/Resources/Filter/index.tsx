/* eslint-disable react/jsx-wrap-multilines */
import React, { KeyboardEvent } from 'react';

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
  AutocompleteField,
  ConnectedAutocompleteField,
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
  states,
  resourceTypes,
  statuses,
  Filter as FilterModel,
  FilterGroup,
} from './models';
import SearchHelpTooltip from '../SearchHelpTooltip';
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from '../api/endpoint';

const ExpansionPanelSummary = withStyles((theme) => ({
  root: {
    padding: theme.spacing(0, 3, 0, 2),
    minHeight: 'auto',
    '&$expanded': {
      minHeight: 'auto',
    },
  },
  content: {
    margin: theme.spacing(1, 0),
    '&$expanded': {
      margin: theme.spacing(1, 0),
    },
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

interface Props {
  filter: FilterGroup;
  onFilterGroupChange: (event) => void;
  currentSearch?: string;
  nextSearch?: string;
  onSearchRequest: () => void;
  onSearchPrepare: (event) => void;
  selectedResourceTypes: Array<FilterModel>;
  onResourceTypesChange: (event, types) => void;
  selectedStates: Array<FilterModel>;
  onStatesChange: (_, states) => void;
  selectedStatuses: Array<FilterModel>;
  onStatusesChange: (_, statuses) => void;
  selectedHostGroups?: Array<FilterModel>;
  onHostGroupsChange: (_, hostGroups) => void;
  selectedServiceGroups?: Array<FilterModel>;
  onServiceGroupsChange: (_, serviceGroups) => void;
  onClearAll: () => void;
}

const Filter = ({
  filter,
  onFilterGroupChange,
  currentSearch,
  nextSearch,
  onSearchRequest,
  onSearchPrepare,
  selectedResourceTypes,
  onResourceTypesChange,
  selectedStates,
  onStatesChange,
  selectedStatuses,
  onStatusesChange,
  selectedHostGroups,
  onHostGroupsChange,
  selectedServiceGroups,
  onServiceGroupsChange,
  onClearAll,
}: Props): JSX.Element => {
  const classes = useStyles();

  const getHostGroupSearchEndpoint = (searchValue): string => {
    return buildHostGroupsEndpoint({
      limit: 10,
      search: `name:${searchValue}`,
    });
  };

  const getServiceGroupSearchEndpoint = (searchValue): string => {
    return buildServiceGroupsEndpoint({
      limit: 10,
      search: `name:${searchValue}`,
    });
  };

  const getOptionsFromResult = ({ result }): Array<SelectEntry> => result;

  const requestSearchOnEnterKey = (event: KeyboardEvent): void => {
    const enterKeyPressed = event.keyCode === 13;

    if (enterKeyPressed) {
      onSearchRequest();
    }
  };

  return (
    <ExpansionPanel square>
      <ExpansionPanelSummary
        expandIcon={
          <ExpandMoreIcon
            color="primary"
            aria-label={labelShowCriteriasFilters}
          />
        }
      >
        <Grid
          spacing={1}
          container
          alignItems="center"
          onClick={(e): void => {
            e.stopPropagation();
          }}
          style={{ cursor: 'default' }}
        >
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
              onChange={onFilterGroupChange}
              aria-label={labelStateFilter}
            />
          </Grid>
          <Grid item>
            <SearchField
              className={classes.searchField}
              EndAdornment={(): JSX.Element => <SearchHelpTooltip />}
              value={nextSearch || ''}
              onChange={onSearchPrepare}
              placeholder={labelResourceName}
              onKeyDown={requestSearchOnEnterKey}
            />
          </Grid>
          <Grid item>
            <Button
              variant="contained"
              color="primary"
              disabled={!currentSearch && !nextSearch}
              onClick={onSearchRequest}
            >
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
            <AutocompleteField
              className={classes.autocompleteField}
              options={resourceTypes}
              label={labelTypeOfResource}
              onChange={onResourceTypesChange}
              value={selectedResourceTypes || []}
              openText={`${labelOpen} ${labelTypeOfResource}`}
            />
          </Grid>
          <Grid item>
            <AutocompleteField
              className={classes.autocompleteField}
              options={states}
              label={labelState}
              onChange={onStatesChange}
              value={selectedStates || []}
              openText={`${labelOpen} ${labelState}`}
            />
          </Grid>
          <Grid item>
            <AutocompleteField
              className={classes.autocompleteField}
              options={statuses}
              label={labelStatus}
              onChange={onStatusesChange}
              value={selectedStatuses || []}
              openText={`${labelOpen} ${labelStatus}`}
            />
          </Grid>
          <Grid item>
            <ConnectedAutocompleteField
              className={classes.autocompleteField}
              baseEndpoint={buildHostGroupsEndpoint({ limit: 10 })}
              getSearchEndpoint={getHostGroupSearchEndpoint}
              getOptionsFromResult={getOptionsFromResult}
              label={labelHostGroup}
              onChange={onHostGroupsChange}
              value={selectedHostGroups || []}
              openText={`${labelOpen} ${labelHostGroup}`}
            />
          </Grid>
          <Grid item>
            <ConnectedAutocompleteField
              className={classes.autocompleteField}
              baseEndpoint={buildServiceGroupsEndpoint({ limit: 10 })}
              getSearchEndpoint={getServiceGroupSearchEndpoint}
              label={labelServiceGroup}
              onChange={onServiceGroupsChange}
              getOptionsFromResult={getOptionsFromResult}
              value={selectedServiceGroups || []}
              openText={`${labelOpen} ${labelServiceGroup}`}
            />
          </Grid>
          <Grid item>
            <Button color="primary" onClick={onClearAll}>
              {labelClearAll}
            </Button>
          </Grid>
        </Grid>
      </ExpansionPanelDetails>
    </ExpansionPanel>
  );
};

export default Filter;
