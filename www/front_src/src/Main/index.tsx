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
import duration from 'dayjs/plugin/duration';
import { isNil } from 'ramda';
import { Route, Routes } from 'react-router-dom';
import { useAtomValue } from 'jotai/utils';

import { platformInstallationStatusAtom } from '../platformInstallationStatusAtom';
import reactRoutes from '../reactRoutes/routeMap';

import Provider from './Provider';
import { MainLoaderWithoutTranslation } from './MainLoader';
import useMain from './useMain';

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
dayjs.extend(isToday);
dayjs.extend(isYesterday);
dayjs.extend(weekday);
dayjs.extend(isBetween);
dayjs.extend(isSameOrBefore);
dayjs.extend(duration);

const LoginPage = React.lazy(() => import('../Login'));

const AppPage = React.lazy(() => import('./InitializationPage'));

const Main = (): JSX.Element => {
  useMain();

  const platformInstallationStatus = useAtomValue(
    platformInstallationStatusAtom,
  );

  if (isNil(platformInstallationStatus)) {
    return <MainLoaderWithoutTranslation />;
  }

  return (
    <React.Suspense fallback={<MainLoaderWithoutTranslation />}>
      <Routes>
        <Route element={<LoginPage />} path={reactRoutes.login} />
        <Route element={<AppPage />} path="*" />
      </Routes>
    </React.Suspense>
  );
};

export default (): JSX.Element => (
  <Provider>
    <Main />
  </Provider>
);
