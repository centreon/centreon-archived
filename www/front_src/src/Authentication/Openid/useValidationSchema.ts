import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { OpenidConfiguration, ContactTemplate } from './models';
import {
  labelRequired,
  labelInvalidURL,
  labelInvalidIPAddress,
} from './translatedLabels';

const IPAddressRegexp = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\/\d{1,3})?$/;

const urlRegexp = /https?:\/\/(\S+)/;

const useValidationSchema = (): Yup.SchemaOf<OpenidConfiguration> => {
  const { t } = useTranslation();

  const contactTemplateSchema: Yup.SchemaOf<ContactTemplate> = Yup.object({
    id: Yup.number().required(),
    name: Yup.string().required(),
  });

  return Yup.object({
    aliasBindAttribute: Yup.string().when(
      'autoImport',
      (autoImport, schema) => {
        return autoImport
          ? schema.nullable().required(t(labelRequired))
          : schema.nullable();
      },
    ),
    authenticationType: Yup.string().required(t(labelRequired)),
    authorizationEndpoint: Yup.string().nullable().required(t(labelRequired)),
    autoImport: Yup.boolean().required(t(labelRequired)),
    baseUrl: Yup.string()
      .matches(urlRegexp, t(labelInvalidURL))
      .nullable()
      .required(t(labelRequired)),
    blacklistClientAddresses: Yup.array().of(
      Yup.string()
        .matches(IPAddressRegexp, t(labelInvalidIPAddress))
        .required(t(labelRequired)),
    ),
    clientId: Yup.string().nullable().required(t(labelRequired)),
    clientSecret: Yup.string().nullable().required(t(labelRequired)),
    connectionScopes: Yup.array().of(Yup.string().required(t(labelRequired))),
    contactTemplate: contactTemplateSchema
      .when('autoImport', (autoImport, schema) => {
        return autoImport
          ? schema.nullable().required(t(labelRequired))
          : schema.nullable();
      })
      .defined(),
    emailBindAttribute: Yup.string().when(
      'autoImport',
      (autoImport, schema) => {
        return autoImport
          ? schema.nullable().required(t(labelRequired))
          : schema.nullable();
      },
    ),
    endSessionEndpoint: Yup.string().nullable(),
    fullnameBindAttribute: Yup.string().when(
      'autoImport',
      (autoImport, schema) => {
        return autoImport
          ? schema.nullable().required(t(labelRequired))
          : schema.nullable();
      },
    ),
    introspectionTokenEndpoint: Yup.string().nullable(),
    isActive: Yup.boolean().required(t(labelRequired)),
    isForced: Yup.boolean().required(t(labelRequired)),
    loginClaim: Yup.string().nullable(),
    tokenEndpoint: Yup.string().nullable().required(t(labelRequired)),
    trustedClientAddresses: Yup.array().of(
      Yup.string()
        .matches(IPAddressRegexp, t(labelInvalidIPAddress))
        .required(t(labelRequired)),
    ),
    userinfoEndpoint: Yup.string().nullable(),
    verifyPeer: Yup.boolean().required(t(labelRequired)),
  });
};

export default useValidationSchema;
