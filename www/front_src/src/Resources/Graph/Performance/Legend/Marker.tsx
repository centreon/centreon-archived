import * as React from 'react';

import clsx from 'clsx';
import { equals } from 'ramda';

import { Theme } from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';
import { CreateCSSProperties } from '@mui/styles';

export enum LegendMarkerVariant {
  'dot',
  'bar',
}

interface StylesProps {
  color?: string;
  variant: LegendMarkerVariant;
}

const useStyles = makeStyles<Theme, StylesProps>((theme) => ({
  disabled: {
    color: theme.palette.text.disabled,
  },
  icon: ({ color, variant }): CreateCSSProperties<StylesProps> => ({
    backgroundColor: color,
    borderRadius: equals(LegendMarkerVariant.dot, variant) ? '50%' : 0,
    height: equals(LegendMarkerVariant.dot, variant) ? 9 : '100%',
    marginRight: theme.spacing(0.5),
    width: 9,
  }),
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
