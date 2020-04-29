import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';

import { Severity, useSnackbar, useRequest } from '@centreon/ui';

import {
  labelRequired,
  labelAcknowledgeCommandSent,
  labelAcknowledgedBy,
} from '../../../translatedLabels';
import DialogAcknowledge from './Dialog';
import { Resource, User } from '../../../models';
import { acknowledgeResources, getUser } from '../../../api';

const validationSchema = Yup.object().shape({
  comment: Yup.string().required(labelRequired),
  notify: Yup.boolean(),
});

interface Props {
  resources: Array<Resource>;
  onClose;
  onSuccess;
}

const AcknowledgeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { showMessage } = useSnackbar();

  const {
    sendRequest: sendAcknowledgeResources,
    sending: sendingAcknowledgeResources,
  } = useRequest({
    request: acknowledgeResources,
  });

  const { sendRequest: sendGetUser } = useRequest<User>({
    request: getUser,
  });

  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const form = useFormik({
    initialValues: {
      comment: '',
      notify: false,
      acknowledgeAttachedResources: false,
    },
    onSubmit: (values) => {
      sendAcknowledgeResources({
        resources,
        params: values,
      }).then(() => {
        showSuccess(labelAcknowledgeCommandSent);
        onSuccess();
      });
    },
    validationSchema,
  });

  const hasResources = resources.length > 0;

  React.useEffect(() => {
    sendGetUser().then((user) =>
      form.setFieldValue('comment', `${labelAcknowledgedBy} ${user.username}`),
    );
  }, [hasResources]);

  return (
    <DialogAcknowledge
      resources={resources}
      onConfirm={form.submitForm}
      onCancel={onClose}
      canConfirm={form.isValid}
      errors={form.errors}
      values={form.values}
      handleChange={form.handleChange}
      submitting={sendingAcknowledgeResources}
      loading={form.values.comment === ''}
    />
  );
};

export default AcknowledgeForm;
