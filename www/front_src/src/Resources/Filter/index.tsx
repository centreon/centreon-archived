import * as React from 'react';

import { isEmpty, propEq, pick, find } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Button, makeStyles, Grid, Typography } from '@material-ui/core';
import SearchIcon from '@material-ui/icons/Search';

import {
  MemoizedFilters as Filters,
  SearchField,
  IconButton,
  TextField,
} from '@centreon/ui';

import {
  labelStateFilter,
  labelSearch,
  labelShowCriteriasFilters,
  labelNewFilter,
  labelMyFilters,
  labelFilter,
} from '../translatedLabels';
import { useResourceContext } from '../Context';

import SearchHelpTooltip from './SearchHelpTooltip';
import SaveFilter from './Save';
import FilterLoadingSkeleton from './FilterLoadingSkeleton';
import Criterias from './Criterias';
import FilterSummary from './Summary';
import {
  standardFilterById,
  unhandledProblemsFilter,
  resourceProblemsFilter,
  allFilter,
} from './models';
import SelectFilter from './Fields/SelectFilter';

const useStyles = makeStyles(() => ({
  filterSelect: {
    width: 200,
  },
}));

const Filter = (): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const {
    filter,
    setFilter,
    nextSearch,
    setNextSearch,
    customFilters,
    customFiltersLoading,
    setCriteria,
    setNewFilter,
    filterExpanded,
    toggleFilterExpanded,
  } = useResourceContext();

  const memoProps = [filter, nextSearch, customFilters, customFiltersLoading];

  const requestSearch = (): void => {
    setCriteria({ name: 'search', value: nextSearch });
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

  const changeFilter = (event): void => {
    const filterId = event.target.value;

    const updatedFilter =
      standardFilterById[filterId] ||
      customFilters?.find(propEq('id', filterId));

    setFilter(updatedFilter);
    setNextSearch(updatedFilter.criterias.find(propEq('name', 'search')).value);
  };

  const translatedOptions = [
    unhandledProblemsFilter,
    resourceProblemsFilter,
    allFilter,
  ].map(({ id, name }) => ({ id, name: t(name) }));

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
    ...translatedOptions,
    ...customFilterOptions,
  ];

  const canDisplaySelectedFilter = find(propEq('id', filter.id), options);

  return (
    <Filters
      expandLabel={labelShowCriteriasFilters}
      expandableFilters={
        <Grid container item alignItems="center" spacing={1}>
          <Criterias />
        </Grid>
      }
      expanded={filterExpanded}
      filters={
        <Grid container item alignItems="center" spacing={1} wrap="nowrap">
          <Grid item>
            <SaveFilter />
          </Grid>
          <Grid item>
            {customFiltersLoading ? (
              <FilterLoadingSkeleton />
            ) : (
              <SelectFilter
                ariaLabel={t(labelStateFilter)}
                options={options.map(pick(['id', 'name', 'type']))}
                selectedOptionId={canDisplaySelectedFilter ? filter.id : ''}
                onChange={changeFilter}
              />
            )}
          </Grid>
          {filterExpanded ? (
            <>
              <Grid item>
                <SearchField
                  EndAdornment={SearchHelpTooltip}
                  placeholder={t(labelSearch)}
                  value={nextSearch || ''}
                  onChange={prepareSearch}
                  onKeyDown={requestSearchOnEnterKey}
                />
              </Grid>
              <Grid item>
                <Button
                  color="primary"
                  size="small"
                  variant="text"
                  onClick={requestSearch}
                >
                  {t(labelSearch)}
                </Button>
              </Grid>
            </>
          ) : (
            <Grid item>
              <FilterSummary />
            </Grid>
          )}
        </Grid>
      }
      memoProps={memoProps}
      onExpand={toggleFilterExpanded}
    />
  );
};

export default Filter;
