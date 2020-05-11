import * as React from 'react';

import { useFormik, FormikErrors } from 'formik';
import * as Yup from 'yup';

import { Severity, useSnackbar, useRequest } from '@centreon/ui';

import {
  labelRequired,
  labelDowntimeCommandSent,
  labelDowntimeBy,
  labelEndDateMustBeGreater,
} from '../../../translatedLabels';
import DialogDowntime from './Dialog';
import { Resource, User } from '../../../models';
import { setDowntimeOnResources, getUser } from '../../../api';

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
  dateStart: Yup.string().required(labelRequired).nullable(),
  timeStart: Yup.string().required(labelRequired).nullable(),
  dateEnd: Yup.string().required(labelRequired).nullable(),
  timeEnd: Yup.string().required(labelRequired).nullable(),
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
}: Props): JSX.Element | null => {
  const { showMessage } = useSnackbar();

  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const [locale, setLocale] = React.useState<string | null>('en');
  const [timezone, setTimezone] = React.useState<string | null>(null);

  const {
    sendRequest: sendSetDowntimeOnResources,
    sending: sendingSetDowntingOnResources,
  } = useRequest({
    request: setDowntimeOnResources,
  });

  const { sendRequest: sendGetUser } = useRequest<User>({
    request: getUser,
  });

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

      sendSetDowntimeOnResources({
        resources,
        params: { ...values, startTime, endTime, duration },
      }).then(() => {
        showSuccess(labelDowntimeCommandSent);
        onSuccess();
      });
    },
    validationSchema,
    validate,
  });

  React.useEffect(() => {
    sendGetUser().then((user) => {
      form.setFieldValue('comment', `${labelDowntimeBy} ${user.username}`);
      setLocale(user.locale);
      setTimezone(user.timezone);
    });
  }, []);

  return (
    <DialogDowntime
      locale={locale}
      timezone={timezone}
      resources={resources}
      onConfirm={form.submitForm}
      onCancel={onClose}
      canConfirm={form.isValid}
      errors={form.errors}
      values={form.values}
      handleChange={form.handleChange}
      setFieldValue={form.setFieldValue}
      submitting={sendingSetDowntingOnResources}
      loading={form.values.comment === ''}
    />
  );
};

export default DowntimeForm;
