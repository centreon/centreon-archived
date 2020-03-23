import * as React from 'react';

import { useFormik } from 'formik';
import * as Yup from 'yup';

import { useCancelTokenSource, Severity, useSnackbar } from '@centreon/ui';

import {
  labelRequired,
  labelSomethingWentWrong,
  labelSuccessfullyAcknowledged,
  labelDowntimeBy,
} from '../../translatedLabels';
import DialogDowntime from './Dialog';
import { Resource } from '../../models';
import { acknowledgeResources, getUser } from '../../api';

const validationSchema = Yup.object().shape({
  comment: Yup.string().required(labelRequired),
  dateStart: Yup.string().required(labelRequired),
  timeStart: Yup.string().required(labelRequired),
  dateEnd: Yup.string().required(labelRequired),
  timeEnd: Yup.string().required(labelRequired),
  fixed: Yup.boolean(),
  duration: Yup.object().shape({
    value: Yup.string().when('$fixed', (fixed, schema) =>
      fixed ? schema.required(labelRequired) : schema,
    ),
  }),
});

interface Props {
  resources: Array<Resource>;
  onClose;
  onSuccess;
}

const DowntimeForm = ({
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

  const [loaded, setLoaded] = React.useState(false);
  const [locale, setLocale] = React.useState<string | null>('en');

  const currentDate = new Date();
  const twoHoursLaterDate = new Date(new Date().getTime() + 2 * 60 * 60 * 1000);

  const form = useFormik({
    initialValues: {
      comment: '',
      dateStart: currentDate,
      timeStart: currentDate,
      dateEnd: twoHoursLaterDate,
      timeEnd: twoHoursLaterDate,
      fixed: true,
      duration: {
        value: 3600,
        unit: 'seconds',
      },
      downtimeAttachedResources: true,
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

  React.useEffect(() => {
    if (!hasResources) {
      return;
    }

    getUser(token)
      .then((user) => {
        form.setFieldValue('comment', `${labelDowntimeBy} ${user.username}`);
        setLocale(user.locale);
      })
      .catch(() => showError(labelSomethingWentWrong))
      .finally(() => setLoaded(true));
  }, [hasResources]);

  React.useEffect(() => (): void => cancel(), []);

  return (
    <DialogDowntime
      locale={locale}
      resources={resources}
      onConfirm={form.submitForm}
      onCancel={onClose}
      canConfirm={form.isValid}
      errors={form.errors}
      values={form.values}
      handleChange={form.handleChange}
      setFieldValue={form.setFieldValue}
      submitting={form.isSubmitting}
      loading={!loaded}
    />
  );
};

export default DowntimeForm;
