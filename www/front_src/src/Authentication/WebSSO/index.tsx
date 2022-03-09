import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, not } from 'ramda';

import { Container, LinearProgress, Paper, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

import { labelDefineWebSSOConfiguration } from './translatedLabels';
import useWebSSO from './useWebSSO';
import Form from './Form';
import { WebSSOConfiguration } from './models';
import LoadingSkeletonForm from './Form/LoadingSkeleton';

const useStyles = makeStyles((theme) => ({
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

const WebSSOConfigurationForm = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    sendingGetWebSSOConfiguration,
    initialWebSSOConfiguration,
    loadWebSSOonfiguration,
  } = useWebSSO();

  const isWebSSOConfigurationEmpty = React.useMemo(
    () => isNil(initialWebSSOConfiguration),
    [initialWebSSOConfiguration],
  );

  React.useEffect(() => {
    loadWebSSOonfiguration();
  }, []);

  return (
    <Container className={classes.container}>
      <Paper className={classes.paper}>
        <Typography variant="h4">
          {t(labelDefineWebSSOConfiguration)}
        </Typography>
        <div className={classes.loading}>
          {not(isWebSSOConfigurationEmpty) && sendingGetWebSSOConfiguration && (
            <LinearProgress />
          )}
        </div>
        {isWebSSOConfigurationEmpty ? (
          <LoadingSkeletonForm />
        ) : (
          <Form
            initialValues={initialWebSSOConfiguration as WebSSOConfiguration}
            loadWebSSOonfiguration={loadWebSSOonfiguration}
          />
        )}
      </Paper>
    </Container>
  );
};

export default WebSSOConfigurationForm;
