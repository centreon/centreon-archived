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
import { equals, isNil, mergeAll, not, pipe, reduce, toPairs } from 'ramda';
import i18next, { Resource, ResourceLanguage } from 'i18next';
import { initReactI18next } from 'react-i18next';
import { useAtom } from 'jotai';
import { Route, Routes, useLocation, useNavigate } from 'react-router-dom';
import { useAtomValue } from 'jotai/utils';

import { getData, useRequest, withSnackbar } from '@centreon/ui';

import { webVersionsDecoder } from '../api/decoders';
import { WebVersions } from '../api/models';
import { webVersionsEndpoint } from '../api/endpoint';
import { webVersionsAtom } from '../webVersionsAtom';
import { translationEndpoint } from '../App/endpoint';
import reactRoutes from '../reactRoutes/routeMap';

import Provider from './Provider';
import MainLoader from './MainLoader';
import useUser, { areUserParametersLoadedAtom } from './useUser';

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

const Main = (): JSX.Element => {
  const { sendRequest: getWebVersions } = useRequest<WebVersions>({
    decoder: webVersionsDecoder,
    request: getData,
  });
  const { sendRequest: getTranslations } = useRequest<ResourceLanguage>({
    request: getData,
  });

  const [webVersions, setWebVersions] = useAtom(webVersionsAtom);
  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);

  const loadUser = useUser(i18next.changeLanguage);
  const location = useLocation();
  const navigate = useNavigate();

  const getLocale = (): string => navigator.language.slice(0, 2);

  const initializeI18n = (retrievedTranslations): void => {
    i18next.use(initReactI18next).init({
      fallbackLng: 'en',
      keySeparator: false,
      lng: getLocale(),
      nsSeparator: false,
      resources: pipe(
        toPairs as (t) => Array<[string, ResourceLanguage]>,
        reduce(
          (acc, [language, values]) =>
            mergeAll([acc, { [language]: { translation: values } }]),
          {},
        ),
      )(retrievedTranslations) as Resource,
    });
  };

  React.useEffect(() => {
    Promise.all([
      getWebVersions({
        endpoint: webVersionsEndpoint,
      }),
      getTranslations({
        endpoint: translationEndpoint,
      }),
    ]).then(([retrievedWebVersions, retrievedTranslations]) => {
      setWebVersions(retrievedWebVersions);
      loadUser();
      initializeI18n(retrievedTranslations);
    });
  }, []);

  React.useEffect(() => {
    if (isNil(areUserParametersLoaded) && i18next.isInitialized) {
      i18next?.changeLanguage(getLocale());
    }
    if (
      not(areUserParametersLoaded) ||
      not(equals(location.pathname, reactRoutes.login))
    ) {
      return;
    }

    navigate('/monitoring/resources');
  }, [location, areUserParametersLoaded]);

  if (isNil(webVersions)) {
    return <MainLoader />;
  }

  return (
    <React.Suspense fallback={<MainLoader />}>
      <Routes>
        <Route element={<LoginPage />} path={reactRoutes.login} />
        <Route element={<MainContent />} path="*" />
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
