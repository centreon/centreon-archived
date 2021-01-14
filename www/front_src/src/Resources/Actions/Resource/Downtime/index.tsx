import * as React from 'react';

import { useFormik, FormikErrors } from 'formik';
import * as Yup from 'yup';
import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

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

const getValidationSchema = (t): unknown =>
  Yup.object().shape({
    dateStart: Yup.string().required(t(labelRequired)).nullable(),
    timeStart: Yup.string().required(t(labelRequired)).nullable(),
    dateEnd: Yup.string().required(t(labelRequired)).nullable(),
    timeEnd: Yup.string().required(t(labelRequired)).nullable(),
    fixed: Yup.boolean(),
    duration: Yup.object().when('fixed', (fixed, schema) => {
      return !fixed
        ? schema.shape({
            value: Yup.string().required(t(labelRequired)),
            unit: Yup.string().required(t(labelRequired)),
          })
        : schema;
    }),
    comment: Yup.string().required(t(labelRequired)),
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
  resources: Array<Resource>;
  onClose;
  onSuccess;
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

  const defaultDurationInMs = downtime.default_duration * 1000;
  const defaultEndDate = new Date(currentDate.getTime() + defaultDurationInMs);

  const form = useFormik({
    initialValues: {
      dateStart: currentDate,
      timeStart: currentDate,
      dateEnd: defaultEndDate,
      timeEnd: defaultEndDate,
      fixed: true,
      duration: {
        value: downtime.default_duration,
        unit: 'seconds',
      },
      comment: undefined,
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
        params: {
          ...values,
          startTime: toIsoString(startTime),
          endTime: toIsoString(endTime),
          duration,
        },
      }).then(() => {
        showSuccess(t(labelDowntimeCommandSent));
        onSuccess();
      });
    },
    validationSchema: getValidationSchema(t),
    validate: (values) => validate(values, t),
  });

  React.useEffect(() => {
    form.setFieldValue('comment', `${t(labelDowntimeBy)} ${alias}`);
  }, []);

  return (
    <DialogDowntime
      resources={resources}
      onConfirm={form.submitForm}
      onCancel={onClose}
      canConfirm={form.isValid}
      errors={form.errors}
      values={form.values}
      handleChange={form.handleChange}
      setFieldValue={form.setFieldValue}
      submitting={sendingSetDowntingOnResources}
      loading={isNil(form.values.comment)}
    />
  );
};

export default DowntimeForm;
