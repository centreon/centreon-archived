import * as React from 'react';

import { isNil, not } from 'ramda';
import { useNavigate } from 'react-router-dom';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';

import { userAtom } from '@centreon/ui-context';

import { webVersionsAtom } from '../webVersionsAtom';
import reactRoutes from '../reactRoutes/routeMap';
import PageLoader from '../components/PageLoader';

import MainLoader from './MainLoader';
import { areUserParametersLoadedAtom } from './useUser';

const App = React.lazy(() => import('../App'));

const MainContent = (): JSX.Element => {
  const navigate = useNavigate();

  const [areUserParametersLoaded] = useAtom(areUserParametersLoadedAtom);
  const user = useAtomValue(userAtom);
  const webVersions = useAtomValue(webVersionsAtom);

  React.useEffect(() => {
    if (isNil(webVersions) || isNil(areUserParametersLoaded)) {
      return;
    }

    if (isNil(webVersions.installedVersion)) {
      navigate(reactRoutes.install);

      return;
    }

    if (not(isNil(webVersions.availableVersion))) {
      navigate(reactRoutes.upgrade);
    }

    if (not(areUserParametersLoaded)) {
      navigate(reactRoutes.login);
    }
  }, [webVersions, areUserParametersLoaded]);

  if (
    isNil(webVersions) ||
    isNil(user) ||
    isNil(areUserParametersLoaded) ||
    not(areUserParametersLoaded) ||
    isNil(webVersions.installedVersion) ||
    not(isNil(webVersions.availableVersion))
  ) {
    return <MainLoader />;
  }

  return (
    <React.Suspense fallback={<PageLoader />}>
      <App />
    </React.Suspense>
  );
};

export default MainContent;
