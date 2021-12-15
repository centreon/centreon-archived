import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { isNil, pipe, reject, sortBy } from 'ramda';

import { Button, Grid, makeStyles } from '@material-ui/core';
import TuneIcon from '@material-ui/icons/Tune';

import { PopoverMenu, SelectEntry, useMemoComponent } from '@centreon/ui';

import {
  labelClear,
  labelSearch,
  labelSearchOptions,
} from '../../translatedLabels';
import {
  applyCurrentFilterDerivedAtom,
  clearFilterDerivedAtom,
  filterWithParsedSearchDerivedAtom,
} from '../filterAtoms';

import FilterCriteria from './Criteria';
import { Criteria, CriteriaDisplayProps, selectableCriterias } from './models';
import { criteriaNameSortOrder } from './searchQueryLanguage/models';

const useStyles = makeStyles((theme) => ({
  container: {
    padding: theme.spacing(2),
  },
  searchButton: {
    marginTop: theme.spacing(1),
  },
}));

const getCriterias = (filterWithParsedSearch): Array<Criteria> => {
  const getSelectableCriteriaByName = (name: string): CriteriaDisplayProps =>
    selectableCriterias[name];

  const isNonSelectableCriteria = (criteria: Criteria): boolean =>
    pipe(({ name }) => name, getSelectableCriteriaByName, isNil)(criteria);

  const criterias = sortBy<Criteria>(
    ({ name }) => criteriaNameSortOrder[name],
    filterWithParsedSearch.criterias,
  );

  return pipe(
    reject(isNonSelectableCriteria) as (criterias) => Array<Criteria>,
  )(criterias);
};

const CriteriasContent = (): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const filterWithParsedSearch = useAtomValue(
    filterWithParsedSearchDerivedAtom,
  );
  const applyCurrentFilter = useUpdateAtom(applyCurrentFilterDerivedAtom);
  const clearFilter = useUpdateAtom(clearFilterDerivedAtom);

  const criterias = getCriterias(filterWithParsedSearch);

  return (
    <PopoverMenu
      icon={<TuneIcon fontSize="small" />}
      popperPlacement="bottom-start"
      title={t(labelSearchOptions)}
      onClose={applyCurrentFilter}
    >
      {(): JSX.Element => (
        <Grid
          container
          alignItems="stretch"
          className={classes.container}
          direction="column"
          spacing={1}
        >
          {criterias.map(({ name, value }) => {
            return (
              <Grid item key={name}>
                <FilterCriteria
                  name={name}
                  value={value as Array<SelectEntry>}
                />
              </Grid>
            );
          })}
          <Grid container item className={classes.searchButton} spacing={1}>
            <Grid item>
              <Button color="primary" size="small" onClick={clearFilter}>
                {t(labelClear)}
              </Button>
            </Grid>
            <Grid item>
              <Button
                color="primary"
                size="small"
                variant="contained"
                onClick={applyCurrentFilter}
              >
                {t(labelSearch)}
              </Button>
            </Grid>
          </Grid>
        </Grid>
      )}
    </PopoverMenu>
  );
};

const Criterias = (): JSX.Element => {
  const filterWithParsedSearch = useAtomValue(
    filterWithParsedSearchDerivedAtom,
  );

  return useMemoComponent({
    Component: <CriteriasContent />,
    memoProps: [filterWithParsedSearch],
  });
};

export default Criterias;
