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
import { InputProps, InputType } from '../../FormInputs/models';
import {
  labelActivation,
  labelClientAddresses,
  labelIdentityProvider,
} from '../../translatedLabels';

export const inputs: Array<InputProps> = [
  {
    category: labelActivation,
    fieldName: 'isActive',
    label: labelEnableWebSSOAuthentication,
    type: InputType.Switch,
  },
  {
    category: labelActivation,
    fieldName: 'isForced',
    label: labelAuthenticationMode,
    options: [
      {
        isChecked: (value: boolean): boolean => value,
        label: labelWebSSOOnly,
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
    fieldName: 'loginHeaderAttribute',
    label: labelLoginHeaderAttributeName,
    required: true,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'patternMatchingLogin',
    label: labelPatternMatchLogin,
    type: InputType.Text,
  },
  {
    category: labelIdentityProvider,
    fieldName: 'patternReplaceLogin',
    label: labelPatternReplaceLogin,
    type: InputType.Text,
  },
];
