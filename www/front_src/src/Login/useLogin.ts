import { useNavigate } from 'react-router-dom';
import { FormikHelpers, FormikValues } from 'formik';
import { replace } from 'ramda';
import { useAtom } from 'jotai';
import { useTranslation } from 'react-i18next';

import { useRequest, useSnackbar } from '@centreon/ui';

import { WebVersions } from '../api/models';
import { webVersionsAtom } from '../webVersionsAtom';
import useUser from '../Main/useUser';

import postLogin from './api';
import { redirectDecoder } from './api/decoder';
import { LoginFormValues, Redirect } from './models';
import { labelLoginSucceeded } from './translatedLabels';

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

  const { sendRequest, sending } = useRequest<Redirect>({
    decoder: redirectDecoder,
    request: postLogin,
  });

  const { showSuccessMessage } = useSnackbar();
  const navigate = useNavigate();
  const loadUser = useUser(i18n.changeLanguage);

  const [webVersions] = useAtom(webVersionsAtom);

  const submitLoginForm = (
    values: LoginFormValues,
    { setSubmitting },
  ): void => {
    sendRequest({
      login: values.alias,
      password: values.password,
    })
      .then(({ redirectUri }) => {
        showSuccessMessage(t(labelLoginSucceeded));
        loadUser()?.then(() => navigate(replace('/centreon', '', redirectUri)));
      })
      .catch(() => undefined)
      .finally(() => {
        setSubmitting(false);
      });
  };

  return {
    sending,
    submitLoginForm,
    webVersions,
  };
};

export default useLogin;
