import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { OpenidConfiguration, NamedEntity } from './models';
import {
  labelRequired,
  labelInvalidURL,
  labelInvalidIPAddress,
} from './translatedLabels';

const IPAddressRegexp = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\/\d{1,3})?$/;

const urlRegexp = /https?:\/\/(\S+)/;

const useValidationSchema = (): Yup.SchemaOf<OpenidConfiguration> => {
  const { t } = useTranslation();

  const namedEntitySchema: Yup.SchemaOf<NamedEntity> = Yup.object({
    id: Yup.number().required(t(labelRequired)),
    name: Yup.string().required(t(labelRequired)),
  });

  const authorizationSchema = Yup.object({
    accessGroup: namedEntitySchema.nullable().required(t(labelRequired)),
    claimValue: Yup.string().required(t(labelRequired)),
  });

  return Yup.object({
    authenticationType: Yup.string().required(t(labelRequired)),
    authorizationEndpoint: Yup.string().nullable().required(t(labelRequired)),
    authorizationRules: Yup.array().of(authorizationSchema),
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
    claimName: Yup.string().nullable(),
    clientId: Yup.string().nullable().required(t(labelRequired)),
    clientSecret: Yup.string().nullable().required(t(labelRequired)),
    connectionScopes: Yup.array().of(Yup.string().required(t(labelRequired))),
    contactGroup: namedEntitySchema.nullable().defined(),
    contactTemplate: namedEntitySchema
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
