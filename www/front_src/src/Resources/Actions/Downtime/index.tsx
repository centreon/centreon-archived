import * as React from 'react';

import { useFormik, FormikErrors } from 'formik';
import * as Yup from 'yup';

import { useCancelTokenSource, Severity, useSnackbar } from '@centreon/ui';

import {
  labelRequired,
  labelSomethingWentWrong,
  labelDowntimeCommandSent,
  labelDowntimeBy,
  labelEndDateMustBeGreater,
} from '../../translatedLabels';
import DialogDowntime from './Dialog';
import { Resource } from '../../models';
import { setDowntimeOnResources, getUser } from '../../api';

interface DateParams {
  dateStart: Date;
  timeStart: Date;
  dateEnd: Date;
  timeEnd: Date;
}

const formatDateInterval = (values: DateParams): [Date, Date] => {
  const timeStart = new Date(values.timeStart);
  const dateTimeStart = new Date(values.dateStart);
  dateTimeStart.setHours(timeStart.getHours());
  dateTimeStart.setMinutes(timeStart.getMinutes());
  dateTimeStart.setSeconds(0);

  const timeEnd = new Date(values.timeEnd);
  const dateTimeEnd = new Date(values.dateEnd);
  dateTimeEnd.setHours(timeEnd.getHours());
  dateTimeEnd.setMinutes(timeEnd.getMinutes());
  dateTimeEnd.setSeconds(0);

  return [dateTimeStart, dateTimeEnd];
};

const validationSchema = Yup.object().shape({
  dateStart: Yup.string()
    .required(labelRequired)
    .nullable(),
  timeStart: Yup.string()
    .required(labelRequired)
    .nullable(),
  dateEnd: Yup.string()
    .required(labelRequired)
    .nullable(),
  timeEnd: Yup.string()
    .required(labelRequired)
    .nullable(),
  fixed: Yup.boolean(),
  duration: Yup.object().when('fixed', (fixed, schema) => {
    return !fixed
      ? schema.shape({
          value: Yup.string().required(labelRequired),
          unit: Yup.string().required(labelRequired),
        })
      : schema;
  }),
  comment: Yup.string().required(labelRequired),
});

const validate = (values: DateParams): FormikErrors<DateParams> => {
  const errors: FormikErrors<DateParams> = {};

  if (
    values.dateStart &&
    values.timeStart &&
    values.dateEnd &&
    values.timeEnd
  ) {
    const [start, end] = formatDateInterval(values);

    if (start >= end) {
      errors.dateEnd = labelEndDateMustBeGreater;
    }
  }

  return errors;
};

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
  const twoHoursMs = 2 * 60 * 60 * 1000;
  const twoHoursLaterDate = new Date(currentDate.getTime() + twoHoursMs);

  const form = useFormik({
    initialValues: {
      dateStart: currentDate,
      timeStart: currentDate,
      dateEnd: twoHoursLaterDate,
      timeEnd: twoHoursLaterDate,
      fixed: true,
      duration: {
        value: 3600,
        unit: 'seconds',
      },
      comment: '',
      downtimeAttachedResources: true,
    },
    onSubmit: (values, { setSubmitting }) => {
      setSubmitting(true);

      const [startTime, endTime] = formatDateInterval(values);

      const unitMultipliers = {
        seconds: 1,
        minutes: 60,
        hours: 3600,
      };
      const durationDivider = unitMultipliers?.[values.duration.unit] || 1;
      const duration = values.duration.value * durationDivider;

      const params = resources.map((resource) => ({
        ...resource,
        ...values,
        startTime,
        endTime,
        duration,
      }));

      setDowntimeOnResources({
        resources: params,
        cancelToken: token,
      })
        .then(() => {
          showSuccess(labelDowntimeCommandSent);
          onSuccess();
        })
        .catch(() => showError(labelSomethingWentWrong))
        .finally(() => setSubmitting(false));
    },
    validationSchema,
    validate,
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
