import * as React from 'react';

import { isEmpty, propEq, pick, find } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Grid, makeStyles } from '@material-ui/core';

import { MemoizedFilters as Filters, SearchField } from '@centreon/ui';

import {
  labelStateFilter,
  labelSearch,
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
import { build } from './Criterias/searchQueryLanguage';

const useStyles = makeStyles((theme) => ({
  container: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridGap: theme.spacing(1),
    gridTemplateColumns: 'auto auto 1fr',
    width: '100%',
  },
}));

const Filter = (): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  const {
    filter,
    setFilter,

    customFilters,
    customFiltersLoading,
    setCriteria,
    setSearch,
    setNewFilter,
    transientFilter,
    setTransientFilter,
    search,
  } = useResourceContext();

  const memoProps = [
    filter,
    transientFilter,
    customFilters,
    customFiltersLoading,
    search,
    setSearch,
  ];

  const requestSearch = (): void => {
    // setCriteria({ name: 'search', value: nextSearch });
  };

  const requestSearchOnEnterKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.keyCode === 13;

    if (enterKeyPressed) {
      requestSearch();
    }
  };

  const prepareSearch = (event): void => {
    // setNextSearch(event.target.value);
    // setTr
    setSearch(event.target.value);
    console.log(event.target.value);
    setNewFilter();
  };

  const changeFilter = (event): void => {
    const filterId = event.target.value;

    const updatedFilter =
      standardFilterById[filterId] ||
      customFilters?.find(propEq('id', filterId));

    setFilter(updatedFilter);
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
      filters={
        <div className={classes.container}>
          <SaveFilter />
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

          <SearchField
            EndAdornment={() => (
              <Grid container direction="row" wrap="nowrap">
                <Grid item>
                  <Criterias />
                </Grid>
                <Grid item>
                  <SearchHelpTooltip />
                </Grid>
              </Grid>
            )}
            placeholder={t(labelSearch)}
            value={search}
            onChange={prepareSearch}
            onKeyDown={requestSearchOnEnterKey}
          />
        </div>
      }
      memoProps={memoProps}
    />
  );
};

export default Filter;
