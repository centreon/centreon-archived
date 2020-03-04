import React from 'react';

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
} from '../translatedLabels';
import {
  unhandledProblemsFilter,
  resourcesProblemFilter,
  allFilter,
} from '../models';
import SearchHelpTooltip from '../SearchHelpTooltip';
import { states, resourceTypes, statuses } from './filterParams';

const useStyles = makeStyles((theme) => ({
  filterBox: {
    padding: theme.spacing(2),
    backgroundColor: theme.palette.common.white,
  },
}));

const Filter = ({
  filterId,
  search,
  onSearchChange,
  onSearchRequest,
  onFilterChange,
  selectedResourceTypes,
  onResourceTypeChange,
  selectedStates,
  onStatesChange,
  selectedStatuses,
  onStatusesChange,
  // selectedHostGroups,
  // onHostgroupsChange,
  // selectedGroups,
  // onServiceGroupsChange,
}) => {
  const classes = useStyles();

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
                  resourcesProblemFilter,
                  allFilter,
                ]}
                selectedOptionId={filterId}
                onChange={onFilterChange}
                ariaLabel={labelStateFilter}
              />
            </Grid>
            <Grid item>
              <SearchField
                EndAdornment={(): JSX.Element => <SearchHelpTooltip />}
                value={search || ''}
                onChange={onSearchChange}
                placeholder={labelResourceName}
              />
            </Grid>
            <Grid item>
              <Button
                variant="contained"
                color="primary"
                disabled={!search}
                onClick={onSearchRequest}
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
                style={{ width: 275 }}
                options={resourceTypes}
                label={labelTypeOfResource}
                onChange={onResourceTypeChange}
                defaultValue={selectedResourceTypes || []}
              />
            </Grid>
            <Grid item>
              <AutocompleteField
                options={states}
                label={labelState}
                onChange={onStatesChange}
                defaultValue={selectedStates || []}
              />
            </Grid>
            <Grid item>
              <AutocompleteField
                options={statuses}
                label={labelStatus}
                onChange={onStatusesChange}
                defaultValue={selectedStatuses || []}
              />
            </Grid>
          </Grid>
        </Grid>
      </Grid>
    </Paper>
  );
};

export default Filter;
