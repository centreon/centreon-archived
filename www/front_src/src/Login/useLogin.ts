import { useState, useCallback, useEffect } from 'react';

import { useNavigate } from 'react-router-dom';
import { FormikHelpers, FormikValues } from 'formik';
import { useAtom } from 'jotai';
import { useTranslation } from 'react-i18next';
import {
  filter,
  isEmpty,
  isNil,
  not,
  propEq,
  reject,
  path,
  pathEq,
  equals,
} from 'ramda';
import { useUpdateAtom } from 'jotai/utils';

import { useRequest, useSnackbar, getData } from '@centreon/ui';

import { PlatformInstallationStatus } from '../api/models';
import { platformInstallationStatusAtom } from '../Main/atoms/platformInstallationStatusAtom';
import useUser from '../Main/useUser';
import { passwordResetInformationsAtom } from '../ResetPassword/passwordResetInformationsAtom';
import routeMap from '../reactRoutes/routeMap';
import useInitializeTranslation from '../Main/useInitializeTranslation';

import postLogin from './api';
import { providersConfigurationDecoder, redirectDecoder } from './api/decoder';
import {
  labelLoginSucceeded,
  labelPasswordHasExpired,
} from './translatedLabels';
import { providersConfigurationEndpoint } from './api/endpoint';
import {
  LoginFormValues,
  Redirect,
  RedirectAPI,
  ProviderConfiguration,
} from './models';

interface UseLoginState {
  platformInstallationStatus: PlatformInstallationStatus | null;
  providersConfiguration: Array<ProviderConfiguration> | null;
  sendLogin: (values) => Promise<Redirect>;
  submitLoginForm: (
    values: LoginFormValues,
    { setSubmitting }: Pick<FormikHelpers<FormikValues>, 'setSubmitting'>,
  ) => void;
}

const useLogin = (): UseLoginState => {
  const { t, i18n } = useTranslation();
  const [providersConfiguration, setProvidersConfiguration] =
    useState<Array<ProviderConfiguration> | null>(null);

  const { sendRequest: sendLogin } = useRequest<Redirect>({
    decoder: redirectDecoder,
    httpCodesBypassErrorSnackbar: [401],
    request: postLogin,
  });

  const { sendRequest: getProvidersConfiguration } = useRequest<
    Array<ProviderConfiguration>
  >({
    decoder: providersConfigurationDecoder,
    request: getData,
  });

  const { getInternalTranslation, getExternalTranslation } =
    useInitializeTranslation();

  const { showSuccessMessage, showWarningMessage, showErrorMessage } =
    useSnackbar();
  const navigate = useNavigate();
  const loadUser = useUser();

  const [platformInstallationStatus] = useAtom(platformInstallationStatusAtom);
  const setPasswordResetInformations = useUpdateAtom(
    passwordResetInformationsAtom,
  );

  const checkPasswordExpiration = useCallback(
    ({ error, alias, setSubmitting }) => {
      const isUserNotAllowed = pathEq(['response', 'status'], 401, error);

      const { password_is_expired: passwordIsExpired } = path(
        ['response', 'data'],
        error,
      ) as RedirectAPI;

      if (isUserNotAllowed && passwordIsExpired) {
        setPasswordResetInformations({
          alias,
        });
        navigate(routeMap.resetPassword);
        showWarningMessage(t(labelPasswordHasExpired));

        return;
      }

      setSubmitting(false);
      showErrorMessage(path(['response', 'data', 'message'], error) as string);
    },
    [],
  );

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
        getInternalTranslation().finally(() =>
          loadUser()?.then(() => navigate(redirectUri)),
        );
      })
      .catch((error) =>
        checkPasswordExpiration({ alias: values.alias, error, setSubmitting }),
      );
  };

  const getBrowserLocale = (): string => navigator.language.slice(0, 2);

  useEffect(() => {
    getExternalTranslation().then(() =>
      i18n.changeLanguage?.(getBrowserLocale()),
    );

    getProvidersConfiguration({
      endpoint: providersConfigurationEndpoint,
    }).then((providers) => {
      const forcedProviders = filter<ProviderConfiguration>(
        (provider): boolean =>
          not(isNil(provider.isForced)) &&
          (provider.isForced as boolean) &&
          not(equals(provider.name, 'local')),
        providers || [],
      );

      if (not(isEmpty(forcedProviders))) {
        window.location.replace(forcedProviders[0].authenticationUri);

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
    providersConfiguration,
    sendLogin,
    submitLoginForm,
  };
};

export default useLogin;
