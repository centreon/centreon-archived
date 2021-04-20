import * as React from 'react';

import pollerStep1 from '../route-components/pollerStep1';
import pollerStep2 from '../route-components/pollerStep2';
import pollerStep3 from '../route-components/pollerStep3';
import remoteServerStep1 from '../route-components/remoteServerStep1';
import remoteServerStep2 from '../route-components/remoteServerStep2';
import remoteServerStep3 from '../route-components/remoteServerStep3';
import ressources from '../Resources';
import serverConfigurationWizard from '../route-components/serverConfigurationWizard';
import manager from '../route-components/administration/extensions/manager';
import notAllowedPage from '../route-components/notAllowedPage';

import routeMap from './route-map';

const reactRoutes = [
  {
    comp: pollerStep1,
    path: routeMap.pollerStep1,
  },
  {
    comp: pollerStep2,
    path: routeMap.pollerStep2,
  },
  {
    comp: pollerStep3,
    path: routeMap.pollerStep3,
  },
  {
    comp: remoteServerStep1,
    path: routeMap.remoteServerStep1,
  },
  {
    comp: remoteServerStep2,
    path: routeMap.remoteServerStep2,
  },
  {
    comp: remoteServerStep3,
    path: routeMap.remoteServerStep3,
  },
  {
    comp: serverConfigurationWizard,
    path: routeMap.serverConfigurationWizard,
  },
  {
    comp: manager,
    path: routeMap.extensionsManagerPage,
  },
  {
    comp: notAllowedPage,
    path: routeMap.notAllowedPage,
  },
  {
    comp: ressources,
    path: routeMap.resources,
  },
];

export default reactRoutes;
