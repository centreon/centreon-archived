import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';

import { useSnackbar, useRequest } from '@centreon/ui';
import { acknowledgementAtom, userAtom } from '@centreon/ui-context';

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
  persistent: Yup.boolean(),
});

interface Props {
  onClose: () => void;
  onSuccess: () => void;
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
  const { showSuccessMessage } = useSnackbar();

  const {
    sendRequest: sendAcknowledgeResources,
    sending: sendingAcknowledgeResources,
  } = useRequest({
    request: acknowledgeResources,
  });

  const { alias } = useAtomValue(userAtom);
  const acknowledgement = useAtomValue(acknowledgementAtom);

  const form = useFormik<AcknowledgeFormValues>({
    initialValues: {
      acknowledgeAttachedResources: true,
      comment: undefined,
      forceActiveChecks: acknowledgement.force_active_checks,
      isSticky: acknowledgement.sticky,
      notify: true,
      persistent: acknowledgement.persistent,
    },
    onSubmit: (values): void => {
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
