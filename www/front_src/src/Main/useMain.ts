import { useEffect } from 'react';

import i18next, { Resource, ResourceLanguage } from 'i18next';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';
import {
  and,
  includes,
  isEmpty,
  isNil,
  mergeAll,
  not,
  or,
  pipe,
  reduce,
  toPairs,
} from 'ramda';
import { initReactI18next } from 'react-i18next';
import { useLocation, useNavigate, useSearchParams } from 'react-router-dom';

import { getData, useRequest, useSnackbar } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { platformInstallationStatusDecoder } from '../api/decoders';
import { webVersionsEndpoint } from '../api/endpoint';
import { PlatformInstallationStatus } from '../api/models';
import { translationEndpoint } from '../App/endpoint';
import reactRoutes from '../reactRoutes/routeMap';
import useFederatedComponents from '../federatedComponents/useFederatedComponents';

import { platformInstallationStatusAtom } from './atoms/platformInstallationStatusAtom';
import useUser, { areUserParametersLoadedAtom } from './useUser';
import usePlatformVersions from './usePlatformVersions';

const useMain = (): void => {
  const { sendRequest: getWebVersions } =
    useRequest<PlatformInstallationStatus>({
      decoder: platformInstallationStatusDecoder,
      request: getData,
    });
  const { sendRequest: getTranslations } = useRequest<ResourceLanguage>({
    httpCodesBypassErrorSnackbar: [500],
    request: getData,
  });
  const { showErrorMessage } = useSnackbar();

  const [platformInstallationStatus, setPlatformInstallationStatus] = useAtom(
    platformInstallationStatusAtom,
  );
  const user = useAtomValue(userAtom);
  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);

  const loadUser = useUser(i18next.changeLanguage);
  const location = useLocation();
  const navigate = useNavigate();
  const [searchParameter] = useSearchParams();
  const { getPlatformVersions } = usePlatformVersions();
  useFederatedComponents();

  const getBrowserLocale = (): string => navigator.language.slice(0, 2);

  const initializeI18n = (retrievedTranslations?: ResourceLanguage): void => {
    i18next.use(initReactI18next).init({
      fallbackLng: 'en',
      keySeparator: false,
      lng: getBrowserLocale(),
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

  const displayAuthenticationError = (): void => {
    const authenticationError = searchParameter.get('authenticationError');

    if (or(isNil(authenticationError), isEmpty(authenticationError))) {
      return;
    }

    showErrorMessage(authenticationError as string);
  };

  useEffect(() => {
    displayAuthenticationError();

    getPlatformVersions();

    getTranslations({
      endpoint: translationEndpoint,
    })
      .then((retrievedTranslations) => {
        initializeI18n(retrievedTranslations);
      })
      .catch(() => {
        initializeI18n();
      })
      .finally(() => {
        getWebVersions({
          endpoint: webVersionsEndpoint,
        }).then((retrievedWebVersions) => {
          setPlatformInstallationStatus(retrievedWebVersions);
        });
      });
  }, []);

  useEffect((): void => {
    if (isNil(platformInstallationStatus)) {
      return;
    }

    loadUser();
  }, [platformInstallationStatus]);

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
