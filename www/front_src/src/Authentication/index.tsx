import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Box, Paper, Tab } from '@mui/material';
import { TabContext, TabList, TabPanel } from '@mui/lab';

import { useMemoComponent } from '@centreon/ui';

import { Provider } from './models';
import LocalAuthentication from './Local';
import { labelPasswordSecurityPolicy } from './Local/translatedLabels';
import { labelOpenIDConnectConfiguration } from './Openid/translatedLabels';

const Authentication = (): JSX.Element => {
  const { t } = useTranslation();
  const [tab, setTab] = React.useState(Provider.Local);

  const changeTab = (_, newTab: Provider): void => {
    setTab(newTab);
  };

  return (
    <Box>
      <TabContext value={tab}>
        <Paper>
          <TabList variant="fullWidth" onChange={changeTab}>
            <Tab
              label={t(labelPasswordSecurityPolicy)}
              value={Provider.Local}
            />
            <Tab
              label={t(labelOpenIDConnectConfiguration)}
              value={Provider.Openid}
            />
          </TabList>
        </Paper>
        <TabPanel value={Provider.Local}>
          {useMemoComponent({
            Component: <LocalAuthentication />,
            memoProps: [],
          })}
        </TabPanel>
        <TabPanel value={Provider.Openid}>OpenID Connect</TabPanel>
      </TabContext>
    </Box>
  );
};

export default Authentication;
