import * as React from 'react';

import { useFormik } from 'formik';
import { useTranslation } from 'react-i18next';

import { useSnackbar, useRequest, useLocaleDateTimeFormat } from '@centreon/ui';
import { useUserContext } from '@centreon/ui-context';

import {
  labelDowntimeCommandSent,
  labelDowntimeBy,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import { setDowntimeOnResources } from '../../api';

import DialogDowntime from './Dialog';
import { getValidationSchema, validate } from './validation';
import { formatDateInterval } from './utils';

interface Props {
  onClose: () => void;
  onSuccess: () => void;
  resources: Array<Resource>;
}

export interface DowntimeFormValues {
  comment?: string;
  dateEnd: Date;
  dateStart: Date;
  duration: {
    unit: string;
    value: number;
  };
  fixed: boolean;
  isDowntimeWithServices: boolean;
  timeEnd: Date;
  timeStart: Date;
}

export interface DowntimeToPost {
  comment?: string;
  duration: {
    unit: string;
    value: number;
  };
  endTime: string;
  fixed: boolean;
  isDowntimeWithServices: boolean;
  startTime: string;
}

const DowntimeForm = ({
  resources,
  onClose,
  onSuccess,
}: Props): JSX.Element | null => {
  const { t } = useTranslation();
  const { showSuccessMessage } = useSnackbar();

  const { toIsoString } = useLocaleDateTimeFormat();

  const {
    sendRequest: sendSetDowntimeOnResources,
    sending: sendingSetDowntingOnResources,
  } = useRequest({
    request: setDowntimeOnResources,
  });
  const { alias, downtime } = useUserContext();

  const { alias, downtime } = useUserContext();

  const currentDate = new Date();

  const defaultDurationInMs = downtime.duration * 1000;
  const defaultEndDate = new Date(currentDate.getTime() + defaultDurationInMs);

  const form = useFormik<DowntimeFormValues>({
    initialValues: {
      comment: undefined,
      dateEnd: defaultEndDate,
      dateStart: currentDate,
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
        showSuccessMessage(t(labelDowntimeCommandSent));
        onSuccess();
      });
    },
    validate: (values) => validate({ t, values }),
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
