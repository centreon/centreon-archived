import * as React from 'react';

import { Typography, Button, makeStyles } from '@material-ui/core';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectField,
  SearchField,
  Filters,
} from '@centreon/ui';

import { isEmpty, propEq, pick, find, defaultTo } from 'ramda';
import { Skeleton } from '@material-ui/lab';
import clsx from 'clsx';
import {
  labelFilter,
  labelCriterias,
  labelStateFilter,
  labelSearch,
  labelResource,
  labelState,
  labelStatus,
  labelHostGroup,
  labelServiceGroup,
  labelClearAll,
  labelOpen,
  labelShowCriteriasFilters,
  labelNewFilter,
  labelMyFilters,
} from '../translatedLabels';
import {
  unhandledProblemsFilter,
  resourceProblemsFilter,
  allFilter,
  states as availableStates,
  resourceTypes as availableResourceTypes,
  statuses as availableStatuses,
  standardFilterById,
  isCustom,
  newFilter,
} from './models';
import SearchHelpTooltip from './SearchHelpTooltip';
import { useResourceContext } from '../Context';
import SaveFilter from './Save';
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from './api/endpoint';

const useStyles = makeStyles((theme) => ({
  grid: {
    display: 'grid',
    gridGap: theme.spacing(1),
    gridAutoFlow: 'column',

    alignItems: 'center',
    justifyItems: 'center',
  },
  filterRow: {
    gridTemplateColumns:
      'auto 30px minmax(100px, 200px) minmax(min-content, 400px) auto auto',
  },
  filterLoadingSkeleton: {
    transform: 'none',
    height: '100%',
    width: '100%',
  },
  criteriaRow: {
    gridTemplateColumns: `auto 30px repeat(5, minmax(140px, 290px)) auto`,
  },
  filterSelect: {
    width: 200,
  },
  filterLineLabel: {
    width: 60,
    textAlign: 'center',
  },
}));

const Filter = (): JSX.Element => {
  const classes = useStyles();

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
    customFiltersLoading,
  } = useResourceContext();

  const getConnectedAutocompleteEndpoint = (buildEndpoint) => ({
    search,
    page,
  }): string => {
    return buildEndpoint({
      limit: 10,
      page,
      search,
    });
  };

  const setNewFilter = (): void => {
    if (isCustom(filter)) {
      return;
    }
    setFilter({ ...newFilter, criterias: filter.criterias });
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
      standardFilterById[filterId] ||
      customFilters?.find(propEq('id', filterId));

    setFilter(updatedFilter);

    if (!updatedFilter.criterias) {
      return;
    }

    setResourceTypes(updatedFilter.criterias.resourceTypes);
    setStatuses(updatedFilter.criterias.statuses);
    setStates(updatedFilter.criterias.states);
    setHostGroups(updatedFilter.criterias.hostGroups);
    setServiceGroups(updatedFilter.criterias.serviceGroups);
    setNextSearch(updatedFilter.criterias.search);
    setCurrentSearch(updatedFilter.criterias.search);
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

  const customFilterOptions = isEmpty(customFilters)
    ? []
    : [
        {
          id: 'my_filters',
          name: labelMyFilters,
          type: 'header',
        },
        ...customFilters,
      ];

  const options = [
    { id: '', name: labelNewFilter },
    unhandledProblemsFilter,
    resourceProblemsFilter,
    allFilter,
    ...customFilterOptions,
  ];

  const canDisplaySelectedFilter = find(propEq('id', filter.id), options);

  return (
    <Filters
      expandable
      expandLabel={labelShowCriteriasFilters}
      filters={
        <div className={clsx([classes.grid, classes.filterRow])}>
          <Typography className={classes.filterLineLabel} variant="h6">
            {labelFilter}
          </Typography>
          <SaveFilter />
          {customFiltersLoading ? (
            <Skeleton className={classes.filterLoadingSkeleton} />
          ) : (
            <SelectField
              options={options.map(pick(['id', 'name', 'type']))}
              selectedOptionId={canDisplaySelectedFilter ? filter.id : ''}
              onChange={changeFilterGroup}
              aria-label={labelStateFilter}
              fullWidth
            />
          )}
          <SearchField
            fullWidth
            EndAdornment={SearchHelpTooltip}
            value={nextSearch || ''}
            onChange={prepareSearch}
            placeholder={labelSearch}
            onKeyDown={requestSearchOnEnterKey}
          />
          <Button variant="contained" color="primary" onClick={requestSearch}>
            {labelSearch}
          </Button>
        </div>
      }
      expandableFilters={
        <div className={clsx([classes.grid, classes.criteriaRow])}>
          <Typography className={classes.filterLineLabel} variant="subtitle1">
            {labelCriterias}
          </Typography>
          <div />
          <MultiAutocompleteField
            options={availableResourceTypes}
            label={labelResource}
            onChange={changeResourceTypes}
            value={resourceTypes || []}
            openText={`${labelOpen} ${labelResource}`}
            limitTags={2}
            fullWidth
          />
          <MultiAutocompleteField
            options={availableStates}
            label={labelState}
            onChange={changeStates}
            value={states || []}
            openText={`${labelOpen} ${labelState}`}
            limitTags={1}
            fullWidth
          />
          <MultiAutocompleteField
            options={availableStatuses}
            label={labelStatus}
            onChange={changeStatuses}
            value={statuses || []}
            openText={`${labelOpen} ${labelStatus}`}
            fullWidth
            limitTags={2}
          />
          <MultiConnectedAutocompleteField
            getEndpoint={getConnectedAutocompleteEndpoint(
              buildHostGroupsEndpoint,
            )}
            label={labelHostGroup}
            onChange={changeHostGroups}
            value={hostGroups || []}
            openText={`${labelOpen} ${labelHostGroup}`}
            field="name"
            fullWidth
          />
          <MultiConnectedAutocompleteField
            getEndpoint={getConnectedAutocompleteEndpoint(
              buildServiceGroupsEndpoint,
            )}
            label={labelServiceGroup}
            onChange={changeServiceGroups}
            value={serviceGroups || []}
            openText={`${labelOpen} ${labelServiceGroup}`}
            field="name"
            fullWidth
          />
          <Button color="primary" onClick={clearAllFilters}>
            {labelClearAll}
          </Button>
        </div>
      }
    />
  );
};

export default Filter;
