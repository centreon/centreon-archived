import { InputProps, InputType } from '@centreon/ui';

import {
  labelBlacklistClientAddresses,
  labelMixed,
  labelWebSSOOnly,
  labelTrustedClientAddresses,
  labelLoginHeaderAttributeName,
  labelPatternMatchLogin,
  labelPatternReplaceLogin,
  labelEnableWebSSOAuthentication,
  labelAuthenticationMode,
} from '../translatedLabels';
import {
  labelActivation,
  labelClientAddresses,
  labelIdentityProvider,
} from '../../translatedLabels';

export const inputs: Array<InputProps> = [
  {
    dataTestId: 'web_sso_isActive',
    fieldName: 'isActive',
    group: labelActivation,
    label: labelEnableWebSSOAuthentication,
    type: InputType.Switch,
  },
  {
    dataTestId: 'web_sso_isForced',
    fieldName: 'isForced',
    group: labelActivation,
    label: labelAuthenticationMode,
    radio: {
      options: [
        {
          label: labelWebSSOOnly,
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
    dataTestId: 'web_sso_trustedClientAddresses',
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
    dataTestId: 'web_sso_blacklistClientAddresses',
    fieldName: 'blacklistClientAddresses',
    group: labelClientAddresses,
    label: labelBlacklistClientAddresses,
    type: InputType.MultiAutocomplete,
  },
  {
    dataTestId: 'web_sso_loginHeaderAttribute',
    fieldName: 'loginHeaderAttribute',
    group: labelIdentityProvider,
    label: labelLoginHeaderAttributeName,
    required: true,
    type: InputType.Text,
  },
  {
    dataTestId: 'web_sso_patternMatchingLogin',
    fieldName: 'patternMatchingLogin',
    group: labelIdentityProvider,
    label: labelPatternMatchLogin,
    type: InputType.Text,
  },
  {
    dataTestId: 'web_sso_patternReplaceLogin',
    fieldName: 'patternReplaceLogin',
    group: labelIdentityProvider,
    label: labelPatternReplaceLogin,
    type: InputType.Text,
  },
];
