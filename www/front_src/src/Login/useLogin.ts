import { useNavigate } from 'react-router-dom';
import { FormikHelpers, FormikValues } from 'formik';
import { replace } from 'ramda';

import { useRequest, useSnackbar } from '@centreon/ui';

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
}

const useLogin = (): UseLoginState => {
  const { sendRequest, sending } = useRequest<Redirect>({
    decoder: redirectDecoder,
    request: postLogin,
  });
  const { showSuccessMessage, showErrorMessage } = useSnackbar();
  const navigate = useNavigate();

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

  return {
    sending,
    submitLoginForm,
  };
};

export default useLogin;
