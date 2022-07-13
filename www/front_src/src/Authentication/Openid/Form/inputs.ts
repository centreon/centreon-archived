import { equals, isEmpty, isNil, not, path, prop } from 'ramda';
import { FormikValues } from 'formik';

import { InputProps, InputType } from '@centreon/ui';

import {
  labelAccessGroup,
  labelAtLeastOneOfTheTwoFollowingFieldsMustBeFilled,
  labelAuthenticationMode,
  labelAuthorizationEndpoint,
  labelBaseUrl,
  labelBlacklistClientAddresses,
  labelClientID,
  labelClientSecret,
  labelContactGroup,
  labelContactTemplate,
  labelDisableVerifyPeer,
  labelEmailAttribute,
  labelEnableAutoImport,
  labelEnableOpenIDConnectAuthentication,
  labelEndSessionEndpoint,
  labelFullnameAttribute,
  labelIntrospectionTokenEndpoint,
  labelLoginClaimValue,
  labelMixed,
  labelOpenIDConnectOnly,
  labelScopes,
  labelTokenEndpoint,
  labelTrustedClientAddresses,
  labelUseBasicAuthenticatonForTokenEndpointAuthentication,
  labelUserInformationEndpoint,
  labelAuthorizationValue,
  labelDefineRelationAuthorizationValueAndAccessGroup,
  labelDeleteRelation,
  labelAuthorizationKey,
} from '../translatedLabels';
import { AuthenticationType, AuthorizationRule } from '../models';
import {
  labelActivation,
  labelAuthorizations,
  labelAutoImportUsers,
  labelClientAddresses,
  labelIdentityProvider,
} from '../../translatedLabels';
import {
  accessGroupsEndpoint,
  contactGroupsEndpoint,
  contactTemplatesEndpoint,
} from '../../api/endpoints';

const isAutoImportDisabled = (values: FormikValues): boolean =>
  not(prop('autoImport', values));

const isAutoImportEnabled = (values: FormikValues): boolean =>
  prop('autoImport', values);

const isAuthorizationRelationsFilled = (values: FormikValues): boolean =>
  not(isEmpty(prop('authorizationRules', values)));

