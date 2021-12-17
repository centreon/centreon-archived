import React from 'react';

import { useNavigate } from 'react-router-dom';
import { FormikHelpers, FormikValues } from 'formik';
import { not, replace } from 'ramda';
import { useAtom } from 'jotai';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';

import { useRequest, useSnackbar } from '@centreon/ui';

import { areUserParametersLoadedAtom } from '../Main/mainAtom';
import { WebVersions } from '../api/models';
import { webVersionsAtom } from '../webVersionsAtom';
import useUser from '../Main/useUser';

import postLogin from './api';
import { redirectDecoder } from './api/decoder';
import { LoginFormValues, Redirect } from './models';
import { labelLoginFailed, labelLoginSucceeded } from './translatedLabels';

interface UseLoginState {
  sending: boolean;
  submitLoginForm: (
    values: LoginFormValues,
    { setSubmitting }: Pick<FormikHelpers<FormikValues>, 'setSubmitting'>,
  ) => void;
  webVersions: WebVersions | null;
}

const useLogin = (): UseLoginState => {
  const { t, i18n } = useTranslation();
  const [loggingIn, setLoggingIn] = React.useState(false);

  const { sendRequest, sending } = useRequest<Redirect>({
    decoder: redirectDecoder,
    request: postLogin,
    showErrorOnPermissionDenied: false,
  });

  const { showSuccessMessage, showErrorMessage } = useSnackbar();
  const navigate = useNavigate();
  const loadUser = useUser(i18n.changeLanguage);

  const [webVersions] = useAtom(webVersionsAtom);

  const submitLoginForm = (
    values: LoginFormValues,
    { setSubmitting },
  ): void => {
    setLoggingIn(true);
    sendRequest({
      login: values.alias,
      password: values.password,
    })
      .then(({ redirectUri }) => {
        showSuccessMessage(t(labelLoginSucceeded));
        loadUser()?.then(() => navigate(replace('/centreon', '', redirectUri)));
      })
      .catch(() => {
        showErrorMessage(t(labelLoginFailed));
        setSubmitting(false);
        setLoggingIn(false);
      });
  };

  return {
    sending,
    submitLoginForm,
    webVersions,
  };
};

export default useLogin;
