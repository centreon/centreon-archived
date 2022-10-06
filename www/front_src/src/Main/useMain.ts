import { useEffect } from 'react';

import { useSetAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';
import { and, includes, isEmpty, isNil, not, or } from 'ramda';
import { useLocation, useNavigate, useSearchParams } from 'react-router-dom';

import { getData, useRequest, useSnackbar } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { platformInstallationStatusDecoder } from '../api/decoders';
import { platformInstallationStatusEndpoint } from '../api/endpoint';
import { PlatformInstallationStatus } from '../api/models';
import reactRoutes from '../reactRoutes/routeMap';
import useFederatedModules from '../federatedModules/useFederatedModules';

import { platformInstallationStatusAtom } from './atoms/platformInstallationStatusAtom';
import useUser, { areUserParametersLoadedAtom } from './useUser';
import usePlatformVersions from './usePlatformVersions';
import useInitializeTranslation from './useInitializeTranslation';

const useMain = (): void => {
  const { sendRequest: getPlatformInstallationStatus } =
    useRequest<PlatformInstallationStatus>({
      decoder: platformInstallationStatusDecoder,
      request: getData,
    });
  const { showErrorMessage } = useSnackbar();

  const { getBrowserLocale, getInternalTranslation, i18next } =
    useInitializeTranslation();

  const setPlatformInstallationStatus = useSetAtom(
    platformInstallationStatusAtom,
  );
  const user = useAtomValue(userAtom);
  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);

  const loadUser = useUser();
  const location = useLocation();
  const navigate = useNavigate();
  const [searchParameter] = useSearchParams();
  const { getPlatformVersions } = usePlatformVersions();
  useFederatedModules();

  const displayAuthenticationError = (): void => {
    const authenticationError = searchParameter.get('authenticationError');

    if (or(isNil(authenticationError), isEmpty(authenticationError))) {
      return;
    }

    showErrorMessage(authenticationError as string);
  };

  useEffect(() => {
    displayAuthenticationError();

    getPlatformInstallationStatus({
      endpoint: platformInstallationStatusEndpoint,
    }).then((retrievedPlatformInstallationStatus) => {
      setPlatformInstallationStatus(retrievedPlatformInstallationStatus);
      getPlatformVersions();
    });
  }, []);

  useEffect((): void => {
    loadUser();
  }, []);

  useEffect((): void => {
    if (not(areUserParametersLoaded)) {
      return;
    }

    getInternalTranslation();
  }, [areUserParametersLoaded]);

  useEffect(() => {
    const canChangeToBrowserLanguage = and(
      isNil(areUserParametersLoaded),
      i18next.isInitialized,
    );
    if (canChangeToBrowserLanguage) {
      i18next?.changeLanguage(getBrowserLocale());
    }

    const canRedirectToUserDefaultPage = and(
      areUserParametersLoaded,
      includes(location.pathname, [reactRoutes.login, '/']),
    );

    if (not(canRedirectToUserDefaultPage)) {
      return;
    }

    navigate(user.default_page as string);
  }, [location, areUserParametersLoaded, user]);
};

export default useMain;
