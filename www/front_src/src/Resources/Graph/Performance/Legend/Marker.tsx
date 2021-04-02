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
  icon: {
    width: 9,
    height: ({ variant }) =>
      equals(LegendMarkerVariant.dot, variant) ? 9 : '100%',
    borderRadius: ({ variant }) =>
      equals(LegendMarkerVariant.dot, variant) ? '50%' : 0,
    marginRight: theme.spacing(1),
    backgroundColor: ({ color }) => color,
  },
  disabled: {
    color: theme.palette.text.disabled,
  },
}));

interface Props {
  disabled?: boolean;
  color: string;
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
