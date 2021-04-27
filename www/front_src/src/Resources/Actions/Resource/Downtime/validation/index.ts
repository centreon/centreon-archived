import dayjs from 'dayjs';
import { FormikErrors } from 'formik';
import * as Yup from 'yup';

import {
  labelEndDateGreaterThanStartDate,
  labelMaxDuration1Year,
  labelRequired,
} from '../../../../translatedLabels';
import { DateParams } from '../models';
import { formatDateInterval } from '../utils';

interface Props {
  t: (string) => string;
  values: DateParams;
}

const getDateEndError = ({ values, t }: Props): string | undefined => {
  const [start, end] = formatDateInterval(values);

  if (start >= end) {
    return t(labelEndDateGreaterThanStartDate);
  }

  const dateEndStartDifference = dayjs(start).diff(dayjs(end), 'year');

  if (dateEndStartDifference) {
    return t(labelMaxDuration1Year);
  }

  return undefined;
};

const validate = ({ values, t }: Props): FormikErrors<DateParams> => {
  if (
    values.dateStart &&
    values.timeStart &&
    values.dateEnd &&
    values.timeEnd
  ) {
    const dateEndError = getDateEndError({ t, values });

    return dateEndError
      ? {
          dateEnd: dateEndError,
        }
      : {};
  }

  return {};
};

const getValidationSchema = (t: (string) => string): unknown =>
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

export { validate, getValidationSchema };
