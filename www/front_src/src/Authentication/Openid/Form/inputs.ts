import { equals, not, pathEq, prop } from 'ramda';
import { FormikValues } from 'formik';

import { InputProps, InputType } from '@centreon/ui';

import {
  labelAtLeastOneOfTheTwoFollowingFieldsMustBeFilled,
  labelAuthenticationMode,
  labelAuthorizationEndpoint,
  labelBaseUrl,
  labelBlacklistClientAddresses,
  labelClientID,
  labelClientSecret,
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
  labelDeleteRelation,
  labelEnableConditionsOnIdentityProvider,
  labelConditionsAttributePath,
  labelWhichEndpointTheConditionsAttributePathComeFrom,
  labelOther,
  labelUserIformation,
  labelIntrospectionEndpoint,
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
import { AuthenticationType, EndpointType } from '../models';
import {
  labelActivation,
  labelAutoImportUsers,
  labelAuthenticationConditions,
  labelIdentityProvider,
} from '../../translatedLabels';
import {
  accessGroupsEndpoint,
  contactTemplatesEndpoint,
} from '../../api/endpoints';

const isAutoImportDisabled = (values: FormikValues): boolean =>
  not(prop('autoImport', values));

const isAutoImportEnabled = (values: FormikValues): boolean =>
  prop('autoImport', values);

const hideCustomEndpoint =
  (rootObject: string) =>
  (values: FormikValues): boolean =>
    !pathEq(
      [rootObject, 'endpoint', 'type'],
      EndpointType.CustomEndpoint,
      values,
    );

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
    fieldName: 'authenticationConditions.trustedClientAddresses',
    group: labelAuthenticationConditions,
    label: labelTrustedClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    autocomplete: {
      creatable: true,
      options: [],
    },
    fieldName: 'authenticationConditions.blacklistClientAddresses',
    group: labelAuthenticationConditions,
    label: labelBlacklistClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    fieldName: 'authenticationConditions.isEnabled',
    group: labelAuthenticationConditions,
    label: labelEnableConditionsOnIdentityProvider,
    type: InputType.Switch,
  },
  {
    fieldName: 'authenticationConditions.attributePath',
    group: labelAuthenticationConditions,
    label: labelConditionsAttributePath,
    type: InputType.Text,
  },
  {
    fieldName: 'authenticationConditions.endpoint.type',
    group: labelAuthenticationConditions,
    label: labelWhichEndpointTheConditionsAttributePathComeFrom,
    radio: {
      options: [
        {
          label: labelIntrospectionEndpoint,
          value: EndpointType.IntrospectionEndpoint,
        },
        {
          label: labelUserIformation,
          value: EndpointType.UserInformationEndpoint,
        },
        {
          label: labelOther,
          value: EndpointType.CustomEndpoint,
        },
      ],
    },
    type: InputType.Radio,
  },
  {
    fieldName: 'authenticationConditions.endpoint.customEndpoint',
    group: labelAuthenticationConditions,
    hideInput: hideCustomEndpoint('authenticationConditions'),
    label: labelDefineYourEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'authenticationConditions.authorizedValues',
    fieldsTable: {
      columns: [
        {
          fieldName: '',
          label: labelConditionValue,
          type: InputType.Text,
        },
      ],
      defaultRowValue: {
        conditionValue: '',
      },
      deleteLabel: labelDeleteRelation,
      hasSingleValue: true,
    },
    group: labelAuthenticationConditions,
    label: labelDefineAuthorizedConditionsValues,
    type: InputType.FieldsTable,
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
    fieldName: 'rolesMapping.isEnabled',
    group: labelRolesMapping,
    label: labelEnableAutoManagement,
    type: InputType.Switch,
  },
  {
    fieldName: 'rolesMapping.applyOnlyFirstRole',
    group: labelRolesMapping,
    label: labelApplyOnlyFirtsRole,
    type: InputType.Switch,
  },
  {
    fieldName: 'rolesMapping.attributePath',
    group: labelRolesMapping,
    label: labelRolesAttributePath,

    type: InputType.Text,
  },
  {
    fieldName: 'rolesMapping.endpoint.type',
    group: labelRolesMapping,
    label: labelWhichendpointtheRolesAttributePathComeFrom,
    radio: {
      options: [
        {
          label: labelIntrospectionEndpoint,
          value: EndpointType.IntrospectionEndpoint,
        },
        {
          label: labelUserIformation,
          value: EndpointType.UserInformationEndpoint,
        },
        {
          label: labelOther,
          value: EndpointType.CustomEndpoint,
        },
      ],
    },
    type: InputType.Radio,
  },
  {
    fieldName: 'rolesMapping.endpoint.customEndpoint',
    group: labelRolesMapping,
    hideInput: hideCustomEndpoint('rolesMapping'),
    label: labelDefineYourEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'rolesMapping.relations',
    fieldsTable: {
      columns: [
        {
          fieldName: 'claimValue',
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
