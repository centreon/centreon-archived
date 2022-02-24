import * as React from 'react';

import i18next, { Resource, ResourceLanguage } from 'i18next';
import { useAtom } from 'jotai';
import { useAtomValue } from 'jotai/utils';
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

  const [webVersions, setWebVersions] = useAtom(platformInstallationStatusAtom);
  const user = useAtomValue(userAtom);
  const areUserParametersLoaded = useAtomValue(areUserParametersLoadedAtom);

  const loadUser = useUser(i18next.changeLanguage);
  const location = useLocation();
  const navigate = useNavigate();

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

  React.useEffect(() => {
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
          setWebVersions(retrievedWebVersions);
        });
      });
  }, []);

  React.useEffect((): void => {
    if (isNil(webVersions)) {
      return;
    }

    loadUser(webVersions);
  }, [webVersions]);

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

    navigate(user.default_page as string);
  }, [location, areUserParametersLoaded, user]);
};

export default useMain;
