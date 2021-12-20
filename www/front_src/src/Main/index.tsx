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
import { isNil } from 'ramda';
import { Route, Routes } from 'react-router-dom';
import { useAtomValue } from 'jotai/utils';

import { withSnackbar } from '@centreon/ui';

import { webVersionsAtom } from '../webVersionsAtom';
import reactRoutes from '../reactRoutes/routeMap';

import Provider from './Provider';
import MainLoader from './MainLoader';
import useMain from './useMain';

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
dayjs.extend(isToday);
dayjs.extend(isYesterday);
dayjs.extend(weekday);
dayjs.extend(isBetween);
dayjs.extend(isSameOrBefore);

const LoginPage = React.lazy(() => import('../Login'));

const AppPage = React.lazy(() => import('./AppPage'));

const Main = (): JSX.Element => {
  useMain();

  const webVersions = useAtomValue(webVersionsAtom);

  if (isNil(webVersions)) {
    return <MainLoader />;
  }

  return (
    <React.Suspense fallback={<MainLoader />}>
      <Routes>
        <Route element={<LoginPage />} path={reactRoutes.login} />
        <Route element={<AppPage />} path="*" />
      </Routes>
    </React.Suspense>
  );
};

const MainWithSnackbar = withSnackbar({
  Component: Main,
});

export default (): JSX.Element => (
  <Provider>
    <MainWithSnackbar />
  </Provider>
);
