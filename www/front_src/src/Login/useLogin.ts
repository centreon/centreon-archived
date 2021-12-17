import React from 'react';

import { useNavigate } from 'react-router-dom';
import { FormikHelpers, FormikValues } from 'formik';
import { replace } from 'ramda';
import { useAtom } from 'jotai';

import { getData, useRequest, useSnackbar } from '@centreon/ui';

import { WebVersions } from '../api/models';
import { webVersionsDecoder } from '../api/decoders';
import { webVersionsEndpoint } from '../api/endpoint';
import { webVersionsAtom } from '../webVersionsAtom';

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
  const { sendRequest, sending } = useRequest<Redirect>({
    decoder: redirectDecoder,
    request: postLogin,
  });
  const { sendRequest: getWebVersions } = useRequest<WebVersions>({
    decoder: webVersionsDecoder,
    request: getData,
  });

  const { showSuccessMessage, showErrorMessage } = useSnackbar();
  const navigate = useNavigate();

  const [webVersions, setWebVersions] = useAtom(webVersionsAtom);

  const submitLoginForm = (
    values: LoginFormValues,
    { setSubmitting },
  ): void => {
    sendRequest({
      login: values.alias,
      password: values.password,
    })
      .then(({ redirectUri }) => {
        showSuccessMessage(labelLoginSucceeded);
        // window.location.href = redirectUri;
        navigate(replace('/centreon', '', redirectUri));
      })
      .catch(() => {
        showErrorMessage(labelLoginFailed);
        setSubmitting(false);
      });
  };

  const loadWebVersions = (): void => {
    getWebVersions({
      endpoint: webVersionsEndpoint,
    }).then((retrievedWebVersions) => {
      setWebVersions(retrievedWebVersions);
    });
  };

  React.useEffect(() => {
    loadWebVersions();
  }, []);

  return {
    sending,
    submitLoginForm,
    webVersions,
  };
};

export default useLogin;
