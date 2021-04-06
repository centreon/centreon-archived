import * as React from 'react';

import routeMap from './route-map';

const reactRoutes = [
  {
    comp: React.lazy(() => import('../route-components/pollerStep1')),
    path: routeMap.pollerStep1,
  },
  {
    comp: React.lazy(() => import('../route-components/pollerStep2')),
    path: routeMap.pollerStep2,
  },
  {
    comp: React.lazy(() => import('../route-components/pollerStep3')),
    path: routeMap.pollerStep3,
  },
  {
    comp: React.lazy(() => import('../route-components/remoteServerStep1')),
    path: routeMap.remoteServerStep1,
  },
  {
    comp: React.lazy(() => import('../route-components/remoteServerStep2')),
    path: routeMap.remoteServerStep2,
  },
  {
    comp: React.lazy(() => import('../route-components/remoteServerStep3')),
    path: routeMap.remoteServerStep3,
  },
  {
    comp: React.lazy(() =>
      import('../route-components/serverConfigurationWizard'),
    ),
    path: routeMap.serverConfigurationWizard,
  },
  {
    comp: React.lazy(() =>
      import('../route-components/administration/extensions/manager'),
    ),
    path: routeMap.extensionsManagerPage,
  },
  {
    comp: React.lazy(() => import('../route-components/notAllowedPage')),
    path: routeMap.notAllowedPage,
  },
  {
    comp: React.lazy(() => import('../Resources')),
    path: routeMap.resources,
  },
];

export default reactRoutes;
