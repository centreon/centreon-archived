import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, not } from 'ramda';

import {
  Paper,
  Theme,
  Typography,
  LinearProgress,
  Container,
} from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { labelDefinePasswordPasswordSecurityPolicy } from './translatedLabels';
import useAuthentication from './useAuthentication';
import Form from './Form';
import { PasswordSecurityPolicy } from './models';
import LoadingSkeleton from './LoadingSkeleton';

const useStyles = makeStyles((theme: Theme) => ({
  container: {
    width: 'fit-content',
  },
  loading: {
    height: theme.spacing(0.5),
  },
  paper: {
    padding: theme.spacing(2),
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

  const isPasswordSecurityPolicyEmpty = React.useMemo(
    () => isNil(initialPasswordPasswordSecurityPolicy),
    [initialPasswordPasswordSecurityPolicy],
  );

  return (
    <Container className={classes.container}>
      <Paper className={classes.paper}>
        <Typography variant="h4">
          {t(labelDefinePasswordPasswordSecurityPolicy)}
        </Typography>
        <div className={classes.loading}>
          {not(isPasswordSecurityPolicyEmpty) &&
            sendingGetPasswordPasswordSecurityPolicy && <LinearProgress />}
        </div>
        {isPasswordSecurityPolicyEmpty ? (
          <LoadingSkeleton />
        ) : (
          <Form
            initialValues={
              initialPasswordPasswordSecurityPolicy as PasswordSecurityPolicy
            }
            loadPasswordPasswordSecurityPolicy={
              loadPasswordPasswordSecurityPolicy
            }
          />
        )}
      </Paper>
    </Container>
  );
};

export default LocalAuthentication;
