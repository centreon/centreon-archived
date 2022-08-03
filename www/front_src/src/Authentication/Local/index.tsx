import { useMemo } from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, not } from 'ramda';

import { Theme, Typography, LinearProgress } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import useTab from '../useTab';

import { labelDefinePasswordPasswordSecurityPolicy } from './translatedLabels';
import useAuthentication from './useAuthentication';
import Form from './Form';
import { PasswordSecurityPolicy } from './models';

const useStyles = makeStyles((theme: Theme) => ({
  loading: {
    height: theme.spacing(0.5),
  },
}));

const LocalAuthentication = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    sendingGetPasswordPasswordSecurityPolicy,
    initialPasswordPasswordSecurityPolicy,
    loadPasswordPasswordSecurityPolicy,
  } = useAuthentication();

  const isPasswordSecurityPolicyEmpty = useMemo(
    () => isNil(initialPasswordPasswordSecurityPolicy),
    [initialPasswordPasswordSecurityPolicy],
  );

  useTab(isPasswordSecurityPolicyEmpty);

  return (
    <div>
      <Typography variant="h4">
        {t(labelDefinePasswordPasswordSecurityPolicy)}
      </Typography>
      <div className={classes.loading}>
        {not(isPasswordSecurityPolicyEmpty) &&
          sendingGetPasswordPasswordSecurityPolicy && <LinearProgress />}
      </div>
      <Form
        initialValues={
          initialPasswordPasswordSecurityPolicy as PasswordSecurityPolicy
        }
        isLoading={isPasswordSecurityPolicyEmpty}
        loadPasswordSecurityPolicy={loadPasswordPasswordSecurityPolicy}
      />
    </div>
  );
};

export default LocalAuthentication;
