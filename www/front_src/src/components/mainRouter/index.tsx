import * as React from 'react';

import { Routes, Route } from 'react-router-dom';

import { PageSkeleton } from '@centreon/ui';

import LegacyRoute from '../../route-components/legacyRoute';

const ReactRouter = React.lazy(() => import('../ReactRouter'));

const MainRouter = (): JSX.Element => (
  <React.Suspense fallback={<PageSkeleton />}>
    <Routes>
      <Route element={<LegacyRoute />} path="/main.php/*" />
      <Route element={<ReactRouter />} path="/*" />
    </Routes>
  </React.Suspense>
);

export default MainRouter;
