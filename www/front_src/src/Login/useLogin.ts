import React from 'react';

import { useNavigate } from 'react-router-dom';
import { FormikHelpers, FormikValues } from 'formik';
import { useAtom } from 'jotai';
import { useTranslation } from 'react-i18next';
import { filter, head, isEmpty, isNil, not, or, propEq, reject } from 'ramda';

import { useRequest, useSnackbar, getData } from '@centreon/ui';

import { PlatformInstallationStatus } from '../api/models';
import { platformInstallationStatusAtom } from '../platformInstallationStatusAtom';
import useUser from '../Main/useUser';

import postLogin from './api';
import {
  platformVersionsDecoder,
  providersConfigurationDecoder,
  redirectDecoder,
} from './api/decoder';
import {
  LoginFormValues,
  PlatformVersions,
  ProviderConfiguration,
  Redirect,
} from './models';
import { labelLoginSucceeded } from './translatedLabels';
import {
  platformVersionsEndpoint,
  providersConfigurationEndpoint,
} from './api/endpoint';

interface UseLoginState {
  platformInstallationStatus: PlatformInstallationStatus | null;
  platformVersions: PlatformVersions | null;
  providersConfiguration: Array<ProviderConfiguration> | null;
  submitLoginForm: (
    values: LoginFormValues,
    { setSubmitting }: Pick<FormikHelpers<FormikValues>, 'setSubmitting'>,
  ) => void;
}

const useLogin = (): UseLoginState => {
  const { t, i18n } = useTranslation();
  const [platformVersions, setPlatformVersions] =
    React.useState<PlatformVersions | null>(null);
  const [providersConfiguration, setProvidersConfiguration] =
    React.useState<Array<ProviderConfiguration> | null>(null);

  const { sendRequest: sendLogin } = useRequest<Redirect>({
    decoder: redirectDecoder,
    request: postLogin,
  });

  const { sendRequest: sendPlatformVersions } = useRequest<PlatformVersions>({
    decoder: platformVersionsDecoder,
    request: getData,
  });

  const { sendRequest: getProvidersConfiguration } = useRequest<
    Array<ProviderConfiguration>
  >({
    decoder: providersConfigurationDecoder,
    request: getData,
  });

  const { showSuccessMessage } = useSnackbar();
  const navigate = useNavigate();
  const loadUser = useUser(i18n.changeLanguage);

  const [platformInstallationStatus] = useAtom(platformInstallationStatusAtom);

  const submitLoginForm = (
    values: LoginFormValues,
    { setSubmitting },
  ): void => {
    sendLogin({
      login: values.alias,
      password: values.password,
    })
      .then(({ redirectUri }) => {
        showSuccessMessage(t(labelLoginSucceeded));
        loadUser(platformInstallationStatus)?.then(() => navigate(redirectUri));
      })
      .catch(() => undefined)
      .finally(() => {
        setSubmitting(false);
      });
  };

  const getBrowserLocale = (): string => navigator.language.slice(0, 2);

  React.useEffect(() => {
    i18n.changeLanguage?.(getBrowserLocale());

    sendPlatformVersions({
      endpoint: platformVersionsEndpoint,
    }).then(setPlatformVersions);

    getProvidersConfiguration({
      endpoint: providersConfigurationEndpoint,
    }).then((providers) => {
      const forcedProviders = reject<ProviderConfiguration>(
        (provider): boolean =>
          or(isNil(provider.isForced), not(provider.isForced)),
        providers || [],
      );

      if (not(isEmpty(forcedProviders))) {
        window.location.replace(head(forcedProviders).authenticationUri);

        return;
      }

      const externalProviders = reject<ProviderConfiguration>(
        propEq('name', 'local'),
        providers,
      );

      const activeProviders = filter<ProviderConfiguration>(
        propEq('isActive', true),
        externalProviders || [],
      );

      setProvidersConfiguration(activeProviders);
    });
  }, []);

  return {
    platformInstallationStatus,
    platformVersions,
    providersConfiguration,
    submitLoginForm,
  };
};

export default useLogin;
