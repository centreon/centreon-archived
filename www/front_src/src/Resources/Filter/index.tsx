import * as React from 'react';

import { isEmpty, propEq, pick, find } from 'ramda';
import { useTranslation } from 'react-i18next';
import clsx from 'clsx';
import { ParentSize } from '@visx/visx';

import { Button, makeStyles } from '@material-ui/core';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectField,
  SearchField,
  Filters,
} from '@centreon/ui';

import {
  labelStateFilter,
  labelSearch,
  labelResource,
  labelState,
  labelStatus,
  labelHostGroup,
  labelServiceGroup,
  labelClear,
  labelOpen,
  labelShowCriteriasFilters,
  labelNewFilter,
  labelMyFilters,
} from '../translatedLabels';
import { useResourceContext } from '../Context';

import SearchHelpTooltip from './SearchHelpTooltip';
import SaveFilter from './Save';
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from './api/endpoint';
import useFilterModels from './useFilterModels';
import FilterLoadingSkeleton from './FilterLoadingSkeleton';

const useStyles = makeStyles((theme) => ({
  grid: {
    display: 'flex',
    flexWrap: 'wrap',
    gridGap: theme.spacing(1),
    alignItems: 'center',
  },
  filterSelect: {
    width: 200,
  },
  criterias: {
    marginLeft: 36,
  },
  searchField: {
    width: 375,
  },
  field: {
    minWidth: 160,
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
        <div className={classes.grid}>
          <SaveFilter />
          {customFiltersLoading ? (
            <FilterLoadingSkeleton />
          ) : (
            <SelectField
              options={options.map(pick(['id', 'name', 'type']))}
              selectedOptionId={canDisplaySelectedFilter ? filter.id : ''}
              onChange={changeFilterGroup}
              aria-label={t(labelStateFilter)}
              className={classes.field}
            />
          )}
          <SearchField
            className={classes.searchField}
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
        <ParentSize>
          {({ width }): JSX.Element => {
            const limitTags = width < 1000 ? 1 : 2;

            const commonProps = {
              limitTags,
              className: classes.field,
            };

            return (
              <div className={clsx([classes.grid, classes.criterias])}>
                <MultiAutocompleteField
                  options={availableResourceTypes}
                  label={t(labelResource)}
                  onChange={changeResourceTypes}
                  value={resourceTypes || []}
                  openText={`${t(labelOpen)} ${t(labelResource)}`}
                  {...commonProps}
                />
                <MultiAutocompleteField
                  options={availableStates}
                  label={t(labelState)}
                  onChange={changeStates}
                  value={states || []}
                  openText={`${t(labelOpen)} ${t(labelState)}`}
                  {...commonProps}
                />
                <MultiAutocompleteField
                  options={availableStatuses}
                  label={t(labelStatus)}
                  onChange={changeStatuses}
                  value={statuses || []}
                  openText={`${t(labelOpen)} ${t(labelStatus)}`}
                  {...commonProps}
                />
                <MultiConnectedAutocompleteField
                  getEndpoint={getConnectedAutocompleteEndpoint(
                    buildHostGroupsEndpoint,
                  )}
                  label={t(labelHostGroup)}
                  onChange={changeHostGroups}
                  value={hostGroups || []}
                  openText={`${t(labelOpen)} ${t(labelHostGroup)}`}
                  field="name"
                  {...commonProps}
                />
                <MultiConnectedAutocompleteField
                  getEndpoint={getConnectedAutocompleteEndpoint(
                    buildServiceGroupsEndpoint,
                  )}
                  label={t(labelServiceGroup)}
                  onChange={changeServiceGroups}
                  value={serviceGroups || []}
                  openText={`${t(labelOpen)} ${t(labelServiceGroup)}`}
                  field="name"
                  {...commonProps}
                />
                <Button color="primary" onClick={clearAllFilters} size="small">
                  {t(labelClear)}
                </Button>
              </div>
            );
          }}
        </ParentSize>
      }
    />
  );
};

export default Filter;
