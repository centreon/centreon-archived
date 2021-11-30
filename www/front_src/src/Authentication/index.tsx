import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

import { Paper, makeStyles, Theme, Typography } from '@material-ui/core';

import { labelDefinePasswordSecurityPolicy } from './translatedLabels';
import useAuthentication from './useAuthentication';
import Form from './Form';
import { SecurityPolicy } from './models';

const useStyles = makeStyles((theme: Theme) => ({
  authenticationContainer: {
    margin: theme.spacing(1),
    padding: theme.spacing(1),
  },
}));

const Authentication = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const { sendingGetSecurityPolicy, initialSecurityPolicy } =
    useAuthentication();

  const isSecurityPolicyEmpty = React.useMemo(
    () => isNil(initialSecurityPolicy),
    [initialSecurityPolicy],
  );

  return (
    <Paper className={classes.authenticationContainer}>
      <Typography variant="h4">
        {t(labelDefinePasswordSecurityPolicy)}
      </Typography>
      {sendingGetSecurityPolicy || isSecurityPolicyEmpty ? (
        <Typography>{t('loading')}</Typography>
      ) : (
        <Form initialValues={initialSecurityPolicy as SecurityPolicy} />
      )}
    </Paper>
  );
};

export default Authentication;
