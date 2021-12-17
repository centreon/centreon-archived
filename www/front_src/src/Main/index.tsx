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
import { isNil, not, pathEq } from 'ramda';
import { useNavigate, Routes, Route } from 'react-router-dom';
import { useAtom } from 'jotai';

import { getData, useRequest, withSnackbar } from '@centreon/ui';
import { User, userAtom } from '@centreon/ui-context';

import { webVersionsDecoder, userDecoder } from '../api/decoders';
import { userEndpoint, webVersionsEndpoint } from '../api/endpoint';
import reactRoutes from '../reactRoutes/routeMap';
import { WebVersions } from '../api/models';
import { webVersionsAtom } from '../webVersionsAtom';

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

const App = React.lazy(() => import('../App'));
const LoginPage = React.lazy(() => import('../Login'));

const MainContent = (): JSX.Element => {
  const [webVersionsLoaded, setWebVersionsLoaded] = React.useState(false);
  const [isUserDisconnected, setIsUserDisconnected] = React.useState<
    boolean | null
  >(null);
  const [areTranslationsLoaded, setAreTranslationsLoaded] =
    React.useState(false);

  const navigate = useNavigate();
  const { sendRequest: getUser } = useRequest<User>({
    decoder: userDecoder,
    request: getData,
    showErrorOnPermissionDenied: false,
  });
  const { sendRequest: getWebVersions, sending: sendingWebVersions } =
    useRequest<WebVersions>({
      decoder: webVersionsDecoder,
      request: getData,
    });

  const [user, setUser] = useAtom(userAtom);
  const [webVersions, setWebVersions] = useAtom(webVersionsAtom);

  const changeAreTranslationsLoaded = (loaded): void => {
    setAreTranslationsLoaded(loaded);
  };

  const loadUser = (): Promise<void | User> =>
    getUser({
      endpoint: userEndpoint,
    }).catch((error) => {
      if (
        pathEq(['response', 'status'], 403)(error) ||
        pathEq(['response', 'status'], 401)(error)
      ) {
        setIsUserDisconnected(true);
      }
    });

  React.useEffect(() => {
    Promise.all<void | User, boolean | WebVersions>([
      loadUser(),
      isNil(webVersions) &&
        getWebVersions({
          endpoint: webVersionsEndpoint,
        }),
    ])
      .then(([retrievedUser, retrievedWebVersions]) => {
        if (isNil(webVersions)) {
          setWebVersions(retrievedWebVersions as WebVersions);
        }
        setWebVersionsLoaded(true);

        if (isNil(retrievedUser)) {
          return;
        }

        const {
          alias,
          isExportButtonEnabled,
          locale,
          name,
          timezone,
          use_deprecated_pages: useDeprecatedPages,
        } = retrievedUser as User;

        setUser({
          alias,
          isExportButtonEnabled,
          locale: locale || 'en',
          name,
          timezone,
          use_deprecated_pages: useDeprecatedPages,
        });
        setIsUserDisconnected(false);
      })
      .catch(() => undefined);
  }, []);

  React.useEffect(() => {
    if (
      isNil(webVersions) ||
      not(webVersionsLoaded) ||
      isNil(isUserDisconnected)
    ) {
      return;
    }

    if (isNil(webVersions.installedVersion)) {
      navigate(reactRoutes.install);

      return;
    }

    if (not(isNil(webVersions.availableVersion))) {
      navigate(reactRoutes.upgrade);
    }

    if (isUserDisconnected) {
      navigate(reactRoutes.login);
    }
  }, [webVersions, webVersionsLoaded, isUserDisconnected]);

  if (
    sendingWebVersions ||
    isNil(webVersions) ||
    isNil(user) ||
    isUserDisconnected
  ) {
    return <MainLoader />;
  }

  return (
    <React.Suspense fallback={<MainLoader />}>
      <App
        areTranslationsLoaded={areTranslationsLoaded}
        changeAreTranslationsLoaded={changeAreTranslationsLoaded}
      />
    </React.Suspense>
  );
};

const Main = (): JSX.Element => (
  <Provider>
    <React.Suspense fallback={<MainLoader allowTransition />}>
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
