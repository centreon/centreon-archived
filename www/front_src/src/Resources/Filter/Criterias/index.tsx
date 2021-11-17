import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Button, Grid } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import TuneIcon from '@mui/icons-material/Tune';

import { PopoverMenu, SelectEntry, useMemoComponent } from '@centreon/ui';

import { useResourceContext } from '../../Context';
import {
  labelClear,
  labelSearch,
  labelSearchOptions,
} from '../../translatedLabels';
import { FilterState } from '../useFilter';

import Criteria from './Criteria';
import { Criteria as CriteriaInterface } from './models';

const useStyles = makeStyles((theme) => ({
  container: {
    padding: theme.spacing(2),
  },
  searchButton: {
    marginTop: theme.spacing(1),
  },
}));

interface Props
  extends Pick<FilterState, 'applyCurrentFilter' | 'clearFilter'> {
  criterias: Array<CriteriaInterface>;
}

const CriteriasContent = ({
  criterias,
  applyCurrentFilter,
  clearFilter,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

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
                <Criteria name={name} value={value as Array<SelectEntry>} />
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
  const {
    getMultiSelectCriterias,
    applyCurrentFilter,
    clearFilter,
    filterWithParsedSearch,
  } = useResourceContext();

  const criterias = getMultiSelectCriterias();

  return useMemoComponent({
    Component: (
      <CriteriasContent
        applyCurrentFilter={applyCurrentFilter}
        clearFilter={clearFilter}
        criterias={criterias}
      />
    ),
    memoProps: [criterias, filterWithParsedSearch],
  });
};

export default Criterias;
