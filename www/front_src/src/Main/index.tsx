import { lazy, useEffect, Suspense } from 'react';

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
import { and, equals, isNil, not } from 'ramda';
import { Route, Routes, useLocation, useNavigate } from 'react-router-dom';
import { useAtomValue } from 'jotai/utils';
import { useAtom } from 'jotai';

import reactRoutes from '../reactRoutes/routeMap';
import AuthenticationDenied from '../FallbackPages/AuthenticationDenied';

import { platformInstallationStatusAtom } from './atoms/platformInstallationStatusAtom';
import Provider from './Provider';
import { MainLoaderWithoutTranslation } from './MainLoader';
import useMain from './useMain';
import { areUserParametersLoadedAtom } from './useUser';

dayjs.extend(localizedFormat);
dayjs.extend(utcPlugin);
dayjs.extend(timezonePlugin);
dayjs.extend(isToday);
dayjs.extend(isYesterday);
dayjs.extend(weekday);
dayjs.extend(isBetween);
dayjs.extend(isSameOrBefore);
dayjs.extend(duration);

const LoginPage = lazy(() => import('../Login'));
const ResetPasswordPage = lazy(() => import('../ResetPassword'));

const AppPage = lazy(() => import('./InitializationPage'));

const Main = (): JSX.Element => {
  const navigate = useNavigate();
  const { pathname } = useLocation();

  useMain();

  const [areUserParametersLoaded] = useAtom(areUserParametersLoadedAtom);
  const platformInstallationStatus = useAtomValue(
    platformInstallationStatusAtom,
  );

  const navigateTo = (path: string): void => {
    navigate(path);
    window.location.reload();
  };

  useEffect(() => {
    if (isNil(platformInstallationStatus) || isNil(areUserParametersLoaded)) {
      return;
    }

    if (not(platformInstallationStatus.isInstalled)) {
      navigateTo(reactRoutes.install);

      return;
    }

    const canUpgrade = and(
      platformInstallationStatus.hasUpgradeAvailable,
      not(areUserParametersLoaded),
    );

    if (canUpgrade) {
      navigateTo(reactRoutes.upgrade);

      return;
    }

    if (
      not(areUserParametersLoaded) &&
      !equals(pathname, reactRoutes.authenticationDenied)
    ) {
      navigate(reactRoutes.login);
    }
  }, [platformInstallationStatus, areUserParametersLoaded]);

  if (isNil(platformInstallationStatus)) {
    return <MainLoaderWithoutTranslation />;
  }

  return (
    <Suspense fallback={<MainLoaderWithoutTranslation />}>
      <Routes>
        <Route
          element={<AuthenticationDenied />}
          path={reactRoutes.authenticationDenied}
        />
        <Route element={<LoginPage />} path={reactRoutes.login} />
        <Route
          element={<ResetPasswordPage />}
          path={reactRoutes.resetPassword}
        />
        <Route element={<AppPage />} path="*" />
      </Routes>
    </Suspense>
  );
};

export default (): JSX.Element => (
  <Provider>
    <Main />
  </Provider>
);
