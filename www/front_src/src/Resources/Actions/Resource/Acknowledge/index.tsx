import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { useTranslation } from 'react-i18next';

import { Severity, useSnackbar, useRequest } from '@centreon/ui';
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
  force_active_checks: Yup.boolean(),
  is_sticky: Yup.boolean(),
  notify: Yup.boolean(),
});

interface Props {
  onClose;
  onSuccess;
  resources: Array<Resource>;
}

export interface AcknowledgeFormValues {
  acknowledgeAttachedResources: boolean;
  comment?: string;
  forceActiveChecks: boolean;
  isSticky: boolean;
  notify: boolean;
  persistent: boolean;
}

const AcknowledgeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();

  const { alias, acknowledgement } = useUserContext();

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
      acknowledgeAttachedResources: acknowledgement.with_services,
      comment: undefined,
      forceActiveChecks: acknowledgement.force_active_checks,
      isSticky: acknowledgement.sticky,
      notify: acknowledgement.notify,
      persistent: acknowledgement.persistent,
    },
    onSubmit: (values) => {
      sendAcknowledgeResources({
        params: values,
        resources,
      }).then(() => {
        showSuccess(t(labelAcknowledgeCommandSent));
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
