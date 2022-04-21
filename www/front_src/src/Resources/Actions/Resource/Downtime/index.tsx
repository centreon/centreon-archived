import * as React from 'react';

import { useFormik, FormikErrors } from 'formik';
import * as Yup from 'yup';
import { useTranslation } from 'react-i18next';

import {
  Severity,
  useSnackbar,
  useRequest,
  useLocaleDateTimeFormat,
} from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import {
  labelRequired,
  labelDowntimeCommandSent,
  labelDowntimeBy,
  labelEndDateMustBeGreater,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import { setDowntimeOnResources } from '../../api';

import DialogDowntime from './Dialog';

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

const getValidationSchema = (t): unknown =>
  Yup.object().shape({
    comment: Yup.string().required(t(labelRequired)),
    dateEnd: Yup.string().required(t(labelRequired)).nullable(),
    dateStart: Yup.string().required(t(labelRequired)).nullable(),
    duration: Yup.object().when('fixed', (fixed, schema) => {
      return !fixed
        ? schema.shape({
            unit: Yup.string().required(t(labelRequired)),
            value: Yup.string().required(t(labelRequired)),
          })
        : schema;
    }),
    fixed: Yup.boolean(),
    timeEnd: Yup.string().required(t(labelRequired)).nullable(),
    timeStart: Yup.string().required(t(labelRequired)).nullable(),
  });

const validate = (values: DateParams, t): FormikErrors<DateParams> => {
  const errors: FormikErrors<DateParams> = {};

  if (
    values.dateStart &&
    values.timeStart &&
    values.dateEnd &&
    values.timeEnd
  ) {
    const [start, end] = formatDateInterval(values);

    if (start >= end) {
      errors.dateEnd = t(labelEndDateMustBeGreater);
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
  const { t } = useTranslation();
  const { showMessage } = useSnackbar();

  const showSuccess = (message): void =>
    showMessage({ message, severity: Severity.success });

  const { alias, downtime } = useUserContext();
  const { toIsoString } = useLocaleDateTimeFormat();

  const {
    sendRequest: sendSetDowntimeOnResources,
    sending: sendingSetDowntingOnResources,
  } = useRequest({
    request: setDowntimeOnResources,
  });

  const currentDate = new Date();

  const defaultDurationInMs = downtime.duration * 1000;
  const defaultEndDate = new Date(currentDate.getTime() + defaultDurationInMs);

  const form = useFormik({
    initialValues: {
      comment: undefined,
      dateEnd: defaultEndDate,
      dateStart: currentDate,
      downtimeAttachedResources: true,
      duration: {
        unit: 'seconds',
        value: downtime.duration,
      },
      fixed: downtime.fixed,
      isDowntimeWithServices: downtime.with_services,
      timeEnd: defaultEndDate,
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
        params: {
          ...values,
          duration,
          endTime: toIsoString(endTime),
          startTime: toIsoString(startTime),
        },
        resources,
      }).then(() => {
        showSuccess(t(labelDowntimeCommandSent));
        onSuccess();
      });
    },
    validate: (values) => validate(values, t),
    validationSchema: getValidationSchema(t),
  });

  React.useEffect(() => {
    form.setFieldValue('comment', `${t(labelDowntimeBy)} ${alias}`);
  }, []);

  return (
    <DialogDowntime
      canConfirm={form.isValid}
      errors={form.errors}
      handleChange={form.handleChange}
      resources={resources}
      setFieldValue={form.setFieldValue}
      submitting={sendingSetDowntingOnResources}
      values={form.values}
      onCancel={onClose}
      onConfirm={form.submitForm}
    />
  );
};

export default DowntimeForm;
