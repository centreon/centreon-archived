import { equals, isEmpty, isNil, not, path, prop } from 'ramda';
import { FormikValues } from 'formik';

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
import { InputProps, InputType } from '../../FormInputs/models';
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
    category: labelActivation,
    fieldName: 'isActive',
    label: labelEnableOpenIDConnectAuthentication,
    type: InputType.Switch,
  },
  {
    category: labelActivation,
    fieldName: 'isForced',
    label: labelAuthenticationMode,
    options: [
      {
        isChecked: (value: boolean): boolean => value,
        label: labelOpenIDConnectOnly,
        value: true,
      },
      {
        isChecked: (value: boolean): boolean => !value,
        label: labelMixed,
        value: false,
      },
    ],
    type: InputType.Radio,
  },
  {
    category: labelClientAddresses,
    fieldName: 'trustedClientAddresses',
    label: labelTrustedClientAddresses,
    type: InputType.Multiple,
  },
  {
    category: labelClientAddresses,
    fieldName: 'blacklistClientAddresses',
    label: labelBlacklistClientAddresses,
    type: InputType.Multiple,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'baseUrl',
    label: labelBaseUrl,
    required: true,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'authorizationEndpoint',
    label: labelAuthorizationEndpoint,
    required: true,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'tokenEndpoint',
    label: labelTokenEndpoint,
    required: true,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'clientId',
    label: labelClientID,
    required: true,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'clientSecret',
    label: labelClientSecret,
    required: true,
    type: InputType.Password,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'connectionScopes',
    label: labelScopes,
    type: InputType.Multiple,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'loginClaim',
    label: labelLoginClaimValue,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'endSessionEndpoint',
    label: labelEndSessionEndpoint,
    type: InputType.Text,
  },
  {
    additionalLabel: labelAtLeastOneOfTheTwoFollowingFieldsMustBeFilled,
    category: labelIdentityProvider,
    fieldName: 'introspectionTokenEndpoint',
    label: labelIntrospectionTokenEndpoint,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'userinfoEndpoint',
    label: labelUserInformationEndpoint,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    change: ({ setFieldValue, value }): void => {
      setFieldValue(
        'authenticationType',
        value
          ? AuthenticationType.ClientSecretBasic
          : AuthenticationType.ClientSecretPost,
      );
    },
    fieldName: 'authenticationType',
    getChecked: (value): boolean =>
      equals(AuthenticationType.ClientSecretBasic, value),
    label: labelUseBasicAuthenticatonForTokenEndpointAuthentication,
    type: InputType.Switch,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'verifyPeer',
    label: labelDisableVerifyPeer,
    type: InputType.Switch,
  },
  {
    category: labelAutoImportUsers,
    fieldName: 'autoImport',
    label: labelEnableAutoImport,
    type: InputType.Switch,
  },
  {
    category: labelAutoImportUsers,
    endpoint: contactTemplatesEndpoint,
    fieldName: 'contactTemplate',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    label: labelContactTemplate,
    type: InputType.ConnectedAutocomplete,
  },
  {
    category: labelAutoImportUsers,
    fieldName: 'emailBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    label: labelEmailAttribute,
    type: InputType.Text,
  },
  {
    category: labelAutoImportUsers,
    fieldName: 'fullnameBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    label: labelFullnameAttribute,
    type: InputType.Text,
  },
  {
    category: labelAuthorizations,
    endpoint: contactGroupsEndpoint,
    fieldName: 'contactGroup',
    getRequired: isAuthorizationRelationsFilled,
    label: labelContactGroup,
    type: InputType.ConnectedAutocomplete,
  },
  {
    category: labelAuthorizations,
    fieldName: 'claimName',
    label: labelAuthorizationKey,
    type: InputType.Text,
  },
  {
    additionalFieldsToMemoize: ['contactGroup'],
    category: labelAuthorizations,
    fieldName: 'authorizationRules',
    fieldsTableConfiguration: {
      columns: [
        {
          fieldName: 'claimValue',
          label: labelAuthorizationValue,
          type: InputType.Text,
        },
        {
          endpoint: accessGroupsEndpoint,
          fieldName: 'accessGroup',
          label: labelAccessGroup,
          type: InputType.ConnectedAutocomplete,
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
    label: labelDefineRelationAuthorizationValueAndAccessGroup,
    type: InputType.FieldsTable,
  },
];
