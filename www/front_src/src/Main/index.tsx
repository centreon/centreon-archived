import * as React from 'react';

import 'dayjs/locale/en';
import 'dayjs/locale/pt';
import 'dayjs/locale/fr';
import 'dayjs/locale/es';
import dayjs from 'dayjs';
import timezonePlugin from 'dayjs/plugin/timezone';
import utcPlugin from 'dayjs/plugin/utc';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import isToday from 'dayjs/plugin/isToday';
import isYesterday from 'dayjs/plugin/isYesterday';
import weekday from 'dayjs/plugin/weekday';
import isBetween from 'dayjs/plugin/isBetween';
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore';
import { Routes, Route } from 'react-router-dom';

import { withSnackbar } from '@centreon/ui';

import reactRoutes from '../reactRoutes/routeMap';

import Provider from './Provider';
import MainLoader from './MainLoader';

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
dayjs.extend(isToday);
dayjs.extend(isYesterday);
dayjs.extend(weekday);
dayjs.extend(isBetween);
dayjs.extend(isSameOrBefore);

const LoginPage = React.lazy(() => import('../Login'));

const MainContent = React.lazy(() => import('./Content'));

const Main = (): JSX.Element => (
  <Provider>
    <React.Suspense fallback={<MainLoader />}>
      <Routes>
        <Route element={<LoginPage />} path={reactRoutes.login} />
        <Route element={<MainContent />} path="*" />
      </Routes>
    </React.Suspense>
  </Provider>
);

export default withSnackbar({
  Component: Main,
});
