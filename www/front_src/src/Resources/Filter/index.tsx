import React, { useState } from 'react';

import { Paper, Grid, Typography, Button, makeStyles } from '@material-ui/core';

import { AutocompleteField, SelectField, SearchField } from '@centreon/ui';

import {
  labelFilter,
  labelStateFilter,
  labelResourceName,
  labelSearch,
  labelTypeOfResource,
  labelState,
  labelStatus,
  labelHostGroup,
  labelServiceGroup,
  labelClearAll,
} from '../translatedLabels';
import {
  unhandledProblemsFilter,
  resourceProblemsFilter,
  allFilter,
  states,
  resourceTypes,
  statuses,
  filterById,
} from './models';
import SearchHelpTooltip from '../SearchHelpTooltip';

import ConnectedAutocompleteField from './ConnectedAutocompleteField';
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from '../api/endpoint';

const useStyles = makeStyles((theme) => ({
  filterBox: {
    padding: theme.spacing(2),
    backgroundColor: theme.palette.common.white,
  },
  autocompleteField: {
    width: 275,
  },
}));

const Filter = ({
  filter,
  onFilterChange,
  search,
  onSearchRequest,
  selectedResourceTypes,
  onResourceTypeChange,
  selectedStates,
  onStatesChange,
  selectedStatuses,
  onStatusesChange,
  selectedHostGroups,
  onHostgroupsChange,
  selectedServiceGroups,
  onServiceGroupsChange,
}): JSX.Element => {
  const classes = useStyles();

  const [searchFieldValue, setSearchFieldValue] = useState<string>();

  const changeSearchFieldValue = (event): void => {
    setSearchFieldValue(event.target.value);
  };

  const requestSearch = (): void => {
    onSearchRequest(searchFieldValue);
  };

  return (
    <Paper elevation={1} className={classes.filterBox}>
      <Grid container direction="column" spacing={2}>
        <Grid item>
          <Typography variant="h6">{labelFilter}</Typography>
        </Grid>
        <Grid item>
          <Grid spacing={2} container direction="row" alignItems="center">
            <Grid item>
              <SelectField
                options={[
                  unhandledProblemsFilter,
                  resourceProblemsFilter,
                  allFilter,
                ]}
                selectedOptionId={filter.id}
                onChange={onFilterChange}
                ariaLabel={labelStateFilter}
              />
            </Grid>
            <Grid item>
              <SearchField
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
                disabled={!searchFieldValue && !search}
                onClick={requestSearch}
              >
                {labelSearch}
              </Button>
            </Grid>
          </Grid>
        </Grid>
        <Grid item>
          <Grid spacing={2} container direction="row" alignItems="center">
            <Grid item>
              <AutocompleteField
                className={classes.autocompleteField}
                options={resourceTypes}
                label={labelTypeOfResource}
                onChange={onResourceTypeChange}
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
                getSearchEndpoint={(searchValue): string =>
                  buildHostGroupsEndpoint({
                    limit: 10,
                    search: `name:^${searchValue}`,
                  })
                }
                label={labelHostGroup}
                onChange={onHostgroupsChange}
                value={selectedHostGroups || []}
              />
            </Grid>
            <Grid item>
              <ConnectedAutocompleteField
                className={classes.autocompleteField}
                baseEndpoint={buildServiceGroupsEndpoint({ limit: 10 })}
                getSearchEndpoint={(searchValue): string =>
                  buildServiceGroupsEndpoint({
                    limit: 10,
                    search: `name:^${searchValue}`,
                  })
                }
                label={labelServiceGroup}
                onChange={onServiceGroupsChange}
                value={selectedServiceGroups || []}
              />
            </Grid>
            <Grid item>
              <Button color="primary">{labelClearAll}</Button>
            </Grid>
          </Grid>
        </Grid>
      </Grid>
    </Paper>
  );
};

export default Filter;
