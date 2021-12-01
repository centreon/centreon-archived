import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { SecurityPolicy } from './models';
import {
  oneHourInMilliseconds,
  sevenDaysInMilliseconds,
  twelveMonthsInMilliseconds,
} from './timestamps';
import {
  labelChooseAValueBetween1and10,
  labelChooseAValueBetween1HourAnd12Months,
  labelChooseAValueBetween1HourAnd1Week,
  labelMaximum128Characters,
  labelMinimum8Characters,
  labelRequired,
  labelBlockingDurationMustBeLessOrEqualThan7Days,
} from './translatedLabels';

const useValidationSchema = (): Yup.SchemaOf<SecurityPolicy> => {
  const { t } = useTranslation();

  return Yup.object().shape({
    attempts: Yup.number()
      .min(1, t(labelChooseAValueBetween1and10))
      .max(10, t(labelChooseAValueBetween1and10))
      .nullable()
      .defined(),
    blockingDuration: Yup.number()
      .max(
        sevenDaysInMilliseconds,
        t(labelBlockingDurationMustBeLessOrEqualThan7Days),
      )
      .nullable()
      .defined(),
    canReusePasswords: Yup.boolean().defined(),
    delayBeforeNewPassword: Yup.number()
      .min(oneHourInMilliseconds, t(labelChooseAValueBetween1HourAnd1Week))
      .max(sevenDaysInMilliseconds, t(labelChooseAValueBetween1HourAnd1Week))
      .nullable()
      .defined(),
    hasLowerCase: Yup.boolean().defined(),
    hasNumber: Yup.boolean().defined(),
    hasSpecialCharacter: Yup.boolean().defined(),
    hasUpperCase: Yup.boolean().defined(),
    passwordExpiration: Yup.number()
      .min(sevenDaysInMilliseconds, t(labelChooseAValueBetween1HourAnd12Months))
      .max(
        twelveMonthsInMilliseconds,
        t(labelChooseAValueBetween1HourAnd12Months),
      )
      .nullable()
      .defined(),
    passwordMinLength: Yup.number()
      .min(8, t(labelMinimum8Characters))
      .max(128, t(labelMaximum128Characters))
      .defined(t(labelRequired)),
  });
};

export default useValidationSchema;
