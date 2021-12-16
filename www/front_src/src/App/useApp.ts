import * as React from 'react';

import i18next, { Resource, ResourceLanguage } from 'i18next';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { equals, mergeAll, not, pathEq, pipe, reduce, toPairs } from 'ramda';
import { initReactI18next } from 'react-i18next';
import { useSearchParams, useNavigate } from 'react-router-dom';

import {
  acknowledgementAtom,
  aclAtom,
  Actions,
  downtimeAtom,
  refreshIntervalAtom,
  userAtom,
} from '@centreon/ui-context';
import { getData, useRequest } from '@centreon/ui';

import useExternalComponents from '../externalComponents/useExternalComponents';
import useNavigation from '../Navigation/useNavigation';
import reactRoutes from '../reactRoutes/routeMap';

import {
  aclEndpoint,
  parametersEndpoint,
  translationEndpoint,
} from './endpoint';
import { DefaultParameters } from './models';

const keepAliveEndpoint =
  './api/internal.php?object=centreon_keepalive&action=keepAlive';

interface UseAppState {
  dataLoaded: boolean;
  displayInFullScreen: () => void;
  hasMinArgument: () => boolean;
  isFullscreenEnabled: boolean;
  removeFullscreen: () => void;
}

const useApp = (): UseAppState => {
  const [dataLoaded, setDataLoaded] = React.useState(false);
  const [isFullscreenEnabled, setIsFullscreenEnabled] = React.useState(false);
  const keepAliveIntervalRef = React.useRef<NodeJS.Timer | null>(null);

  const { sendRequest: keepAliveRequest } = useRequest({
    request: getData,
  });

  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const { sendRequest: getParameters } = useRequest<DefaultParameters>({
    request: getData,
  });
  const { sendRequest: getTranslations } = useRequest<ResourceLanguage>({
    request: getData,
  });
  const { sendRequest: getAcl } = useRequest<Actions>({
    request: getData,
  });

  const user = useAtomValue(userAtom);
  const setDowntime = useUpdateAtom(downtimeAtom);
  const setRefreshInterval = useUpdateAtom(refreshIntervalAtom);
  const setAcl = useUpdateAtom(aclAtom);
  const setAcknowledgement = useUpdateAtom(acknowledgementAtom);

  const { getNavigation } = useNavigation();
  const { getExternalComponents } = useExternalComponents();

  const initializeI18n = (retrievedTranslations): void => {
    const locale = (user.locale || navigator.language)?.slice(0, 2);

    i18next.use(initReactI18next).init({
      fallbackLng: 'en',
      keySeparator: false,
      lng: locale,
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
    getNavigation();
    getExternalComponents();
    Promise.all([
      getParameters({
        endpoint: parametersEndpoint,
      }),
      getTranslations({
        endpoint: translationEndpoint,
      }),
      getAcl({
        endpoint: aclEndpoint,
      }),
    ])
      .then(([retrievedParameters, retrievedTranslations, retrievedAcl]) => {
        setDowntime({
          default_duration: parseInt(
            retrievedParameters.monitoring_default_downtime_duration,
            10,
          ),
          default_fixed: false,
          default_with_services: false,
        });
        setRefreshInterval(
          parseInt(retrievedParameters.monitoring_default_refresh_interval, 10),
        );
        setAcl({ actions: retrievedAcl });
        setAcknowledgement({
          persistent:
            retrievedParameters.monitoring_default_acknowledgement_persistent,
          sticky: retrievedParameters.monitoring_default_acknowledgement_sticky,
        });

        initializeI18n(retrievedTranslations);

        setDataLoaded(true);
      })
      .catch((error) => {
        if (pathEq(['response', 'status'], 401)(error)) {
          navigate(reactRoutes.login);
        }
      });
  }, []);

  const hasMinArgument = (): boolean => equals(searchParams.get('min'), '1');

  const displayInFullScreen = (): void => {
    setIsFullscreenEnabled(true);
  };

  const removeFullscreen = (): void => {
    setIsFullscreenEnabled(false);
  };

  const keepAlive = (): void => {
    keepAliveRequest({
      endpoint: keepAliveEndpoint,
    }).catch((error) => {
      if (not(pathEq(['response', 'status'], 401, error))) {
        return;
      }

      clearInterval(keepAliveIntervalRef.current as NodeJS.Timer);
      navigate(reactRoutes.login);
    });
  };

  React.useEffect(() => {
    keepAlive();

    keepAliveIntervalRef.current = setInterval(keepAlive, 15000);
  }, []);

  return {
    dataLoaded,
    displayInFullScreen,
    hasMinArgument,
    isFullscreenEnabled,
    removeFullscreen,
  };
};

export default useApp;
