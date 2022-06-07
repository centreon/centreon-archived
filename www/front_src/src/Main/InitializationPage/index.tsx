import { lazy, Suspense } from 'react';

import { isNil, not } from 'ramda';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';

import { userAtom } from '@centreon/ui-context';

import { platformInstallationStatusAtom } from '../../platformInstallationStatusAtom';
import PageLoader from '../../components/PageLoader';
import { MainLoader } from '../MainLoader';
import { areUserParametersLoadedAtom } from '../useUser';

const App = lazy(() => import('../../App'));

const InitializationPage = (): JSX.Element => {
  const [areUserParametersLoaded] = useAtom(areUserParametersLoadedAtom);
  const user = useAtomValue(userAtom);
  const platformInstallationStatus = useAtomValue(
    platformInstallationStatusAtom,
  );

  const canDisplayApp =
    not(isNil(platformInstallationStatus)) &&
    not(isNil(user)) &&
    areUserParametersLoaded;

  if (not(canDisplayApp)) {
    return <MainLoader />;
  }

  return (
    <Suspense fallback={<PageLoader />}>
      <App />
    </Suspense>
  );
};

export default InitializationPage;
