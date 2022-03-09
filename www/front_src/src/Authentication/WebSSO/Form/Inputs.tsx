import * as React from 'react';

import { propEq } from 'ramda';
import { useFormikContext, FormikValues } from 'formik';

import { makeStyles } from '@mui/styles';

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
import { getInput } from '../../FormInputs';

const isAuthenticationNotActive = propEq('isActive', false);

export const inputs: Array<InputProps> = [
  {
    fieldName: 'isActive',
    label: labelEnableWebSSOAuthentication,
    type: InputType.Switch,
  },
  {
    fieldName: 'isForced',
    getDisabled: isAuthenticationNotActive,
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
    getDisabled: isAuthenticationNotActive,
    label: labelTrustedClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'blacklistClientAddresses',
    getDisabled: isAuthenticationNotActive,
    label: labelBlacklistClientAddresses,
    type: InputType.Multiple,
  },
  {
    fieldName: 'loginHeaderAttribute',
    getDisabled: isAuthenticationNotActive,
    label: labelLoginHeaderAttributeName,
    type: InputType.Text,
  },
  {
    fieldName: 'patternMatchingLogin',
    getDisabled: isAuthenticationNotActive,
    label: labelPatternMatchLogin,
    type: InputType.Text,
  },
  {
    fieldName: 'patternReplaceLogin',
    getDisabled: isAuthenticationNotActive,
    label: labelPatternReplaceLogin,
    type: InputType.Text,
  },
];

const useStyles = makeStyles((theme) => ({
  inputs: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(2),
  },
}));

const Inputs = (): JSX.Element => {
  const classes = useStyles();

  const { errors, values } = useFormikContext<FormikValues>();
  console.log(errors, values);

  return (
    <div className={classes.inputs}>
      {inputs.map(
        ({
          fieldName,
          label,
          getDisabled,
          type,
          options,
          change,
          getChecked,
        }) => {
          const Input = getInput(type);

          const props = {
            change,
            fieldName,
            getChecked,
            getDisabled,
            label,
            options,
            type,
          };

          return <Input key={label} {...props} />;
        },
      )}
    </div>
  );
};

export default Inputs;
