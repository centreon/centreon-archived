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
    minWidth: 155,
  },
  filterLineLabel: {
    width: 60,
    textAlign: 'center',
  },
}));

const Filter = (): JSX.Element => {
  const [expanded, setExpanded] = React.useState(false);
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

  const expandFilters = () => setExpanded((expand) => !expand);

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
      expanded={expanded}
      expandLabel={labelShowCriteriasFilters}
      onExpand={expandFilters}
      filters={
        <Grid container spacing={1} alignItems="center">
          <Grid item>
            <SaveFilter />
          </Grid>
          <Grid item>
            {customFiltersLoading ? (
              <FilterLoadingSkeleton />
            ) : (
              <SelectFilter
                options={options.map(pick(['id', 'name', 'type']))}
                selectedOptionId={canDisplaySelectedFilter ? filter.id : ''}
                onChange={changeFilter}
                ariaLabel={t(labelStateFilter)}
                className={classes.filterSelect}
              />
            )}
          </Grid>
          <Grid item>
            <SearchField
              EndAdornment={SearchHelpTooltip}
              value={nextSearch || ''}
              onChange={prepareSearch}
              placeholder={t(labelSearch)}
              onKeyDown={requestSearchOnEnterKey}
            />
          </Grid>
          <Grid item>
            <Button
              variant="contained"
              color="primary"
              size="small"
              onClick={requestSearch}
            >
              {t(labelSearch)}
            </Button>
          </Grid>
        </Grid>
      }
      expandableFilters={<Criterias />}
      memoProps={memoProps}
    />
  );
};

export default Filter;
export { useStyles };
