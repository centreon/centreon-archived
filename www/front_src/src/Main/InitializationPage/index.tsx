import * as React from 'react';

import { and, isNil, not } from 'ramda';
import { useNavigate } from 'react-router-dom';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';

import { userAtom } from '@centreon/ui-context';

import { platformInstallationStatusAtom } from '../../platformInstallationStatusAtom';
import reactRoutes from '../../reactRoutes/routeMap';
import PageLoader from '../../components/PageLoader';
import { MainLoader } from '../MainLoader';
import { areUserParametersLoadedAtom } from '../useUser';

const App = React.lazy(() => import('../../App'));

const InitializationPage = (): JSX.Element => {
  const navigate = useNavigate();

  const [areUserParametersLoaded] = useAtom(areUserParametersLoadedAtom);
  const user = useAtomValue(userAtom);
  const platformInstallationStatus = useAtomValue(
    platformInstallationStatusAtom,
  );

  const navigateTo = (path: string): void => {
    navigate(path);
    window.location.reload();
  };

  React.useEffect(() => {
    if (isNil(platformInstallationStatus)) {
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

    if (isNil(areUserParametersLoaded)) {
      return;
    }

    if (not(areUserParametersLoaded)) {
      navigate(reactRoutes.login);
    }
  }, [platformInstallationStatus, areUserParametersLoaded]);

  const canDisplayApp =
    not(isNil(platformInstallationStatus)) &&
    not(isNil(user)) &&
    areUserParametersLoaded;

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
