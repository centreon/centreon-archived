/* eslint-disable react/jsx-wrap-multilines */

import * as React from 'react';

import {
  Typography,
  Button,
  makeStyles,
  Accordion,
  AccordionSummary as MuiAccordionSummary,
  AccordionDetails as MuiAccordionDetails,
  withStyles,
} from '@material-ui/core';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';

import {
  MultiAutocompleteField,
  MultiConnectedAutocompleteField,
  SelectField,
  SearchField,
} from '@centreon/ui';

import { isEmpty, propEq, pick, find } from 'ramda';
import { Skeleton } from '@material-ui/lab';
import clsx from 'clsx';
import Filters from '@centreon/ui/src/ListingPage/Filters';
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
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from '../api/endpoint';
import { useResourceContext } from '../Context';
import SaveFilter from './Save';

const useStyles = makeStyles((theme) => ({
  grid: {
    display: 'grid',
    gridGap: theme.spacing(1),
    gridAutoFlow: 'column',

    alignItems: 'center',
    justifyItems: 'center',
  },
  filterRow: {
    gridTemplateColumns: 'auto 30px 200px 500px auto auto',
  },
  filterLoadingSkeleton: {
    transform: 'none',
    height: '100%',
    width: '100%',
  },
  criteriaRow: {
    gridTemplateColumns: `auto 30px repeat(4, auto) auto`,
  },
  autoCompleteField: {
    minWidth: 200,
    maxWidth: 400,
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

  const getHostgroupEndpoint = ({ search, page }): string => {
    return buildHostGroupsEndpoint({
      limit: 10,
      search: search ? `name:${search}` : undefined,
      page,
    });
  };

  const getServiceGroupSearchEndpoint = ({ search, page }): string => {
    return buildServiceGroupsEndpoint({
      limit: 10,
      search: search ? `name:${search}` : undefined,
      page,
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
      filtersExpandable
      labelFiltersIcon={labelShowCriteriasFilters}
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
            placeholder={labelResourceName}
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
          <div> </div>
          <MultiAutocompleteField
            className={classes.autoCompleteField}
            options={availableResourceTypes}
            label={labelTypeOfResource}
            onChange={changeResourceTypes}
            value={resourceTypes || []}
            openText={`${labelOpen} ${labelTypeOfResource}`}
          />
          <MultiAutocompleteField
            className={classes.autoCompleteField}
            options={availableStates}
            label={labelState}
            onChange={changeStates}
            value={states || []}
            openText={`${labelOpen} ${labelState}`}
          />
          <MultiAutocompleteField
            className={classes.autoCompleteField}
            options={availableStatuses}
            label={labelStatus}
            onChange={changeStatuses}
            value={statuses || []}
            openText={`${labelOpen} ${labelStatus}`}
          />
          <MultiConnectedAutocompleteField
            className={classes.autoCompleteField}
            getEndpoint={getHostgroupEndpoint}
            label={labelHostGroup}
            onChange={changeHostGroups}
            value={hostGroups || []}
            openText={`${labelOpen} ${labelHostGroup}`}
          />
          <MultiConnectedAutocompleteField
            className={classes.autoCompleteField}
            getEndpoint={getServiceGroupSearchEndpoint}
            label={labelServiceGroup}
            onChange={changeServiceGroups}
            value={serviceGroups || []}
            openText={`${labelOpen} ${labelServiceGroup}`}
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
