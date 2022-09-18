import { equals, includes, isEmpty, not, prop } from 'ramda';
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
  labelEmailAttributePath,
  labelEnableAutoImport,
  labelEnableOpenIDConnectAuthentication,
  labelEndSessionEndpoint,
  labelFullnameAttributePath,
  labelIntrospectionTokenEndpoint,
  labelLoginAttributePath,
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
  labelEnableConditionsOnIdentityProvider,
  labelConditionsAttributePath,
  labelWhichendpointtheConditionsAttributePathComeFrom,
  labelOther,
  labelUserIformation,
  labelIntrospectionToken,
  labelDefineAuthorizedConditionsValues,
  labelConditionValue,
  labelRolesMapping,
  labelWhichendpointtheRolesAttributePathComeFrom,
  labelEnableAutoManagement,
  labelApplyOnlyFirtsRole,
  labelRolesAttributePath,
  labelDefineRelationBetweenRolesAndAcl,
  labelRoleValue,
  labelAclAccessGroup,
  labelDefineYourEndpoint,
} from '../translatedLabels';
import { AuthenticationType } from '../models';
import {
  labelActivation,
  labelAuthorizations,
  labelAutoImportUsers,
  labelAuthentificationConditions,
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
    fieldName: 'isEnabled',
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
    group: labelAuthentificationConditions,
    label: labelTrustedClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    autocomplete: {
      creatable: true,
      options: [],
    },
    fieldName: 'blacklistClientAddresses',
    group: labelAuthentificationConditions,
    label: labelBlacklistClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    fieldName: 'enableConditionsOnIdentityProvider',
    group: labelAuthentificationConditions,
    label: labelEnableConditionsOnIdentityProvider,
    type: InputType.Switch,
  },
  {
    fieldName: 'conditionsAttributePath',
    group: labelAuthentificationConditions,
    label: labelConditionsAttributePath,
    // required: true,
    type: InputType.Text,
  },
  {
    fieldName: 'endpointTheConditionsAttributePathComeFrom',
    group: labelAuthentificationConditions,
    label: labelWhichendpointtheConditionsAttributePathComeFrom,
    radio: {
      options: [
        {
          label: labelIntrospectionToken,
          value: 'introspection_endpoint',
        },
        {
          label: labelUserIformation,
          value: 'user_information_endpoint',
        },
        {
          label: labelOther,
          value: 'custom_endpoint',
        },
      ],
    },
    type: InputType.Radio,
    extraTextLabel : labelDefineYourEndpoint
  },
  {
    fieldName: 'conditionsAuthorizedValues',
    fieldsTable: {
      additionalFieldsToMemoize: ['contactGroup'],
      columns: [
        {
          fieldName: 'conditionValue',
          label: labelConditionValue,
          type: InputType.Text,
        },
      ],
      defaultRowValue: {
        accessGroup: null,
        claimValue: '',
      },
      deleteLabel: labelDeleteRelation,
    },
    group: labelAuthentificationConditions,
    label: labelDefineAuthorizedConditionsValues,
    type: InputType.FieldsTable,
  },
  //
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
    label: labelLoginAttributePath,
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
      additionalConditionParameters: [],
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
    label: labelEmailAttributePath,
    type: InputType.Text,
  },
  {
    fieldName: 'fullnameBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    group: labelAutoImportUsers,
    label: labelFullnameAttributePath,
    type: InputType.Text,
  },
  {
    connectedAutocomplete: {
      additionalConditionParameters: [],
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
            additionalConditionParameters: [],
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
    },
    group: labelAuthorizations,
    label: labelDefineRelationAuthorizationValueAndAccessGroup,
    type: InputType.FieldsTable,
  },
  {
    fieldName: 'rolesIsEnabled',
    group: labelRolesMapping,
    label: labelEnableAutoManagement,
    type: InputType.Switch,
  },
  {
    fieldName: 'rolesApplyOnlyFirstRole',
    group: labelRolesMapping,
    label: labelApplyOnlyFirtsRole,
    type: InputType.Switch,
  },
  {
    fieldName: 'rolesAttributePath',
    group: labelRolesMapping,
    label: labelRolesAttributePath,
    type: InputType.Text,
  },
  {
    fieldName: 'rolesEndpoint',
    group: labelRolesMapping,
    label: labelWhichendpointtheRolesAttributePathComeFrom,
    radio: {
      options: [
        {
          label: labelIntrospectionToken,
          value: 'introspection_endpoint',
        },
        {
          label: labelUserIformation,
          value: 'user_information_endpoint',
        },
        {
          label: labelOther,
          value: 'custom_endpoint',
        },
      ],
    },
    type: InputType.Radio,
    extraTextLabel : labelDefineYourEndpoint
  },
  {
    fieldName: 'rolesRelations',
    fieldsTable: {
      additionalFieldsToMemoize: ['contactGroup'],
      columns: [
        {
          fieldName: 'roleValue',
          label: labelRoleValue,
          type: InputType.Text,
        },
        {
          connectedAutocomplete: {
            additionalConditionParameters: [],
            endpoint: accessGroupsEndpoint,
          },
          fieldName: 'accessGroup',
          label: labelAclAccessGroup,
          type: InputType.SingleConnectedAutocomplete,
        },
      ],
      defaultRowValue: {
        accessGroup: null,
        claimValue: '',
      },
      deleteLabel: labelDeleteRelation,
    },
    group: labelRolesMapping,
    label: labelDefineRelationBetweenRolesAndAcl,
    type: InputType.FieldsTable,
  },
];
