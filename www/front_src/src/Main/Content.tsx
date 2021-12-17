import * as React from 'react';

import { isNil, not, pathEq } from 'ramda';
import { useNavigate } from 'react-router-dom';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { getData, useRequest } from '@centreon/ui';
import { User, userAtom } from '@centreon/ui-context';

import { userDecoder } from '../api/decoders';
import { userEndpoint } from '../api/endpoint';
import { webVersionsAtom } from '../webVersionsAtom';
import reactRoutes from '../reactRoutes/routeMap';
import PageLoader from '../components/PageLoader';

const App = React.lazy(() => import('../App'));

const MainContent = (): JSX.Element => {
  const { i18n } = useTranslation();
  const [isUserDisconnected, setIsUserDisconnected] = React.useState<
    boolean | null
  >(null);

  const navigate = useNavigate();
  const { sendRequest: getUser } = useRequest<User>({
    decoder: userDecoder,
    request: getData,
    showErrorOnPermissionDenied: false,
  });

  const [user, setUser] = useAtom(userAtom);
  const webVersions = useAtomValue(webVersionsAtom);

  const changeLanguage = (locale: string): void => {
    i18n.changeLanguage(locale.slice(0, 2));
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
    loadUser().then((retrievedUser) => {
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
      changeLanguage((retrievedUser as User).locale);
      setIsUserDisconnected(false);
    });
  }, []);

  React.useEffect(() => {
    if (isNil(webVersions) || isNil(isUserDisconnected)) {
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
  }, [webVersions, isUserDisconnected]);

  if (
    isNil(webVersions) ||
    isNil(user) ||
    isUserDisconnected ||
    isNil(webVersions.installedVersion) ||
    not(isNil(webVersions.availableVersion))
  ) {
    return <PageLoader />;
  }

  return (
    <React.Suspense fallback={<PageLoader />}>
      <App />
    </React.Suspense>
  );
};

export default MainContent;
