import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';

import { Severity, useSnackbar, useRequest } from '@centreon/ui';

import { isNil } from 'ramda';
import {
  labelRequired,
  labelAcknowledgeCommandSent,
  labelAcknowledgedBy,
} from '../../../translatedLabels';
import DialogAcknowledge from './Dialog';
import { Resource } from '../../../models';
import { useUserContext } from '../../../../Provider/UserContext';
import { acknowledgeResources } from '../../api';

const validationSchema = Yup.object().shape({
  comment: Yup.string().required(labelRequired),
  notify: Yup.boolean(),
});

interface Props {
  onClose;
  onSuccess;
  resources: Array<Resource>;
}

const AcknowledgeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { showMessage } = useSnackbar();

  const { username } = useUserContext();

  const {
    sendRequest: sendAcknowledgeResources,
    sending: sendingAcknowledgeResources,
  } = useRequest({
    request: acknowledgeResources,
  });

  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const form = useFormik({
    initialValues: {
      acknowledgeAttachedResources: false,
      comment: undefined,
      notify: false,
    },
    onSubmit: (values) => {
      sendAcknowledgeResources({
        params: values,
        resources,
      }).then(() => {
        showSuccess(labelAcknowledgeCommandSent);
        onSuccess();
      });
    },
    validationSchema,
  });

  React.useEffect(() => {
    form.setFieldValue('comment', `${labelAcknowledgedBy} ${username}`);
  }, []);

  return (
    <DialogAcknowledge
      canConfirm={form.isValid}
      errors={form.errors}
      handleChange={form.handleChange}
      loading={isNil(form.values.comment)}
      resources={resources}
      submitting={sendingAcknowledgeResources}
      values={form.values}
      onCancel={onClose}
      onConfirm={form.submitForm}
    />
  );
};

export default AcknowledgeForm;
