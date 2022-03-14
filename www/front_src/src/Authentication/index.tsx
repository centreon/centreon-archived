import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Box, Container, Paper, Tab } from '@mui/material';
import { TabContext, TabList, TabPanel } from '@mui/lab';
import { makeStyles } from '@mui/styles';

import { Provider } from './models';
import LocalAuthentication from './Local';
import { labelPasswordSecurityPolicy } from './Local/translatedLabels';
import { labelOpenIDConnectConfiguration } from './Openid/translatedLabels';
import OpenidConfiguration from './Openid';
import WebSSOConfigurationForm from './WebSSO';
import { labelWebSSOConfiguration } from './WebSSO/translatedLabels';

const panels = [
  {
    Component: LocalAuthentication,
    title: labelPasswordSecurityPolicy,
    value: Provider.Local,
  },
  {
    Component: OpenidConfiguration,
    title: labelOpenIDConnectConfiguration,
    value: Provider.Openid,
  },
  {
    Component: WebSSOConfigurationForm,
    title: labelWebSSOConfiguration,
    value: Provider.WebSSO,
  },
];

const useStyles = makeStyles((theme) => ({
  container: {
    maxWidth: theme.spacing(90),
  },
  paper: {
    padding: theme.spacing(4),
  },
}));

const Authentication = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const [tab, setTab] = React.useState(Provider.Local);

  const changeTab = (_, newTab: Provider): void => {
    setTab(newTab);
  };

  const tabs = React.useMemo(
    () =>
      panels.map(({ title, value }) => (
        <Tab key={value} label={t(title)} value={value} />
      )),
    [],
  );

  const tabPanels = React.useMemo(
    () =>
      panels.map(({ Component, value }) => (
        <TabPanel key={value} value={value}>
          <Container className={classes.container}>
            <Paper className={classes.paper}>
              <Component />
            </Paper>
          </Container>
        </TabPanel>
      )),
    [],
  );

  return (
    <Box>
      <TabContext value={tab}>
        <Paper>
          <TabList variant="fullWidth" onChange={changeTab}>
            {tabs}
          </TabList>
        </Paper>
        {tabPanels}
      </TabContext>
    </Box>
  );
};

export default Authentication;
