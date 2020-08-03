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
  SelectEntry,
} from '@centreon/ui';

import { isEmpty, propEq, pick, find } from 'ramda';
import { Skeleton } from '@material-ui/lab';
import clsx from 'clsx';
import { useTranslation } from 'react-i18next';
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
import SearchHelpTooltip from './SearchHelpTooltip';
import {
  buildHostGroupsEndpoint,
  buildServiceGroupsEndpoint,
} from '../api/endpoint';
import { useResourceContext } from '../Context';
import SaveFilter from './Save';
import useFilterModels from './useFilterModels';

const AccordionSummary = withStyles((theme) => ({
  root: {
    padding: theme.spacing(0, 3, 0, 2),
    minHeight: 'auto',
    '&$expanded': {
      minHeight: 'auto',
    },
    '&$focused': {
      backgroundColor: 'unset',
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
  focused: {},
  expanded: {},
}))(MuiAccordionSummary);

const AccordionDetails = withStyles((theme) => ({
  root: {
    padding: theme.spacing(0, 0.5, 1, 2),
  },
}))(MuiAccordionDetails);

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
    customFilters,
    customFiltersLoading,
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
    <Accordion square expanded={expanded}>
      <AccordionSummary
        expandIcon={
          <ExpandMoreIcon
            color="primary"
            aria-label={t(labelShowCriteriasFilters)}
          />
        }
        IconButtonProps={{ onClick: toggleExpanded }}
        style={{ cursor: 'default' }}
      >
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
            placeholder={t(labelResourceName)}
            onKeyDown={requestSearchOnEnterKey}
          />
          <Button variant="contained" color="primary" onClick={requestSearch}>
            {t(labelSearch)}
          </Button>
        </div>
      </AccordionSummary>
      <AccordionDetails>
        <div className={clsx([classes.grid, classes.criteriaRow])}>
          <Typography className={classes.filterLineLabel} variant="subtitle1">
            {t(labelCriterias)}
          </Typography>
          <div> </div>
          <MultiAutocompleteField
            className={classes.autoCompleteField}
            options={availableResourceTypes}
            label={t(labelTypeOfResource)}
            onChange={changeResourceTypes}
            value={resourceTypes || []}
            openText={`${t(labelOpen)} ${t(labelTypeOfResource)}`}
          />
          <MultiAutocompleteField
            className={classes.autoCompleteField}
            options={availableStates}
            label={labelState}
            onChange={changeStates}
            value={states || []}
            openText={`${t(labelOpen)} ${t(labelState)}`}
          />
          <MultiAutocompleteField
            className={classes.autoCompleteField}
            options={availableStatuses}
            label={labelStatus}
            onChange={changeStatuses}
            value={statuses || []}
            openText={`${t(labelOpen)} ${t(labelStatus)}`}
          />
          <MultiConnectedAutocompleteField
            className={classes.autoCompleteField}
            baseEndpoint={buildHostGroupsEndpoint({ limit: 10 })}
            getSearchEndpoint={getHostGroupSearchEndpoint}
            getOptionsFromResult={getOptionsFromResult}
            label={labelHostGroup}
            onChange={changeHostGroups}
            value={hostGroups || []}
            openText={`${t(labelOpen)} ${t(labelHostGroup)}`}
          />
          <MultiConnectedAutocompleteField
            className={classes.autoCompleteField}
            baseEndpoint={buildServiceGroupsEndpoint({ limit: 10 })}
            getSearchEndpoint={getServiceGroupSearchEndpoint}
            label={t(labelServiceGroup)}
            onChange={changeServiceGroups}
            getOptionsFromResult={getOptionsFromResult}
            value={serviceGroups || []}
            openText={`${t(labelOpen)} ${t(labelServiceGroup)}`}
          />
          <Button color="primary" onClick={clearAllFilters}>
            {t(labelClearAll)}
          </Button>
        </div>
      </AccordionDetails>
    </Accordion>
  );
};

export default Filter;
