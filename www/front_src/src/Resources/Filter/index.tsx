/* eslint-disable react/jsx-wrap-multilines */
import React, { useState } from 'react';

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

const ExpansionPanelSummary = withStyles({
  root: {
    padding: '0px 24px 0 8px',
    minHeight: 'auto',
    '&$expanded': {
      minHeight: 'auto',
    },
  },
  content: {
    margin: '8px 0',
    '&$expanded': {
      margin: '8px 0',
    },
  },
  expanded: {},
})(MuiExpansionPanelSummary);

const ExpansionPanelDetails = withStyles((theme) => ({
  root: {
    padding: theme.spacing(1),
  },
}))(MuiExpansionPanelDetails);

const useStyles = makeStyles((theme) => ({
  filterBox: {
    padding: theme.spacing(1),
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
  onSearchRequest: (event) => void;
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
  onSearchRequest,
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

  const [searchFieldValue, setSearchFieldValue] = useState<string>();

  const changeSearchFieldValue = (event): void => {
    setSearchFieldValue(event.target.value);
  };

  const requestSearch = (): void => {
    onSearchRequest(searchFieldValue);
  };

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

  return (
    <ExpansionPanel>
      <ExpansionPanelSummary
        expandIcon={
          <ExpandMoreIcon
            color="primary"
            aria-label={labelShowCriteriasFilters}
          />
        }
      >
        <Grid
          spacing={2}
          container
          direction="row"
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
              value={searchFieldValue || ''}
              onChange={changeSearchFieldValue}
              placeholder={labelResourceName}
            />
          </Grid>
          <Grid item>
            <Button
              variant="contained"
              color="primary"
              disabled={!searchFieldValue && !currentSearch}
              onClick={requestSearch}
            >
              {labelSearch}
            </Button>
          </Grid>
        </Grid>
      </ExpansionPanelSummary>
      <ExpansionPanelDetails>
        <Grid spacing={2} container direction="row" alignItems="center">
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
            />
          </Grid>
          <Grid item>
            <AutocompleteField
              className={classes.autocompleteField}
              options={states}
              label={labelState}
              onChange={onStatesChange}
              value={selectedStates || []}
            />
          </Grid>
          <Grid item>
            <AutocompleteField
              className={classes.autocompleteField}
              options={statuses}
              label={labelStatus}
              onChange={onStatusesChange}
              value={selectedStatuses || []}
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
