import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { WebSSOConfiguration } from './models';
import {
  labelRequired,
  labelInvalidIPAddress,
  labelInvalidRegex,
} from './translatedLabels';

const IPAddressRegexp = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\/\d{1,3})?$/;
const matchARegexp = /^\^?[^(^|$);]+\$?$/;

const useValidationSchema = (): Yup.SchemaOf<WebSSOConfiguration> => {
  const { t } = useTranslation();

  return Yup.object().shape({
    blacklistClientAddresses: Yup.array().of(
      Yup.string()
        .matches(IPAddressRegexp, t(labelInvalidIPAddress))
        .required(t(labelRequired)),
    ),
    isActive: Yup.boolean().required(t(labelRequired)),
    isForced: Yup.boolean().required(t(labelRequired)),
    loginHeaderAttribute: Yup.string().nullable().required(t(labelRequired)),
    patternMatchingLogin: Yup.string()
      .matches(matchARegexp, t(labelInvalidRegex))
      .nullable(),
    patternReplaceLogin: Yup.string()
      .matches(matchARegexp, t(labelInvalidRegex))
      .nullable(),
    trustedClientAddresses: Yup.array().of(
      Yup.string()
        .matches(IPAddressRegexp, t(labelInvalidIPAddress))
        .required(t(labelRequired)),
    ),
  });
};

export default useValidationSchema;
