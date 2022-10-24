import { useTranslation } from 'react-i18next';
import * as Yup from 'yup';

import { OpenidConfiguration, NamedEntity, EndpointType } from './models';
import {
  labelRequired,
  labelInvalidURL,
  labelInvalidIPAddress
} from './translatedLabels';

const IPAddressRegexp = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}(\/\d{1,3})?$/;

const urlRegexp = /https?:\/\/(\S+)/;

const useValidationSchema = (): Yup.SchemaOf<OpenidConfiguration> => {
  const { t } = useTranslation();

  const namedEntitySchema: Yup.SchemaOf<NamedEntity> = Yup.object({
    id: Yup.number().required(t(labelRequired)),
    name: Yup.string().required(t(labelRequired))
  });

  const rolesRelationSchema = Yup.object({
    accessGroup: namedEntitySchema.nullable().required(t(labelRequired)),
    claimValue: Yup.string().required(t(labelRequired))
  });

  const groupsRelationSchema = Yup.object({
    contactGroup: namedEntitySchema.nullable().required(t(labelRequired)),
    groupValue: Yup.string().required(t(labelRequired))
  });

  const endpointTypeSchema = Yup.mixed<EndpointType>()
    .oneOf(Object.values(EndpointType))
    .required(t(labelRequired));
  const switchSchema = Yup.boolean().required(t(labelRequired));
  const endpointSchema = Yup.object({
    customEndpoint: Yup.string().when('type', {
      is: EndpointType.CustomEndpoint,
      otherwise: (schema) => schema.nullable(),
      then: (schema) => schema.required(t(labelRequired))
    }),
    type: endpointTypeSchema
  });

  return Yup.object({
    authenticationConditions: Yup.object({
      attributePath: Yup.string(),
      authorizedValues: Yup.array().of(Yup.string().defined()),
      blacklistClientAddresses: Yup.array().of(
        Yup.string()
          .matches(IPAddressRegexp, t(labelInvalidIPAddress))
          .required(t(labelRequired))
      ),
      endpoint: endpointSchema,
      isEnabled: switchSchema,
      trustedClientAddresses: Yup.array().of(
        Yup.string()
          .matches(IPAddressRegexp, t(labelInvalidIPAddress))
          .required(t(labelRequired))
      )
    }),
    authenticationType: Yup.string().required(t(labelRequired)),
    authorizationEndpoint: Yup.string().nullable().required(t(labelRequired)),
    autoImport: switchSchema,
    baseUrl: Yup.string()
      .matches(urlRegexp, t(labelInvalidURL))
      .nullable()
      .required(t(labelRequired)),
    claimName: Yup.string().nullable(),
    clientId: Yup.string().nullable().required(t(labelRequired)),
    clientSecret: Yup.string().nullable().required(t(labelRequired)),
    connectionScopes: Yup.array().of(Yup.string().required(t(labelRequired))),
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
      }
    ),
    endSessionEndpoint: Yup.string().nullable(),
    fullnameBindAttribute: Yup.string().when(
      'autoImport',
      (autoImport, schema) => {
        return autoImport
          ? schema.nullable().required(t(labelRequired))
          : schema.nullable();
      }
    ),
    groupsMapping: Yup.object({
      attributePath: Yup.string(),
      endpoint: endpointSchema,
      isEnabled: switchSchema,
      relations: Yup.array().of(groupsRelationSchema)
    }),
    introspectionTokenEndpoint: Yup.string().nullable(),
    isActive: switchSchema,
    isForced: switchSchema,
    loginClaim: Yup.string().nullable(),
    rolesMapping: Yup.object({
      applyOnlyFirstRole: switchSchema,
      attributePath: Yup.string(),
      endpoint: endpointSchema,
      isEnabled: switchSchema,
      relations: Yup.array().of(rolesRelationSchema)
    }),
    tokenEndpoint: Yup.string().nullable().required(t(labelRequired)),
    userinfoEndpoint: Yup.string().nullable(),
    verifyPeer: switchSchema
  });
};

export default useValidationSchema;
