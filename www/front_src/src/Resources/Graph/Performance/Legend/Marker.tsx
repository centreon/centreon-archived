import * as React from 'react';

import clsx from 'clsx';
import { equals } from 'ramda';

import { makeStyles, Theme } from '@material-ui/core';

export enum LegendMarkerVariant {
  'dot',
  'bar',
}

const useStyles = makeStyles<
  Theme,
  { color?: string; variant: LegendMarkerVariant }
>((theme) => ({
  disabled: {
    color: theme.palette.text.disabled,
  },
  icon: {
    backgroundColor: ({ color }) => color,
    borderRadius: ({ variant }) =>
      equals(LegendMarkerVariant.dot, variant) ? '50%' : 0,
    height: ({ variant }) =>
      equals(LegendMarkerVariant.dot, variant) ? 9 : '100%',
    marginRight: theme.spacing(0.5),
    width: 9,
  },
}));

interface Props {
  color: string;
  disabled?: boolean;
  variant?: LegendMarkerVariant;
}

const LegendMarker = ({
  disabled,
  color,
  variant = LegendMarkerVariant.bar,
}: Props): JSX.Element => {
  const classes = useStyles({ color, variant });

  return (
    <div className={clsx(classes.icon, { [classes.disabled]: disabled })} />
  );
};

export default LegendMarker;
