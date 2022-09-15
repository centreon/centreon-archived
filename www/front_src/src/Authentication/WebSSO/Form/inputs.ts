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
  labelAuthentificationConditions,
  labelIdentityProvider,
} from '../../translatedLabels';

export const inputs: Array<InputProps> = [
  {
    fieldName: 'isActive',
    group: labelActivation,
    label: labelEnableWebSSOAuthentication,
    type: InputType.Switch,
  },
  {
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
    fieldName: 'loginHeaderAttribute',
    group: labelIdentityProvider,
    label: labelLoginHeaderAttributeName,
    required: true,
    type: InputType.Text,
  },
  {
    fieldName: 'patternMatchingLogin',
    group: labelIdentityProvider,
    label: labelPatternMatchLogin,
    type: InputType.Text,
  },
  {
    fieldName: 'patternReplaceLogin',
    group: labelIdentityProvider,
    label: labelPatternReplaceLogin,
    type: InputType.Text,
  },
];
