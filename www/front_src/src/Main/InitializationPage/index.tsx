import * as React from 'react';

import { and, isNil, not } from 'ramda';
import { useNavigate } from 'react-router-dom';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';

import { userAtom } from '@centreon/ui-context';

import { webVersionsAtom } from '../../webVersionsAtom';
import reactRoutes from '../../reactRoutes/routeMap';
import PageLoader from '../../components/PageLoader';
import MainLoader from '../MainLoader';
import { areUserParametersLoadedAtom } from '../useUser';

const App = React.lazy(() => import('../../App'));

const InitializationPage = (): JSX.Element => {
  const navigate = useNavigate();

  const [areUserParametersLoaded] = useAtom(areUserParametersLoadedAtom);
  const user = useAtomValue(userAtom);
  const webVersions = useAtomValue(webVersionsAtom);

  const navigateTo = (path: string): void => {
    navigate(path);
    window.location.reload();
  };

  React.useEffect(() => {
    if (isNil(webVersions) || isNil(areUserParametersLoaded)) {
      return;
    }

    if (not(webVersions.isInstalled)) {
      navigateTo(reactRoutes.install);

      return;
    }

    const canUpgrade = and(
      webVersions.hasUpgradeAvailable,
      not(areUserParametersLoaded),
    );

    if (canUpgrade) {
      navigateTo(reactRoutes.upgrade);

      return;
    }

    if (not(areUserParametersLoaded)) {
      navigate(reactRoutes.login);
    }
  }, [webVersions, areUserParametersLoaded]);

  const canDisplayApp =
    not(isNil(webVersions)) && not(isNil(user)) && areUserParametersLoaded;

  if (not(canDisplayApp)) {
    return <MainLoader />;
  }

  return (
    <React.Suspense fallback={<PageLoader />}>
      <App />
    </React.Suspense>
  );
};

export default InitializationPage;
