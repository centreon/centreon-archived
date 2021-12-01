import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles, Typography } from '@material-ui/core';

import memoizeComponent from '../../../Resources/memoizedComponent';
import { labelPasswordBlockingPolicy } from '../../translatedLabels';

import Attempts from './Attempts';
import BlockingDuration from './BlockingDuration';

const useStyles = makeStyles((theme) => ({
  fields: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(1),
    rowGap: theme.spacing(1),
  },
}));

const PasswordBlockingPolicy = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return (
    <div>
      <Typography variant="h5">{t(labelPasswordBlockingPolicy)}</Typography>
      <div className={classes.fields}>
        <Attempts />
        <BlockingDuration />
      </div>
    </div>
  );
};

export default memoizeComponent({
  Component: PasswordBlockingPolicy,
  memoProps: [],
});
