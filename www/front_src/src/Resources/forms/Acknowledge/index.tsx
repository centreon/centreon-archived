import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';

import { useCancelTokenSource, Severity, useSnackbar } from '@centreon/ui';

import {
  labelRequired,
  labelSomethingWentWrong,
  labelSuccessfullyAcknowledged,
} from '../../translatedLabels';
import DialogAcknowledge from './Dialog';
import { Resource } from '../../models';
import { acknowledgeResources } from '../../api';

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
}: Props): JSX.Element => {
  const { cancel, token } = useCancelTokenSource();
  const { showMessage } = useSnackbar();

  const showError = (message): void =>
    showMessage({ message, severity: Severity.error });
  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  React.useEffect(() => (): void => cancel(), []);

  const form = useFormik({
    initialValues: {
      comment: '',
      notify: false,
    },
    onSubmit: (values, { setSubmitting }) => {
      setSubmitting(true);

      const params = resources.map((resource) => ({ ...resource, ...values }));

      acknowledgeResources({
        resources: params,
        cancelToken: token,
      })
        .then(() => {
          showSuccess(labelSuccessfullyAcknowledged);
          onSuccess();
        })
        .catch(() => showError(labelSomethingWentWrong))
        .finally(() => setSubmitting(false));
    },
    validationSchema,
  });

  const hasResources = resources.length > 0;

  return (
    <DialogAcknowledge
      open={hasResources}
      onConfirm={form.submitForm}
      onCancel={onClose}
      canConfirm={form.isValid}
      errors={form.errors}
      values={form.values}
      handleChange={form.handleChange}
      submitting={form.isSubmitting}
    />
  );
};

export default AcknowledgeForm;
