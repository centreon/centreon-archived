import { useEffect } from 'react';

import { useFormik } from 'formik';
import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai/utils';
import dayjs from 'dayjs';

import { useSnackbar, useRequest, useLocaleDateTimeFormat } from '@centreon/ui';
import { downtimeAtom, userAtom } from '@centreon/ui-context';

import {
  labelDowntimeCommandSent,
  labelDowntimeBy,
} from '../../../translatedLabels';
import { Resource } from '../../../models';
import { setDowntimeOnResources } from '../../api';

import DialogDowntime from './Dialog';
import { getValidationSchema } from './validation';

interface Props {
  onClose: () => void;
  onSuccess: () => void;
  resources: Array<Resource>;
}

export interface DowntimeFormValues {
  comment?: string;
  duration: {
    unit: string;
    value: number;
  };
  endTime: Date;
  fixed: boolean;
  isDowntimeWithServices: boolean;
  startTime: Date;
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

  const { alias } = useAtomValue(userAtom);
  const downtime = useAtomValue(downtimeAtom);

  const currentDate = new Date();
  const defaultEndDate = dayjs(currentDate)
    .add(dayjs.duration({ seconds: downtime.duration }))
    .toDate();

  const form = useFormik<DowntimeFormValues>({
    initialValues: {
      comment: undefined,
      duration: {
        unit: 'seconds',
        value: downtime.duration,
      },
      endTime: defaultEndDate,
      fixed: downtime.fixed,
      isDowntimeWithServices: downtime.with_services,
      startTime: currentDate,
    },
    onSubmit: (values, { setSubmitting }) => {
      setSubmitting(true);

      const { startTime, endTime } = values;

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
    validationSchema: getValidationSchema(t),
  });

  useEffect(() => {
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
