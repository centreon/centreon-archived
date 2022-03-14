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

export const inputs: Array<InputProps> = [
  {
    fieldName: 'isActive',
    label: labelEnableWebSSOAuthentication,
    type: InputType.Switch,
  },
  {
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
    fieldName: 'trustedClientAddresses',
    label: labelTrustedClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'blacklistClientAddresses',
    label: labelBlacklistClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'loginHeaderAttribute',
    label: labelLoginHeaderAttributeName,
    type: InputType.Text,
  },
  {
    fieldName: 'patternMatchingLogin',
    label: labelPatternMatchLogin,
    type: InputType.Text,
  },
  {
    fieldName: 'patternReplaceLogin',
    label: labelPatternReplaceLogin,
    type: InputType.Text,
  },
];
