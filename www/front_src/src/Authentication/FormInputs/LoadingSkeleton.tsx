import { always, cond, equals } from 'ramda';

import { makeStyles } from '@mui/styles';

import { LoadingSkeleton } from '@centreon/ui';

import { InputProps, InputType } from './models';

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
    equals(InputType.Multiple) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={52} />),
  ],
  [
    equals(InputType.Password) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={52} />),
  ],
  [
    equals(InputType.ConnectedAutocomplete) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={52} />),
  ],
  [
    equals(InputType.FieldsTable) as (b: InputType) => boolean,
    always(<LoadingSkeleton height={52} />),
  ],
]);

const useStyles = makeStyles((theme) => ({
  buttons: {
    columnGap: theme.spacing(2),
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'flex-end',
  },
  skeletonInputs: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(2),
  },
}));

interface Props {
  inputs: Array<InputProps>;
}

const LoadingSkeletonForm = ({ inputs }: Props): JSX.Element => {
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
