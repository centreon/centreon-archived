import * as React from 'react';

import { isEmpty, propEq, pick, find } from 'ramda';
import { useTranslation } from 'react-i18next';

import { Button, Grid, Tooltip } from '@material-ui/core';
import TuneIcon from '@material-ui/icons/Tune';

import {
  IconButton,
  MemoizedFilters as Filters,
  SearchField,
} from '@centreon/ui';

import {
  labelStateFilter,
  labelSearch,
  labelShowCriteriasFilters,
  labelNewFilter,
  labelMyFilters,
  labelSearchOptions,
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

const Filter = (): JSX.Element => {
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

  const [criteriasOpen, setCriteriasOpen] = React.useState(false);

  const memoProps = [
    filter,
    nextSearch,
    customFilters,
    customFiltersLoading,
    criteriasOpen,
  ];

  const requestSearch = (): void => {
    setCriteria({ name: 'search', value: nextSearch });
  };

  const requestSearchOnEnterKey = (event: React.KeyboardEvent): void => {
    const enterKeyPressed = event.keyCode === 13;

    if (enterKeyPressed) {
      requestSearch();
    }
  };

  const toggleCriteriasOpen = (): void => {
    setCriteriasOpen(!criteriasOpen);
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
          <Grid item>
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
              value={nextSearch || ''}
              onChange={prepareSearch}
              onKeyDown={requestSearchOnEnterKey}
            />
          </Grid>
        </Grid>
      }
      memoProps={memoProps}
    />
  );
};

export default Filter;
