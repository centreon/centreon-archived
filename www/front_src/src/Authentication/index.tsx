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
import passwordPadlockLogo from './logos/passwordPadlock.svg';
import providerPadlockLogo from './logos/providerPadlock.svg';

const panels = [
  {
    Component: LocalAuthentication,
    image: passwordPadlockLogo,
    title: labelPasswordSecurityPolicy,
    value: Provider.Local,
  },
  {
    Component: OpenidConfiguration,
    image: providerPadlockLogo,
    title: labelOpenIDConnectConfiguration,
    value: Provider.Openid,
  },
  {
    Component: WebSSOConfigurationForm,
    image: providerPadlockLogo,
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
    height: '100%',
    maxWidth: theme.spacing(120),
    overflowY: 'hidden',
  },
  formContainer: {
    display: 'grid',
    gridTemplateColumns: '1.2fr 0.6fr',
    height: '100%',
    overflowY: 'auto',
    padding: theme.spacing(3),
  },
  image: {
    opacity: 0.5,
    padding: theme.spacing(0, 5),
    position: 'sticky',
    top: 0,
  },
  panel: {
    height: '88%',
    padding: 0,
  },
  paper: {
    boxShadow: theme.shadows[3],
    height: '100%',
  },
  tabList: {
    boxShadow: theme.shadows[2],
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
      panels.map(({ Component, value, image }) => (
        <TabPanel className={classes.panel} key={value} value={value}>
          <div className={classes.formContainer}>
            <Component />
            <img alt="padlock" className={classes.image} src={image} />
          </div>
        </TabPanel>
      )),
    [],
  );

  return (
    <Box className={classes.box}>
      <TabContext value={appliedTab}>
        <Container className={classes.container}>
          <Paper className={classes.paper}>
            <TabList
              className={classes.tabList}
              variant="fullWidth"
              onChange={changeTab}
            >
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
