import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { useTranslation } from 'react-i18next';

import { useSnackbar, useRequest } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import {
  labelRequired,
  labelAcknowledgeCommandSent,
  labelAcknowledgedBy,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import { acknowledgeResources } from '../../api';

import DialogAcknowledge from './Dialog';

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
  const { t } = useTranslation();
  const { showSuccessMessage } = useSnackbar();

  const { alias } = useUserContext();

  const {
    sendRequest: sendAcknowledgeResources,
    sending: sendingAcknowledgeResources,
  } = useRequest({
    request: acknowledgeResources,
  });

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
        showSuccessMessage(t(labelAcknowledgeCommandSent));
        onSuccess();
      });
    },
    validationSchema,
  });

  React.useEffect(() => {
    form.setFieldValue('comment', `${t(labelAcknowledgedBy)} ${alias}`);
  }, []);

  return (
    <DialogAcknowledge
      canConfirm={form.isValid}
      errors={form.errors}
      handleChange={form.handleChange}
      resources={resources}
      submitting={sendingAcknowledgeResources}
      values={form.values}
      onCancel={onClose}
      onConfirm={form.submitForm}
    />
  );
};

export default AcknowledgeForm;
