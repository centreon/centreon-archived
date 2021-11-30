import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { SecurityPolicy } from './models';
import {
  labelBetween1HourAnd12Months,
  labelMinimum8Characters,
  labelRequired,
} from './translatedLabels';

const sevenDaysInSeconds = 1000 * 60 * 60 * 24 * 7;
const oneHourInSeconds = 1000 * 60 * 60;
const twelveMonthsInSeconds = 1000 * 60 * 60 * 24 * 30 * 12;

const useValidationSchema = (): Yup.SchemaOf<SecurityPolicy> => {
  const { t } = useTranslation();

  return Yup.object().shape({
    attempts: Yup.number().min(1).max(10).nullable().defined(),
    blockingDuration: Yup.number().max(sevenDaysInSeconds).nullable().defined(),
    canReusePasswords: Yup.boolean().defined(),
    delayBeforeNewPassword: Yup.number()
      .min(oneHourInSeconds)
      .max(sevenDaysInSeconds)
      .nullable()
      .defined(),
    hasLowerCase: Yup.boolean().defined(),
    hasNumber: Yup.boolean().defined(),
    hasSpecialCharacter: Yup.boolean().defined(),
    hasUpperCase: Yup.boolean().defined(),
    passwordExpiration: Yup.number()
      .min(sevenDaysInSeconds, t(labelBetween1HourAnd12Months))
      .max(twelveMonthsInSeconds, t(labelBetween1HourAnd12Months))
      .nullable()
      .defined(),
    passwordMinLength: Yup.number()
      .min(8, t(labelMinimum8Characters))
      .defined(t(labelRequired)),
  });
};

export default useValidationSchema;
