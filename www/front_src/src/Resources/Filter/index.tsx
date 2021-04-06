import * as React from 'react';

import { isEmpty, propEq, pick, find } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Button, makeStyles, Grid } from '@material-ui/core';

import { MemoizedFilters as Filters, SearchField } from '@centreon/ui';

import {
  labelStateFilter,
  labelSearch,
  labelShowCriteriasFilters,
  labelNewFilter,
  labelMyFilters,
} from '../translatedLabels';
import { useResourceContext } from '../Context';

import SearchHelpTooltip from './SearchHelpTooltip';
import SaveFilter from './Save';
import FilterLoadingSkeleton from './FilterLoadingSkeleton';
import Criterias from './Criterias';
import {
  standardFilterById,
  unhandledProblemsFilter,
  resourceProblemsFilter,
  allFilter,
} from './models';
import SelectFilter from './Fields/SelectFilter';

const useStyles = makeStyles(() => ({
  criterias: {
    marginLeft: 36,
  },
  field: {
    minWidth: 155,
  },
  filterLineLabel: {
    textAlign: 'center',
    width: 60,
  },
  filterSelect: {
    width: 200,
  },
  searchField: {
    width: 375,
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
      expandableFilters={<Criterias />}
      expanded={filterExpanded}
      filters={
        <Grid container alignItems="center" spacing={1}>
          <Grid item>
            <SaveFilter />
          </Grid>
          <Grid item>
            {customFiltersLoading ? (
              <FilterLoadingSkeleton />
            ) : (
              <SelectFilter
                ariaLabel={t(labelStateFilter)}
                className={classes.filterSelect}
                options={options.map(pick(['id', 'name', 'type']))}
                selectedOptionId={canDisplaySelectedFilter ? filter.id : ''}
                onChange={changeFilter}
              />
            )}
          </Grid>
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
              variant="contained"
              onClick={requestSearch}
            >
              {t(labelSearch)}
            </Button>
          </Grid>
        </Grid>
      }
      memoProps={memoProps}
      onExpand={toggleFilterExpanded}
    />
  );
};

export default Filter;
export { useStyles };
