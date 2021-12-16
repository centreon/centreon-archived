import * as React from 'react';

import routeMap from './routeMap';

const reactRoutes = [
  {
    comp: React.lazy(() => import('../route-components/pollerWizard')),
    path: routeMap.pollerWizard,
  },
  {
    comp: React.lazy(() => import('../Extensions')),
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
