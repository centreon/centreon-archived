import * as React from 'react';

import { useFormik, FormikErrors } from 'formik';
import * as Yup from 'yup';

import { Severity, useSnackbar, useRequest } from '@centreon/ui';

import { useUserContext } from '../../../../Provider/UserContext';
import {
  labelRequired,
  labelDowntimeCommandSent,
  labelDowntimeBy,
  labelEndDateMustBeGreater,
} from '../../../translatedLabels';
import DialogDowntime from './Dialog';
import { Resource } from '../../../models';
import { setDowntimeOnResources } from '../../api';

interface DateParams {
  dateEnd: Date;
  dateStart: Date;
  timeEnd: Date;
  timeStart: Date;
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
  comment: Yup.string().required(labelRequired),
  dateEnd: Yup.string().required(labelRequired).nullable(),
  dateStart: Yup.string().required(labelRequired).nullable(),
  duration: Yup.object().when('fixed', (fixed, schema) => {
    return !fixed
      ? schema.shape({
          unit: Yup.string().required(labelRequired),
          value: Yup.string().required(labelRequired),
        })
      : schema;
  }),
  fixed: Yup.boolean(),
  timeEnd: Yup.string().required(labelRequired).nullable(),
  timeStart: Yup.string().required(labelRequired).nullable(),
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
  onClose;
  onSuccess;
  resources: Array<Resource>;
}

const DowntimeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { showMessage } = useSnackbar();

  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const { locale, timezone, username } = useUserContext();

  const {
    sendRequest: sendSetDowntimeOnResources,
    sending: sendingSetDowntingOnResources,
  } = useRequest({
    request: setDowntimeOnResources,
  });

  const currentDate = new Date();
  const twoHoursMs = 2 * 60 * 60 * 1000;
  const twoHoursLaterDate = new Date(currentDate.getTime() + twoHoursMs);

  const form = useFormik({
    initialValues: {
      comment: '',
      dateEnd: twoHoursLaterDate,
      dateStart: currentDate,
      downtimeAttachedResources: true,
      duration: {
        unit: 'seconds',
        value: 3600,
      },
      fixed: true,
      timeEnd: twoHoursLaterDate,
      timeStart: currentDate,
    },
    onSubmit: (values, { setSubmitting }) => {
      setSubmitting(true);

      const [startTime, endTime] = formatDateInterval(values);

      const unitMultipliers = {
        hours: 3600,
        minutes: 60,
        seconds: 1,
      };
      const durationDivider = unitMultipliers?.[values.duration.unit] || 1;
      const duration = values.duration.value * durationDivider;

      sendSetDowntimeOnResources({
        params: { ...values, duration, endTime, startTime },
        resources,
      }).then(() => {
        showSuccess(labelDowntimeCommandSent);
        onSuccess();
      });
    },
    validate,
    validationSchema,
  });

  React.useEffect(() => {
    form.setFieldValue('comment', `${labelDowntimeBy} ${username}`);
  }, []);

  return (
    <DialogDowntime
      canConfirm={form.isValid}
      errors={form.errors}
      handleChange={form.handleChange}
      loading={form.values.comment === ''}
      locale={locale}
      resources={resources}
      setFieldValue={form.setFieldValue}
      submitting={sendingSetDowntingOnResources}
      timezone={timezone}
      values={form.values}
      onCancel={onClose}
      onConfirm={form.submitForm}
    />
  );
};

export default DowntimeForm;
