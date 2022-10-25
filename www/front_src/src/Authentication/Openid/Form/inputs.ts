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
  labelWhichEndpointTheRolesAttributePathComeFrom,
  labelEnableAutoManagement,
  labelApplyOnlyFirtsRole,
  labelRolesAttributePath,
  labelDefineRelationBetweenRolesAndAcl,
  labelRoleValue,
  labelAclAccessGroup,
  labelDefineYourEndpoint,
  labelWhichEndpointTheGroupsAttributePathComeFrom,
  labelContactGroup,
  labelGroupValue,
  labelDefinedTheRelationBetweenGroupsAndContactGroups,
  labelGroupsAttributePath,
} from '../translatedLabels';
import { AuthenticationType, EndpointType } from '../models';
import {
  labelActivation,
  labelAutoImportUsers,
  labelAuthenticationConditions,
  labelIdentityProvider,
  labelGroupsMapping,
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

const hideCustomEndpoint =
  (rootObject: string) =>
  (values: FormikValues): boolean =>
    !pathEq(
      [rootObject, 'endpoint', 'type'],
      EndpointType.CustomEndpoint,
      values,
    );

const authenticationConditions: Array<InputProps> = [
  {
    autocomplete: {
      creatable: true,
      options: [],
    },
    dataTestId: 'oidc_authenticationConditions.trustedClientAddresses',
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
    dataTestId: 'oidc_authenticationConditions.blacklistClientAddresses',
    fieldName: 'authenticationConditions.blacklistClientAddresses',
    group: labelAuthenticationConditions,
    label: labelBlacklistClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    dataTestId: 'oidc_authenticationConditions.isEnabled',
    fieldName: 'authenticationConditions.isEnabled',
    group: labelAuthenticationConditions,
    label: labelEnableConditionsOnIdentityProvider,
    type: InputType.Switch,
  },
  {
    dataTestId: 'oidc_authenticationConditions.attributePath',
    fieldName: 'authenticationConditions.attributePath',
    group: labelAuthenticationConditions,
    label: labelConditionsAttributePath,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_authenticationConditions.endpoint.type',
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
    dataTestId: 'oidc_authenticationConditions.endpoint.customEndpoint',
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
          dataTestId: 'oidc_authenticationConditions.authorizedValues',
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
];

const rolesMapping: Array<InputProps> = [
  {
    dataTestId: 'oidc_rolesMapping.isEnabled',
    fieldName: 'rolesMapping.isEnabled',
    group: labelRolesMapping,
    label: labelEnableAutoManagement,
    type: InputType.Switch,
  },
  {
    dataTestId: 'oidc_rolesMapping.applyOnlyFirstRole',
    fieldName: 'rolesMapping.applyOnlyFirstRole',
    group: labelRolesMapping,
    label: labelApplyOnlyFirtsRole,
    type: InputType.Switch,
  },
  {
    dataTestId: 'oidc_rolesMapping.attributePath',
    fieldName: 'rolesMapping.attributePath',
    group: labelRolesMapping,
    label: labelRolesAttributePath,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_rolesMapping.endpoint.type',
    fieldName: 'rolesMapping.endpoint.type',
    group: labelRolesMapping,
    label: labelWhichEndpointTheRolesAttributePathComeFrom,
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
    dataTestId: 'oidc_rolesMapping.endpoint.customEndpoint',
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
          dataTestId: 'oidc_claimValue',
          fieldName: 'claimValue',
          label: labelRoleValue,
          type: InputType.Text,
        },
        {
          connectedAutocomplete: {
            additionalConditionParameters: [],
            endpoint: accessGroupsEndpoint,
          },
          dataTestId: 'oidc_accessGroup',
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
      getSortable: (values: FormikValues): boolean =>
        prop('applyOnlyFirstRole', values?.rolesMapping),
    },
    group: labelRolesMapping,
    label: labelDefineRelationBetweenRolesAndAcl,
    type: InputType.FieldsTable,
  },
];

const groupsMapping: Array<InputProps> = [
  {
    dataTestId: 'oidc_groupsMapping.isEnabled',
    fieldName: 'groupsMapping.isEnabled',
    group: labelGroupsMapping,
    label: labelEnableAutoManagement,
    type: InputType.Switch,
  },
  {
    dataTestId: 'oidc_groupsMapping.attributePath',
    fieldName: 'groupsMapping.attributePath',
    group: labelGroupsMapping,
    label: labelGroupsAttributePath,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_groupsMapping.endpoint.type',
    fieldName: 'groupsMapping.endpoint.type',
    group: labelGroupsMapping,
    label: labelWhichEndpointTheGroupsAttributePathComeFrom,
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
    dataTestId: 'oidc_groupsMapping.endpoint.customEndpoint',
    fieldName: 'groupsMapping.endpoint.customEndpoint',
    group: labelGroupsMapping,
    hideInput: hideCustomEndpoint('groupsMapping'),
    label: labelDefineYourEndpoint,
    type: InputType.Text,
  },
  {
    fieldName: 'groupsMapping.relations',
    fieldsTable: {
      columns: [
        {
          dataTestId: 'oidc_groupValue',
          fieldName: 'groupValue',
          label: labelGroupValue,
          type: InputType.Text,
        },
        {
          connectedAutocomplete: {
            additionalConditionParameters: [],
            endpoint: contactGroupsEndpoint,
          },
          dataTestId: 'oidc_contactGroup',
          fieldName: 'contactGroup',
          label: labelContactGroup,
          type: InputType.SingleConnectedAutocomplete,
        },
      ],
      defaultRowValue: {
        contactGroup: null,
        groupValue: '',
      },
      deleteLabel: labelDeleteRelation,
    },
    group: labelGroupsMapping,
    label: labelDefinedTheRelationBetweenGroupsAndContactGroups,
    type: InputType.FieldsTable,
  },
];

export const inputs: Array<InputProps> = [
  {
    dataTestId: 'oidc_enableOpenIDConnectAuthentication',
    fieldName: 'isActive',
    group: labelActivation,
    label: labelEnableOpenIDConnectAuthentication,
    type: InputType.Switch,
  },
  {
    dataTestId: 'oidc_activationMode',
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
    dataTestId: 'oidc_baseUrl',
    fieldName: 'baseUrl',
    group: labelIdentityProvider,
    label: labelBaseUrl,
    required: true,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_authorizationEndpoint',
    fieldName: 'authorizationEndpoint',
    group: labelIdentityProvider,
    label: labelAuthorizationEndpoint,
    required: true,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_tokenEndpoint',
    fieldName: 'tokenEndpoint',
    group: labelIdentityProvider,
    label: labelTokenEndpoint,
    required: true,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_clientId',
    fieldName: 'clientId',
    group: labelIdentityProvider,
    label: labelClientID,
    required: true,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_clientSecret',
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
    dataTestId: 'oidc_connectionScopes',
    fieldName: 'connectionScopes',
    group: labelIdentityProvider,
    label: labelScopes,
    type: InputType.MultiAutocomplete,
  },
  {
    dataTestId: 'oidc_loginClaim',
    fieldName: 'loginClaim',
    group: labelIdentityProvider,
    label: labelLoginAttributePath,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_endSessionEndpoint',
    fieldName: 'endSessionEndpoint',
    group: labelIdentityProvider,
    label: labelEndSessionEndpoint,
    type: InputType.Text,
  },
  {
    additionalLabel: labelAtLeastOneOfTheTwoFollowingFieldsMustBeFilled,
    dataTestId: 'oidc_introspectionTokenEndpoint',
    fieldName: 'introspectionTokenEndpoint',
    group: labelIdentityProvider,
    label: labelIntrospectionTokenEndpoint,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_userinfoEndpoint',
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
    dataTestId: 'oidc_authenticationType',
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
    dataTestId: 'oidc_verifyPeer',
    fieldName: 'verifyPeer',
    group: labelIdentityProvider,
    label: labelDisableVerifyPeer,
    type: InputType.Switch,
  },
  {
    dataTestId: 'oidc_autoImport',
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
    dataTestId: 'oidc_contactTemplate',
    fieldName: 'contactTemplate',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    group: labelAutoImportUsers,
    label: labelContactTemplate,
    type: InputType.SingleConnectedAutocomplete,
  },
  {
    dataTestId: 'oidc_emailBindAttribute',
    fieldName: 'emailBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    group: labelAutoImportUsers,
    label: labelEmailAttributePath,
    type: InputType.Text,
  },
  {
    dataTestId: 'oidc_fullnameBindAttribute',
    fieldName: 'fullnameBindAttribute',
    getDisabled: isAutoImportDisabled,
    getRequired: isAutoImportEnabled,
    group: labelAutoImportUsers,
    label: labelFullnameAttributePath,
    type: InputType.Text,
  },
  ...authenticationConditions,
  ...rolesMapping,
  ...groupsMapping,
];
