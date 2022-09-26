import { InputProps, InputType } from '@centreon/ui';

import {
  labelLast3PasswordsCanBeReused,
  labelMinimumPasswordLength,
  labelPasswordBlockingPolicy,
  labelPasswordCasePolicy,
  labelPasswordExpirationPolicy,
} from '../translatedLabels';

import Attempts from './Attempts';
import BlockingDuration from './BlockingDuration';
import CaseButtons from './CaseButtons';
import PasswordExpiration from './PasswordExpiration';
import ExcludedUsers from './PasswordExpiration/ExcludedUsers';
import TimeBeforeNewPassword from './TimeBeforeNewPassword';

const inputs: Array<InputProps> = [
  {
    fieldName: '',
    grid: {
      alignItems: 'center',
      columns: [
        {
          dataTestId: 'local_passwordMinLength',
          fieldName: 'passwordMinLength',
          label: labelMinimumPasswordLength,
          text: {
            type: 'number',
          },
          type: InputType.Text,
        },
        {
          custom: {
            Component: CaseButtons,
          },
          fieldName: '',
          label: '',
          type: InputType.Custom,
        },
      ],
    },
    group: labelPasswordCasePolicy,
    label: '',
    type: InputType.Grid,
  },
  {
    fieldName: '',
    grid: {
      alignItems: 'center',
      columns: [
        {
          custom: {
            Component: PasswordExpiration,
          },
          fieldName: '',
          label: '',
          type: InputType.Custom,
        },
        {
          custom: {
            Component: ExcludedUsers,
          },
          fieldName: '',
          label: '',
          type: InputType.Custom,
        },
      ],
      gridTemplateColumns: 'repeat(2, 1fr)',
    },
    group: labelPasswordExpirationPolicy,
    label: '',
    type: InputType.Grid,
  },
  {
    custom: {
      Component: TimeBeforeNewPassword,
    },
    fieldName: '',
    group: labelPasswordExpirationPolicy,
    label: '',
    type: InputType.Custom,
  },
  {
    dataTestId: 'local_canReusePasswords',
    fieldName: 'canReusePasswords',
    group: labelPasswordExpirationPolicy,
    label: labelLast3PasswordsCanBeReused,
    type: InputType.Switch,
  },
  {
    custom: {
      Component: Attempts,
    },
    fieldName: '',
    group: labelPasswordBlockingPolicy,
    label: '',
    type: InputType.Custom,
  },
  {
    custom: {
      Component: BlockingDuration,
    },
    fieldName: '',
    group: labelPasswordBlockingPolicy,
    label: '',
    type: InputType.Custom,
  },
];

export default inputs;
