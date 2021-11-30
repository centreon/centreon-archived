import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { SecurityPolicy } from './models';
import {
  labelChooseAValueBetween1HourAnd12Months,
  labelChooseAValueBetween1HourAnd1Week,
  labelMaximum128Characters,
  labelMinimum8Characters,
  labelRequired,
} from './translatedLabels';

const sevenDaysInSeconds = 1000 * 60 * 60 * 24 * 7;
const oneHourInSeconds = 1000 * 60 * 60;
const twelveMonthsInSeconds = 1000 * 60 * 60 * 24 * 365;

const useValidationSchema = (): Yup.SchemaOf<SecurityPolicy> => {
  const { t } = useTranslation();

  return Yup.object().shape({
    attempts: Yup.number().min(1).max(10).nullable().defined(),
    blockingDuration: Yup.number().max(sevenDaysInSeconds).nullable().defined(),
    canReusePasswords: Yup.boolean().defined(),
    delayBeforeNewPassword: Yup.number()
      .min(oneHourInSeconds, t(labelChooseAValueBetween1HourAnd1Week))
      .max(sevenDaysInSeconds, t(labelChooseAValueBetween1HourAnd1Week))
      .nullable()
      .defined(),
    hasLowerCase: Yup.boolean().defined(),
    hasNumber: Yup.boolean().defined(),
    hasSpecialCharacter: Yup.boolean().defined(),
    hasUpperCase: Yup.boolean().defined(),
    passwordExpiration: Yup.number()
      .min(sevenDaysInSeconds, t(labelChooseAValueBetween1HourAnd12Months))
      .max(twelveMonthsInSeconds, t(labelChooseAValueBetween1HourAnd12Months))
      .nullable()
      .defined(),
    passwordMinLength: Yup.number()
      .min(8, t(labelMinimum8Characters))
      .max(128, t(labelMaximum128Characters))
      .defined(t(labelRequired)),
  });
};

export default useValidationSchema;
