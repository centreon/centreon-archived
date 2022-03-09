import { always, cond, equals } from 'ramda';

import { InputProps, InputType } from './models';
import MultipleInput from './Multiple';
import SwitchInput from './Switch';
import RadioInput from './Radio';
import TextInput from './Text';

export const getInput = cond<InputType, (props: InputProps) => JSX.Element>([
  [equals(InputType.Switch) as (b: InputType) => boolean, always(SwitchInput)],
  [equals(InputType.Radio) as (b: InputType) => boolean, always(RadioInput)],
  [equals(InputType.Text) as (b: InputType) => boolean, always(TextInput)],
  [
    equals(InputType.Multiple) as (b: InputType) => boolean,
    always(MultipleInput),
  ],
  [equals(InputType.Password) as (b: InputType) => boolean, always(TextInput)],
]);
