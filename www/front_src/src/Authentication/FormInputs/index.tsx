import * as React from 'react';

import { always, cond, equals } from 'ramda';

import { makeStyles } from '@mui/styles';

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

const useStyles = makeStyles((theme) => ({
  inputs: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(2),
  },
}));

interface Props {
  inputs: Array<InputProps>;
}

const Inputs = ({ inputs }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.inputs}>
      {inputs.map(({ fieldName, label, type, options, change, getChecked }) => {
        const Input = getInput(type);

        const props = {
          change,
          fieldName,
          getChecked,
          label,
          options,
          type,
        };

        return <Input key={label} {...props} />;
      })}
    </div>
  );
};

export default Inputs;
