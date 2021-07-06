import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Button, Grid, makeStyles } from '@material-ui/core';
import TuneIcon from '@material-ui/icons/Tune';

import { PopoverMenu, SelectEntry, useMemoComponent } from '@centreon/ui';

import { useResourceContext } from '../../Context';
import {
  labelClear,
  labelSearch,
  labelSearchOptions,
} from '../../translatedLabels';

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

interface Props {
  criterias: Array<CriteriaInterface>;
}

const CriteriasContent = ({ criterias }: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  return (
    <PopoverMenu
      icon={<TuneIcon fontSize="small" />}
      popperPlacement="bottom-start"
      title={t(labelSearchOptions)}
    >
      {() => (
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
              <Button color="primary" size="small">
                {t(labelClear)}
              </Button>
            </Grid>
            <Grid item>
              <Button color="primary" size="small" variant="contained">
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
  const { getMultiSelectCriterias } = useResourceContext();

  const criterias = getMultiSelectCriterias();

  return useMemoComponent({
    Component: <CriteriasContent criterias={criterias} />,
    memoProps: [criterias],
  });
};

export default Criterias;
