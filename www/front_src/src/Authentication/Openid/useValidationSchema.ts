import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { OpenidConfiguration } from './models';
import {
  labelRequired,
  labelInvalidIPAddressOrDomainName,
  labelInvalidURL,
} from './translatedLabels';

const IPAddressAndDomainRegexp =
  /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\/\d{1,3})?$|^(\S)+\.{1,}\D+[^.]$/;

const urlRegexp = /https?:\/\/(\S+)/;

const useValidationSchema = (): Yup.SchemaOf<OpenidConfiguration> => {
  const { t } = useTranslation();

  return Yup.object().shape({
    authenticationType: Yup.string().required(t(labelRequired)),
    authorizationEndpoint: Yup.string().required(t(labelRequired)),
    baseUrl: Yup.string()
      .matches(urlRegexp, t(labelInvalidURL))
      .required(t(labelRequired)),
    blacklistClientAddresses: Yup.array().of(
      Yup.string()
        .matches(IPAddressAndDomainRegexp, t(labelInvalidIPAddressOrDomainName))
        .required(t(labelRequired)),
    ),
    clientId: Yup.string().required(t(labelRequired)),
    clientSecret: Yup.string().required(t(labelRequired)),
    connectionScopes: Yup.array().of(Yup.string().required(t(labelRequired))),
    endSessionEndpoint: Yup.string().required(t(labelRequired)),
    introspectionTokenEndpoint: Yup.string().required(t(labelRequired)),
    isActive: Yup.boolean().required(t(labelRequired)),
    isForced: Yup.boolean().required(t(labelRequired)),
    loginClaim: Yup.string().required(t(labelRequired)),
    tokenEndpoint: Yup.string().required(t(labelRequired)),
    trustedClientAddresses: Yup.array().of(
      Yup.string()
        .matches(IPAddressAndDomainRegexp, t(labelInvalidIPAddressOrDomainName))
        .required(t(labelRequired)),
    ),
    userinfoEndpoint: Yup.string().required(t(labelRequired)),
    verifyPeer: Yup.boolean().required(t(labelRequired)),
  });
};

export default useValidationSchema;