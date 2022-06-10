import { FormikHelpers, FormikValues } from 'formik';
import { equals, not } from 'ramda';
import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';
import { useAtomValue } from 'jotai/utils';
import { useNavigate } from 'react-router-dom';

import { putData, useRequest, useSnackbar } from '@centreon/ui';

import useUser from '../Main/useUser';
import useLogin from '../Login/useLogin';
import { labelLoginSucceeded } from '../Login/translatedLabels';

import { ResetPasswordValues } from './models';
import {
  labelNewPasswordsMustMatch,
  labelPasswordRenewed,
  labelRequired,
  labelTheNewPasswordIstheSameAsTheOldPassword,
} from './translatedLabels';
import { getResetPasswordEndpoint } from './api/endpoint';
import { passwordResetInformationsAtom } from './passwordResetInformationsAtom';

interface UseResetPasswordState {
  submitResetPassword: (
    values: ResetPasswordValues,
    { setSubmitting }: Pick<FormikHelpers<FormikValues>, 'setSubmitting'>,
  ) => void;
  validationSchema: Yup.SchemaOf<ResetPasswordValues>;
}

function matchNewPasswords(this, newConfirmationPassword?: string): boolean {
  return equals(newConfirmationPassword, this.parent.newPassword);
}

function differentPasswords(this, newPassword?: string): boolean {
  return not(equals(newPassword, this.parent.oldPassword));
}

const useResetPassword = (): UseResetPasswordState => {
  const { t } = useTranslation();
  const navigate = useNavigate();

  const { showSuccessMessage } = useSnackbar();
  const { sendRequest } = useRequest({
    request: putData,
  });

  const passwordResetInformations = useAtomValue(passwordResetInformationsAtom);

  const loadUser = useUser();
  const { sendLogin } = useLogin();

  const submitResetPassword = (
    values: ResetPasswordValues,
    { setSubmitting }: Pick<FormikHelpers<FormikValues>, 'setSubmitting'>,
  ): void => {
    sendRequest({
      data: {
        new_password: values.newPassword,
        old_password: values.oldPassword,
      },
      endpoint: getResetPasswordEndpoint(
        passwordResetInformations?.alias as string,
      ),
    })
      .then(() => {
        showSuccessMessage(t(labelPasswordRenewed));
        sendLogin({
          login: passwordResetInformations?.alias as string,
          password: values.newPassword,
        }).then(({ redirectUri }) => {
          showSuccessMessage(t(labelLoginSucceeded));
          loadUser()?.then(() => navigate(redirectUri));
        });
      })
      .catch(() => {
        setSubmitting(false);
      });
  };

  const validationSchema = Yup.object().shape({
    newPassword: Yup.string()
      .test(
        'match',
        t(labelTheNewPasswordIstheSameAsTheOldPassword),
        differentPasswords,
      )
      .required(t(labelRequired)),
    newPasswordConfirmation: Yup.string()
      .test('match', t(labelNewPasswordsMustMatch), matchNewPasswords)
      .required(t(labelRequired)),
    oldPassword: Yup.string().required(t(labelRequired)),
  });

  return {
    submitResetPassword,
    validationSchema,
  };
};

export default useResetPassword;
