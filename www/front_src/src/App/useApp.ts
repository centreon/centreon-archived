import * as React from 'react';

import { useUpdateAtom } from 'jotai/utils';
import { equals, not, pathEq } from 'ramda';
import { useTranslation } from 'react-i18next';
import { useSearchParams, useNavigate } from 'react-router-dom';

import {
  acknowledgementAtom,
  aclAtom,
  Actions,
  downtimeAtom,
  refreshIntervalAtom,
} from '@centreon/ui-context';
import { getData, useRequest, useSnackbar, postData } from '@centreon/ui';

import useExternalComponents from '../externalComponents/useExternalComponents';
import useNavigation from '../Navigation/useNavigation';
import reactRoutes from '../reactRoutes/routeMap';
import { logoutEndpoint } from '../api/endpoint';
import { areUserParametersLoadedAtom } from '../Main/useUser';

import { aclEndpoint, parametersEndpoint } from './endpoint';
import { DefaultParameters } from './models';
import { labelYouAreDisconnected } from './translatedLabels';
import usePendo from './usePendo';

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
  const { t } = useTranslation();

  const [dataLoaded, setDataLoaded] = React.useState(false);
  const [isFullscreenEnabled, setIsFullscreenEnabled] = React.useState(false);
  const keepAliveIntervalRef = React.useRef<NodeJS.Timer | null>(null);
  usePendo();

  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const { showErrorMessage } = useSnackbar();

  const { sendRequest: keepAliveRequest } = useRequest({
    httpCodesBypassErrorSnackbar: [401],
    request: getData,
  });
  const { sendRequest: getParameters } = useRequest<DefaultParameters>({
    request: getData,
  });
  const { sendRequest: getAcl } = useRequest<Actions>({
    request: getData,
  });

  const { sendRequest: logoutRequest } = useRequest({
    request: postData,
  });

  const setDowntime = useUpdateAtom(downtimeAtom);
  const setRefreshInterval = useUpdateAtom(refreshIntervalAtom);
  const setAcl = useUpdateAtom(aclAtom);
  const setAcknowledgement = useUpdateAtom(acknowledgementAtom);
  const setAreUserParametersLoaded = useUpdateAtom(areUserParametersLoadedAtom);

  const { getNavigation } = useNavigation();
  const { getExternalComponents } = useExternalComponents();

  const logout = (): void => {
    setAreUserParametersLoaded(false);
    logoutRequest({
      data: {},
      endpoint: logoutEndpoint,
    }).then(() => {
      showErrorMessage(t(labelYouAreDisconnected));
      navigate(reactRoutes.login);
    });
  };

  React.useEffect(() => {
    getNavigation();
    getExternalComponents();

    Promise.all([
      getParameters({
        endpoint: parametersEndpoint,
      }),
      getAcl({
        endpoint: aclEndpoint,
      }),
    ])
      .then(([retrievedParameters, retrievedAcl]) => {
        setDowntime({
          duration: parseInt(
            retrievedParameters.monitoring_default_downtime_duration,
            10,
          ),
          fixed: false,
          with_services: false,
        });
        setRefreshInterval(
          parseInt(retrievedParameters.monitoring_default_refresh_interval, 10),
        );
        setAcl({ actions: retrievedAcl });
        setAcknowledgement({
          force_active_checks: false,
          notify: false,
          persistent:
            retrievedParameters.monitoring_default_acknowledgement_persistent,
          sticky: retrievedParameters.monitoring_default_acknowledgement_sticky,
          with_services: false,
        });

        setDataLoaded(true);
      })
      .catch((error) => {
        if (pathEq(['response', 'status'], 401)(error)) {
          logout();
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
      logout();

      clearInterval(keepAliveIntervalRef.current as NodeJS.Timer);
    });
  };

  React.useEffect(() => {
    keepAlive();

    keepAliveIntervalRef.current = setInterval(keepAlive, 15000);

    return (): void => {
      clearInterval(keepAliveIntervalRef.current as NodeJS.Timer);
    };
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
