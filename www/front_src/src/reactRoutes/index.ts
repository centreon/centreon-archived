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
    comp: React.lazy(() => import('../NotAllowedPage')),
    path: routeMap.notAllowedPage,
  },
  {
    comp: React.lazy(() => import('../Resources')),
    path: routeMap.resources,
  },
  {
    comp: React.lazy(() => import('../Authentication')),
    path: routeMap.authentication,
  },
  {
    comp: React.lazy(() => import('../ResetPassword')),
    path: routeMap.resetPassword,
  },
];

export default reactRoutes;
