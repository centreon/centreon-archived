import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil, not } from 'ramda';

import { Container, LinearProgress, Paper, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

import { labelDefineOpenIDConnectConfiguration } from './translatedLabels';
import useOpenid from './useOpenid';
import Form from './Form';
import { OpenidConfiguration } from './models';

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

const OpenidConfiguration = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    sendingGetOpenidConfiguration,
    initialOpenidConfiguration,
    loadOpenidConfiguration,
  } = useOpenid();

  const isOpenidConfigurationEmpty = React.useMemo(
    () => isNil(initialOpenidConfiguration),
    [initialOpenidConfiguration],
  );

  React.useEffect(() => {
    loadOpenidConfiguration();
  }, []);

  return (
    <Container className={classes.container}>
      <Paper className={classes.paper}>
        <Typography variant="h4">
          {t(labelDefineOpenIDConnectConfiguration)}
        </Typography>
        <div className={classes.loading}>
          {not(isOpenidConfigurationEmpty) && sendingGetOpenidConfiguration && (
            <LinearProgress />
          )}
        </div>
        {isOpenidConfigurationEmpty ? (
          <p>Loading...</p>
        ) : (
          <Form
            initialValues={initialOpenidConfiguration as OpenidConfiguration}
            loadOpenidConfiguration={loadOpenidConfiguration}
          />
        )}
      </Paper>
    </Container>
  );
};

export default OpenidConfiguration;
