import { useState, useRef, useEffect } from 'react';

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
  hasMinArgument: () => boolean;
}

const useApp = (): UseAppState => {
  const { t } = useTranslation();

  const [dataLoaded, setDataLoaded] = useState(false);
  const keepAliveIntervalRef = useRef<NodeJS.Timer | null>(null);
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

  useEffect(() => {
    getNavigation();

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
          fixed: retrievedParameters.monitoring_default_downtime_fixed,
          with_services:
            retrievedParameters.monitoring_default_downtime_with_services,
        });
        setRefreshInterval(
          parseInt(retrievedParameters.monitoring_default_refresh_interval, 10),
        );
        setAcl({ actions: retrievedAcl });
        setAcknowledgement({
          force_active_checks:
            retrievedParameters.monitoring_default_acknowledgement_force_active_checks,
          notify: retrievedParameters.monitoring_default_acknowledgement_notify,
          persistent:
            retrievedParameters.monitoring_default_acknowledgement_persistent,
          sticky: retrievedParameters.monitoring_default_acknowledgement_sticky,
          with_services:
            retrievedParameters.monitoring_default_acknowledgement_with_services,
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

  useEffect(() => {
    keepAlive();

    keepAliveIntervalRef.current = setInterval(keepAlive, 15000);

    return (): void => {
      clearInterval(keepAliveIntervalRef.current as NodeJS.Timer);
    };
  }, []);

  return {
    dataLoaded,
    hasMinArgument,
  };
};

export default useApp;
