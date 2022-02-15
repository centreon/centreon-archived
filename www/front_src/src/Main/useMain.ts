import * as React from 'react';

import i18next, { Resource, ResourceLanguage } from 'i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import {
  and,
  includes,
  isNil,
  mergeAll,
  not,
  pipe,
  reduce,
  toPairs,
} from 'ramda';
import { initReactI18next } from 'react-i18next';
import { useLocation, useNavigate } from 'react-router';

import { getData, useRequest } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { webVersionsDecoder } from '../api/decoders';
import { webVersionsEndpoint } from '../api/endpoint';
import { PlatformInstallationStatus } from '../api/models';
import { translationEndpoint } from '../App/endpoint';
import reactRoutes from '../reactRoutes/routeMap';
import { platformInstallationStatusAtom } from '../platformInstallationStatusAtom';

import useUser, { areUserParametersLoadedAtom } from './useUser';

const useMain = (): void => {
  const { sendRequest: getWebVersions } =
    useRequest<PlatformInstallationStatus>({
      decoder: webVersionsDecoder,
      request: getData,
    });
  const { sendRequest: getTranslations } = useRequest<ResourceLanguage>({
    request: getData,
  });

  const user = useAtomValue(userAtom);
  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);
  const setWebVersions = useUpdateAtom(platformInstallationStatusAtom);

  const loadUser = useUser(i18next.changeLanguage);
  const location = useLocation();
  const navigate = useNavigate();

  const getBrowserLocale = (): string => navigator.language.slice(0, 2);

  const initializeI18n = (retrievedTranslations): void => {
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

    navigate(user.defaultPage as string);
  }, [location, areUserParametersLoaded, user]);
};

export default useMain;
