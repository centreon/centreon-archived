import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles, Grid } from '@material-ui/core';
import IconCheck from '@material-ui/icons/Check';

import { labelActive } from '../../../../translatedLabels';

import DetailsLine from './DetailsLine';

const useStyles = makeStyles((theme) => ({
  activeIcon: {
    color: theme.palette.success.main,
  },
}));

const ActiveLine = (): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();

  return (
    <Grid container spacing={1} alignItems="center">
      <Grid item>
        <IconCheck className={classes.activeIcon} />
      </Grid>
      <Grid item>
        <DetailsLine key="tries" line={t(labelActive)} />
      </Grid>
    </Grid>
  );
};

export default ActiveLine;
