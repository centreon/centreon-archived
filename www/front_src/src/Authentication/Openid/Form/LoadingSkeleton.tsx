import * as React from 'react';

import { always, cond, equals } from 'ramda';

import { makeStyles } from '@mui/styles';

import { LoadingSkeleton } from '@centreon/ui';

import { InputType } from '../models';

import { inputs } from './Inputs';

const getSkeleton = cond<InputType, JSX.Element>([
  [
    equals(InputType.Switch) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={38} />),
  ],
  [
    equals(InputType.Radio) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={104} />),
  ],
  [
    equals(InputType.Text) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={52} />),
  ],
  [
    equals(InputType.MultiText) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={52} />),
  ],
  [
    equals(InputType.Password) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={52} />),
  ],
]);

const useStyles = makeStyles((theme) => ({
  buttons: {
    columnGap: theme.spacing(2),
    display: 'flex',
    flexDirection: 'row',
  },
  skeletonInputs: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(2),
  },
}));

const LoadingSkeletonForm = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.skeletonInputs}>
      {inputs.map(({ type, label }) => (
        <div key={label}>{getSkeleton(type)}</div>
      ))}
      <div className={classes.buttons}>
        <LoadingSkeleton height={32} width="18%" />
        <LoadingSkeleton height={32} width="14%" />
      </div>
    </div>
  );
};

export default LoadingSkeletonForm;