export const inputs: Array<InputProps> = [
  {
    fieldName: 'isActive',
    group: labelActivation,
    label: labelEnableOpenIDConnectAuthentication,
    type: InputType.Switch,
  },
  {
    fieldName: 'isForced',
    group: labelActivation,
    label: labelAuthenticationMode,
    radio: {
      options: [
        {
          label: labelOpenIDConnectOnly,
          value: true,
        },
        {
          label: labelMixed,
          value: false,
        },
      ],
    },
    type: InputType.Radio,
  },
  {
    autocomplete: {
      creatable: true,
      options: [],
    },
    fieldName: 'trustedClientAddresses',
    group: labelClientAddresses,
    label: labelTrustedClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    autocomplete: {
      creatable: true,
      options: [],
    },
    fieldName: 'blacklistClientAddresses',
    group: labelClientAddresses,
    label: labelBlacklistClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    fieldName: 'baseUrl',
    group: labelIdentityProvider,
    label: labelBaseUrl,
    required: true,
    type: InputType.Text,
  },
  {
    fieldName: 'authorizationEndpoint',
    group: labelIdentityProvider,
    label: labelAuthorizationEndpoint,
    required: true,
    type: InputType.Text,
  },
  {
    fieldName: 'tokenEndpoint',
    group: labelIdentityProvider,
    label: labelTokenEndpoint,
    required: true,
    type: InputType.Text,
  },
  {
    fieldName: 'clientId',
    group: labelIdentityProvider,
    label: labelClientID,
    required: true,
    type: InputType.Text,
  },
  {
    fieldName: 'clientSecret',
    group: labelIdentityProvider,
    label: labelClientSecret,
    required: true,
    type: InputType.Password,
  },
  {
    autocomplete: {
      creatable: true,
      options: [],
    },
    fieldName: 'connectionScopes',
    group: labelIdentityProvider,
    label: labelScopes,
    type: InputType.MultiAutocomplete,
  },
  {
    fieldName: 'loginClaim',
    group: labelIdentityProvider,
    label: labelLoginClaimValue,
    type: InputType.Text,
  },
  {
    fieldName: 'endSessionEndpoint',
    group: labelIdentityProvider,
    label: labelEndSessionEndpoint,
    type: InputType.Text,
  },
  {
    additionalLabel: labelAtLeastOneOfTheTwoFollowingFieldsMustBeFilled,
    fieldName: 'introspectionTokenEndpoint',
    group: labelIdentityProvider,
    label: labelIntrospectionTokenEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'userinfoEndpoint',
    group: labelIdentityProvider,
    label: labelUserInformationEndpoint,
    type: InputType.Text,
  },
  {
    change: ({ setFieldValue, value }): void => {
      setFieldValue(
        'authenticationType',
        value
          ? AuthenticationType.ClientSecretBasic
          : AuthenticationType.ClientSecretPost,
      );
    },
    fieldName: 'authenticationType',
    group: labelIdentityProvider,
    label: labelUseBasicAuthenticatonForTokenEndpointAuthentication,
    switchInput: {
      getChecked: (value): boolean =>
        equals(AuthenticationType.ClientSecretBasic, value),
    },
    type: InputType.Switch,
  },
  {
    fieldName: 'verifyPeer',
    group: labelIdentityProvider,
    label: labelDisableVerifyPeer,
    type: InputType.Switch,
  },
  {
    fieldName: 'autoImport',
    group: labelAutoImportUsers,
    label: labelEnableAutoImport,
    type: InputType.Switch,
  },
  {
    connectedAutocomplete: {
      endpoint: contactTemplatesEndpoint,
    },
    fieldName: 'contactTemplate',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    group: labelAutoImportUsers,
    label: labelContactTemplate,
    type: InputType.SingleConnectedAutocomplete,
  },
  {
    fieldName: 'emailBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    group: labelAutoImportUsers,
    label: labelEmailAttribute,
    type: InputType.Text,
  },
  {
    fieldName: 'fullnameBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    group: labelAutoImportUsers,
    label: labelFullnameAttribute,
    type: InputType.Text,
  },
  {
    connectedAutocomplete: {
      endpoint: contactGroupsEndpoint,
    },
    fieldName: 'contactGroup',
    getRequired: isAuthorizationRelationsFilled,
    group: labelAuthorizations,
    label: labelContactGroup,
    type: InputType.SingleConnectedAutocomplete,
  },
  {
    fieldName: 'claimName',
    group: labelAuthorizations,
    label: labelAuthorizationKey,
    type: InputType.Text,
  },
  {
    fieldName: 'authorizationRules',
    fieldsTable: {
      additionalFieldsToMemoize: ['contactGroup'],
      columns: [
        {
          fieldName: 'claimValue',
          label: labelAuthorizationValue,
          type: InputType.Text,
        },
        {
          connectedAutocomplete: {
            endpoint: accessGroupsEndpoint,
          },
          fieldName: 'accessGroup',
          label: labelAccessGroup,
          type: InputType.SingleConnectedAutocomplete,
        },
      ],
      defaultRowValue: {
        accessGroup: null,
        claimValue: '',
      },
      deleteLabel: labelDeleteRelation,
      getRequired: ({ values, index }): boolean => {
        const tableValues = prop('authorizationRules', values);

        const rowValues = path<AuthorizationRule>(
          ['authorizationRules', index],
          values,
        );

        return isNil(prop('contactGroup', values))
          ? not(isNil(rowValues))
          : isNil(tableValues) ||
              isEmpty(rowValues?.claimValue) ||
              isNil(rowValues?.accessGroup);
      },
    },
    group: labelAuthorizations,
    label: labelDefineRelationAuthorizationValueAndAccessGroup,
    type: InputType.FieldsTable,
  },
];
