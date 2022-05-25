import { equals, isEmpty, isNil, not, path, prop } from 'ramda';
import { FormikValues } from 'formik';

import {
  labelAccessGroup,
  labelAliasAttributeToBind,
  labelAtLeastOneOfTheTwoFollowingFieldsMustBeFilled,
  labelAuthenticationMode,
  labelAuthorizationEndpoint,
  labelAuthorizationsToClaim,
  labelBaseUrl,
  labelBlacklistClientAddresses,
  labelClientID,
  labelClientSecret,
  labelContactGroup,
  labelContactTemplate,
  labelDisableVerifyPeer,
  labelEmailAttributeToBind,
  labelEnableAutoImport,
  labelEnableOpenIDConnectAuthentication,
  labelEndSessionEndpoint,
  labelFullnameAttributeToBind,
  labelIntrospectionTokenEndpoint,
  labelLoginClaimValue,
  labelMixed,
  labelOpenIDConnectOnly,
  labelScopes,
  labelTokenEndpoint,
  labelTrustedClientAddresses,
  labelUseBasicAuthenticatonForTokenEndpointAuthentication,
  labelUserInformationEndpoint,
  labelValueToClaim,
} from '../translatedLabels';
import { AuthenticationType, Authorization } from '../models';
import { InputProps, InputType } from '../../FormInputs/models';
import {
  labelActivation,
  labelAuthorization,
  labelAutoImport,
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
const isAuthorizationClaimFilled = (values: FormikValues): boolean =>
  not(isEmpty(prop('authorizationClaim', values)));

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
    getChecked: (value) => equals(AuthenticationType.ClientSecretBasic, value),
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
    category: labelAutoImport,
    fieldName: 'autoImport',
    label: labelEnableAutoImport,
    type: InputType.Switch,
  },
  {
    category: labelAutoImport,
    endpoint: contactTemplatesEndpoint,
    fieldName: 'contactTemplate',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    label: labelContactTemplate,
    type: InputType.ConnectedAutocomplete,
  },
  {
    category: labelAutoImport,
    fieldName: 'emailBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    label: labelEmailAttributeToBind,
    type: InputType.Text,
  },
  {
    category: labelAutoImport,
    fieldName: 'aliasBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    label: labelAliasAttributeToBind,
    type: InputType.Text,
  },
  {
    category: labelAutoImport,
    fieldName: 'fullnameBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    label: labelFullnameAttributeToBind,
    type: InputType.Text,
  },
  {
    category: labelAuthorization,
    endpoint: contactGroupsEndpoint,
    fieldName: 'contactGroup',
    getRequired: isAuthorizationClaimFilled,
    label: labelContactGroup,
    type: InputType.ConnectedAutocomplete,
  },
  {
    category: labelAuthorization,
    fieldName: 'authorizationClaim',
    fieldsTableConfiguration: {
      columns: [
        {
          fieldName: 'name',
          label: labelValueToClaim,
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
        name: '',
      },
      getRequired: ({ values, index }): boolean => {
        const rowValues = path<Authorization>(
          ['authorizationClaim', index],
          values,
        );

        return (
          not(isNil(rowValues)) &&
          (not(isNil(prop('contactGroup', values))) ||
            isEmpty(rowValues?.name) ||
            isNil(rowValues?.accessGroup))
        );
      },
    },
    label: labelAuthorizationsToClaim,
    type: InputType.FieldsTable,
  },
];
