import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Typography } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import memoizeComponent from '../../../../Resources/memoizedComponent';
import { labelPasswordBlockingPolicy } from '../../translatedLabels';

import Attempts from './Attempts';
import BlockingDuration from './BlockingDuration';

const useStyles = makeStyles((theme) => ({
  fields: {
    display: 'flex',
    flexDirection: 'column',
    marginTop: theme.spacing(1),
    rowGap: theme.spacing(1.5),
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
