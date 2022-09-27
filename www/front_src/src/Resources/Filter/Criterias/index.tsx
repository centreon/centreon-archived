import { useTranslation } from 'react-i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { pipe, isNil, sortBy, reject } from 'ramda';

import { Button, Grid } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import TuneIcon from '@mui/icons-material/Tune';

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
  filterByInstalledModulesWithParsedSearchDerivedAtom,
} from '../filterAtoms';
import useFilterByModule from '../useFilterByModule';

import Criteria from './Criteria';
import { CriteriaDisplayProps, Criteria as CriteriaModel } from './models';
import { criteriaNameSortOrder } from './searchQueryLanguage/models';

const useStyles = makeStyles((theme) => ({
  container: {
    padding: theme.spacing(2),
  },
  searchButton: {
    marginTop: theme.spacing(1),
  },
}));

const CriteriasContent = (): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const { newCriteriaValueName, newSelectableCriterias } = useFilterByModule();

  const filterByInstalledModulesWithParsedSearch = useAtomValue(
    filterByInstalledModulesWithParsedSearchDerivedAtom,
  );

  const getSelectableCriterias = (): Array<CriteriaModel> => {
    const criteriasValue = filterByInstalledModulesWithParsedSearch({
      criteriaName: newCriteriaValueName,
    });

    const criterias = sortBy(
      ({ name }) => criteriaNameSortOrder[name],
      criteriasValue.criterias,
    );

    return reject(isNonSelectableCriteria)(criterias);
  };

  const getSelectableCriteriaByName = (name: string): CriteriaDisplayProps =>
    newSelectableCriterias[name];

  const isNonSelectableCriteria = (criteria: CriteriaModel): boolean =>
    pipe(({ name }) => name, getSelectableCriteriaByName, isNil)(criteria);

  const applyCurrentFilter = useUpdateAtom(applyCurrentFilterDerivedAtom);
  const clearFilter = useUpdateAtom(clearFilterDerivedAtom);

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
          {getSelectableCriterias().map(({ name, value }) => {
            return (
              <Grid item key={name}>
                <Criteria name={name} value={value as Array<SelectEntry>} />
              </Grid>
            );
          })}
          <Grid container item className={classes.searchButton} spacing={1}>
            <Grid item data-testid={labelClear}>
              <Button color="primary" size="small" onClick={clearFilter}>
                {t(labelClear)}
              </Button>
            </Grid>
            <Grid item data-testid={labelSearch}>
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
