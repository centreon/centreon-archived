import React from 'react';

import { useNavigate } from 'react-router-dom';
import { FormikHelpers, FormikValues } from 'formik';
import { useAtom } from 'jotai';
import { useTranslation } from 'react-i18next';
import { not, path, pathEq } from 'ramda';
import { useUpdateAtom } from 'jotai/utils';

import { useRequest, useSnackbar, getData } from '@centreon/ui';

import { PlatformInstallationStatus } from '../api/models';
import { platformInstallationStatusAtom } from '../platformInstallationStatusAtom';
import useUser from '../Main/useUser';
import { passwordResetInformationsAtom } from '../ResetPassword/passwordResetInformationsAtom';
import routeMap from '../reactRoutes/routeMap';

import postLogin from './api';
import { platformVersionsDecoder, redirectDecoder } from './api/decoder';
import {
  LoginFormValues,
  PlatformVersions,
  Redirect,
  RedirectAPI,
} from './models';
import {
  labelLoginSucceeded,
  labelPasswordHasExpired,
} from './translatedLabels';
import { platformVersionsEndpoint } from './api/endpoint';

interface UseLoginState {
  platformInstallationStatus: PlatformInstallationStatus | null;
  platformVersions: PlatformVersions | null;
  sendLogin: (values) => Promise<Redirect>;
  submitLoginForm: (
    values: LoginFormValues,
    { setSubmitting }: Pick<FormikHelpers<FormikValues>, 'setSubmitting'>,
  ) => void;
}

const useLogin = (): UseLoginState => {
  const { t, i18n } = useTranslation();
  const [platformVersions, setPlatformVersions] =
    React.useState<PlatformVersions | null>(null);

  const { sendRequest: sendLogin } = useRequest<Redirect>({
    decoder: redirectDecoder,
    httpCodesBypassErrorSnackbar: [401],
    request: postLogin,
  });

  const { sendRequest: sendPlatformVersions } = useRequest<PlatformVersions>({
    decoder: platformVersionsDecoder,
    request: getData,
  });

  const { showSuccessMessage, showWarningMessage, showErrorMessage } =
    useSnackbar();
  const navigate = useNavigate();
  const loadUser = useUser(i18n.changeLanguage);

  const [platformInstallationStatus] = useAtom(platformInstallationStatusAtom);
  const setPasswordResetInformations = useUpdateAtom(
    passwordResetInformationsAtom,
  );

  const checkPasswordExpiration = React.useCallback(
    ({ error, alias, setSubmitting }) => {
      const isUserNotAllowed = pathEq(['response', 'status'], 401, error);

      const { password_is_expired: passwordIsExpired } = path(
        ['response', 'data'],
        error,
      ) as RedirectAPI;

      if (isUserNotAllowed && not(passwordIsExpired)) {
        setSubmitting(false);
        showErrorMessage(
          path(['response', 'data', 'message'], error) as string,
        );

        return;
      }

      setPasswordResetInformations({
        alias,
      });
      navigate(routeMap.resetPassword);
      showWarningMessage(t(labelPasswordHasExpired));
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
        loadUser(platformInstallationStatus)?.then(() => navigate(redirectUri));
      })
      .catch((error) =>
        checkPasswordExpiration({ alias: values.alias, error, setSubmitting }),
      );
  };

  const getBrowserLocale = (): string => navigator.language.slice(0, 2);

  React.useEffect(() => {
    i18n.changeLanguage?.(getBrowserLocale());
    sendPlatformVersions({
      endpoint: platformVersionsEndpoint,
    }).then(setPlatformVersions);
  }, []);

  return {
    platformInstallationStatus,
    platformVersions,
    sendLogin,
    submitLoginForm,
  };
};

export default useLogin;
