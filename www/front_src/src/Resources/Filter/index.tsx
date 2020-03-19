import React, { useState } from 'react';

import clsx from 'clsx';

import {
  Paper,
  Grid,
  Collapse,
  IconButton,
  Typography,
  Button,
  makeStyles,
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
  expandButton: {
    float: 'right',
    transform: 'rotate(0deg)',
    marginLeft: 'auto',
    padding: theme.spacing(1),
    transition: theme.transitions.create('transform', {
      duration: theme.transitions.duration.shortest,
    }),
  },
  expandOpenButton: {
    transform: 'rotate(180deg)',
  },
  collapseWrapper: {
    padding: theme.spacing(1),
  },
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

  const [expanded, setExpanded] = useState<boolean>(false);

  const toggleExpand = (): void => {
    setExpanded(!expanded);
  };

  const getOptionsFromResult = ({ result }): Array<SelectEntry> => result;

  return (
    <Paper elevation={1} className={classes.filterBox}>
      <Grid container direction="column" spacing={2}>
        <Grid item>
          <Grid spacing={2} container direction="row" alignItems="center">
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
            <Grid item style={{ flex: 1 }}>
              <IconButton
                className={clsx(classes.expandButton, {
                  [classes.expandOpenButton]: expanded,
                })}
                onClick={toggleExpand}
                aria-expanded={expanded}
                aria-label={labelShowCriteriasFilters}
              >
                <ExpandMoreIcon color="primary" />
              </IconButton>
            </Grid>
          </Grid>
        </Grid>
        <Grid item style={{ padding: 0 }}>
          <Collapse
            in={expanded}
            classes={{ wrapper: classes.collapseWrapper }}
          >
            <Grid spacing={2} container direction="row" alignItems="center">
              <Grid item>
                <Typography
                  className={classes.filterLineLabel}
                  variant="subtitle1"
                >
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
          </Collapse>
        </Grid>
      </Grid>
    </Paper>
  );
};

export default Filter;
