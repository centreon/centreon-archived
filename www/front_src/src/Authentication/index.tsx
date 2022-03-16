import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';
import { useUpdateAtom } from 'jotai/utils';

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
import {
  labelActivation,
  labelClientAddresses,
  labelIdentityProvider,
} from './translatedLabels';
import { Category } from './FormInputs/models';
import { tabAtom, appliedTabAtom } from './tabAtoms';

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

export const categories: Array<Category> = [
  {
    name: labelActivation,
    order: 1,
  },
  {
    name: labelIdentityProvider,
    order: 2,
  },
  {
    name: labelClientAddresses,
    order: 3,
  },
];

const useStyles = makeStyles((theme) => ({
  box: {
    overflowY: 'hidden',
  },
  container: {
    marginBottom: theme.spacing(9),
    maxWidth: theme.spacing(120),
  },
  panel: {
    height: '100%',
    overflowY: 'auto',
  },
  paper: {
    padding: theme.spacing(4),
  },
}));

const Authentication = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const appliedTab = useAtomValue(appliedTabAtom);
  const setTab = useUpdateAtom(tabAtom);

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
        <TabPanel className={classes.panel} key={value} value={value}>
          <Component />
        </TabPanel>
      )),
    [],
  );

  return (
    <Box className={classes.box}>
      <TabContext value={appliedTab}>
        <Container className={classes.container}>
          <Paper>
            <TabList variant="fullWidth" onChange={changeTab}>
              {tabs}
            </TabList>
            {tabPanels}
          </Paper>
        </Container>
      </TabContext>
    </Box>
  );
};

export default Authentication;
