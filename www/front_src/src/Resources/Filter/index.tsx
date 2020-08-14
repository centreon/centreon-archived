import * as React from 'react';

import { Typography, Button, makeStyles } from '@material-ui/core';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectField,
  SearchField,
  Filters,
} from '@centreon/ui';

import { isEmpty, propEq, pick, find } from 'ramda';
import { Skeleton } from '@material-ui/lab';
import clsx from 'clsx';
import { useTranslation } from 'react-i18next';
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
import SearchHelpTooltip from './SearchHelpTooltip';
import { useResourceContext } from '../Context';
import SaveFilter from './Save';
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from './api/endpoint';
import useFilterModels from './useFilterModels';

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

  const { t } = useTranslation();

  const {
    unhandledProblemsFilter,
    resourceProblemsFilter,
    allFilter,
    states: availableStates,
    resourceTypes: availableResourceTypes,
    statuses: availableStatuses,
    standardFilterById,
    isCustom,
    newFilter,
  } = useFilterModels();

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
          name: t(labelMyFilters),
          type: 'header',
        },
        ...customFilters,
      ];

  const options = [
    { id: '', name: t(labelNewFilter) },
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
            {t(labelFilter)}
          </Typography>
          <SaveFilter />
          {customFiltersLoading ? (
            <Skeleton className={classes.filterLoadingSkeleton} />
          ) : (
            <SelectField
              options={options.map(pick(['id', 'name', 'type']))}
              selectedOptionId={canDisplaySelectedFilter ? filter.id : ''}
              onChange={changeFilterGroup}
              aria-label={t(labelStateFilter)}
              fullWidth
            />
          )}
          <SearchField
            fullWidth
            EndAdornment={SearchHelpTooltip}
            value={nextSearch || ''}
            onChange={prepareSearch}
            placeholder={t(labelSearch)}
            onKeyDown={requestSearchOnEnterKey}
          />
          <Button variant="contained" color="primary" onClick={requestSearch}>
            {t(labelSearch)}
          </Button>
        </div>
      }
      expandableFilters={
        <div className={clsx([classes.grid, classes.criteriaRow])}>
          <Typography className={classes.filterLineLabel} variant="subtitle1">
            {t(labelCriterias)}
          </Typography>
          <div />
          <MultiAutocompleteField
            options={availableResourceTypes}
            label={t(labelResource)}
            onChange={changeResourceTypes}
            value={resourceTypes || []}
            openText={`${t(labelOpen)} ${t(labelResource)}`}
            limitTags={2}
            fullWidth
          />
          <MultiAutocompleteField
            options={availableStates}
            label={labelState}
            onChange={changeStates}
            value={states || []}
            openText={`${t(labelOpen)} ${t(labelState)}`}
            limitTags={1}
            fullWidth
          />
          <MultiAutocompleteField
            options={availableStatuses}
            label={labelStatus}
            onChange={changeStatuses}
            value={statuses || []}
            openText={`${t(labelOpen)} ${t(labelStatus)}`}
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
            openText={`${t(labelOpen)} ${t(labelHostGroup)}`}
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
            openText={`${t(labelOpen)} ${t(labelServiceGroup)}`}
            field="name"
            fullWidth
          />
          <Button color="primary" onClick={clearAllFilters}>
            {t(labelClearAll)}
          </Button>
        </div>
      }
    />
  );
};

export default Filter;
