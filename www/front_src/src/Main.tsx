import * as React from 'react';

import { isNil, not, pathEq } from 'ramda';
import { useNavigate, Routes, Route } from 'react-router-dom';
import { useAtom } from 'jotai';

import { Typography } from '@material-ui/core';

import { getData, useRequest, withSnackbar } from '@centreon/ui';
import { User, userAtom } from '@centreon/ui-context';

import Provider from './Provider';
import { webVersionsDecoder, userDecoder } from './api/decoders';
import { userEndpoint, webVersionsEndpoint } from './api/endpoint';
import reactRoutes from './reactRoutes/routeMap';
import { WebVersions } from './api/models';

const App = React.lazy(() => import('./App'));
const LoginPage = React.lazy(() => import('./Login'));

const MainContent = (): JSX.Element => {
  const [webVersions, setWebVersions] = React.useState<WebVersions | null>(
    null,
  );
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
    Promise.all([
      getWebVersions({
        endpoint: webVersionsEndpoint,
      }),
      loadUser(),
    ])
      .then(([retrievedWebVersions, retrievedUser]) => {
        setWebVersions(retrievedWebVersions);
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
    return <Typography>Loading...</Typography>;
  }

  return (
    <React.Suspense fallback={<Typography>sdhssddsisdsdssds</Typography>}>
      <App
        areTranslationsLoaded={areTranslationsLoaded}
        changeAreTranslationsLoaded={changeAreTranslationsLoaded}
      />
    </React.Suspense>
  );
};

const Main = (): JSX.Element => (
  <Provider>
    <React.Suspense fallback={<Typography>Loading...</Typography>}>
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
