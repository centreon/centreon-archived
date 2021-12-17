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
import { getData, useRequest, useSnackbar } from '@centreon/ui';

import useExternalComponents from '../externalComponents/useExternalComponents';
import useNavigation from '../Navigation/useNavigation';
import reactRoutes from '../reactRoutes/routeMap';

import { aclEndpoint, parametersEndpoint } from './endpoint';
import { DefaultParameters } from './models';
import { labelYouAreDisconnected } from './translatedLabels';

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

  const navigate = useNavigate();
  const [searchParams] = useSearchParams();

  const { showErrorMessage } = useSnackbar();

  const { sendRequest: keepAliveRequest } = useRequest({
    request: getData,
    showErrorOnPermissionDenied: false,
  });
  const { sendRequest: getParameters } = useRequest<DefaultParameters>({
    request: getData,
  });
  const { sendRequest: getAcl } = useRequest<Actions>({
    request: getData,
  });

  const setDowntime = useUpdateAtom(downtimeAtom);
  const setRefreshInterval = useUpdateAtom(refreshIntervalAtom);
  const setAcl = useUpdateAtom(aclAtom);
  const setAcknowledgement = useUpdateAtom(acknowledgementAtom);

  const { getNavigation } = useNavigation();
  const { getExternalComponents } = useExternalComponents();

  React.useEffect(() => {
    getNavigation();
    getExternalComponents();

    Promise.all<DefaultParameters, Actions>([
      getParameters({
        endpoint: parametersEndpoint,
      }),
      getAcl({
        endpoint: aclEndpoint,
      }),
    ])
      .then(([retrievedParameters, retrievedAcl]) => {
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
      showErrorMessage(t(labelYouAreDisconnected));

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
