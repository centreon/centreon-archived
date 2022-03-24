import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { PasswordSecurityPolicy } from './models';
import { oneHour, sevenDays, twelveMonths } from './timestamps';
import {
  labelChooseAValueBetween1and10,
  labelChooseADurationBetween7DaysAnd12Months,
  labelChooseADurationBetween1HourAnd1Week,
  labelMaximum128Characters,
  labelMinimum8Characters,
  labelRequired,
  labelBlockingDurationMustBeLessThanOrEqualTo7Days,
} from './translatedLabels';

const useValidationSchema = (): Yup.SchemaOf<PasswordSecurityPolicy> => {
  const { t } = useTranslation();

  return Yup.object().shape({
    attempts: Yup.number()
      .min(1, t(labelChooseAValueBetween1and10))
      .max(10, t(labelChooseAValueBetween1and10))
      .nullable()
      .defined(),
    blockingDuration: Yup.number()
      .max(sevenDays, t(labelBlockingDurationMustBeLessThanOrEqualTo7Days))
      .nullable()
      .defined(),
    canReusePasswords: Yup.boolean().defined(),
    delayBeforeNewPassword: Yup.number()
      .min(oneHour, t(labelChooseADurationBetween1HourAnd1Week))
      .max(sevenDays, t(labelChooseADurationBetween1HourAnd1Week))
      .nullable()
      .defined(),
    hasLowerCase: Yup.boolean().defined(),
    hasNumber: Yup.boolean().defined(),
    hasSpecialCharacter: Yup.boolean().defined(),
    hasUpperCase: Yup.boolean().defined(),
    passwordExpiration: Yup.object().shape({
      excludedUsers: Yup.array().of(Yup.string().required()),
      expirationDelay: Yup.number()
        .min(sevenDays, t(labelChooseADurationBetween7DaysAnd12Months))
        .max(twelveMonths, t(labelChooseADurationBetween7DaysAnd12Months))
        .nullable()
        .defined(),
    }),
    passwordMinLength: Yup.number()
      .min(8, t(labelMinimum8Characters))
      .max(128, t(labelMaximum128Characters))
      .defined(t(labelRequired)),
  });
};

export default useValidationSchema;
