import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { useTranslation } from 'react-i18next';

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
import { disacknowledgeResources } from './api';

interface Props {
  resources: Array<Resource>;
  onClose;
  onSuccess;
}

const DisacknowledgeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();
  const [
    disacknowledgeAssociatedResources,
    setDisacknowledgeAssociatedResources,
  ] = React.useState(true);

  const {
    sendRequest: sendDisacknowledgeResources,
    sending: sendingAcknowledgeResources,
  } = useRequest({
    request: disacknowledgeResources,
  });

  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const submitDisacknowledge = (): void => {
    sendDisacknowledgeResources({
      resources,
      params: resources,
    }).then(() => {
      showSuccess(t(labelAcknowledgeCommandSent));
      onSuccess();
    });
  };

  return (
    <DialogAcknowledge
      resources={resources}
      onConfirm={submitDisacknowledge}
      onCancel={onClose}
      canConfirm={form.isValid}
      errors={form.errors}
      values={form.values}
      handleChange={form.handleChange}
      submitting={sendingAcknowledgeResources}
      loading={isNil(form.values.comment)}
    />
  );
};

export default DisacknowledgeForm;
