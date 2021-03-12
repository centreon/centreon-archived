import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import { TranslationDirection } from '.';

const iconSize = 20;
const yMargin = -32;

interface Props {
  xIcon: number;
  icon: JSX.Element | false;
  direction: TranslationDirection;
  disabled: boolean;
  translate?: (direction: TranslationDirection) => void;
  hoverDirection: (direction: TranslationDirection | null) => () => void;
}

const useStyles = makeStyles({
  icon: {
    cursor: 'pointer',
  },
});

const TranslationIcon = ({
  xIcon,
  icon,
  direction,
  disabled,
  translate,
  hoverDirection,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <g>
      <svg
        y={yMargin}
        x={xIcon}
        height={iconSize}
        width={iconSize}
        onClick={() => !disabled && translate && translate?.(direction)}
        onMouseEnter={hoverDirection(direction)}
        onMouseLeave={hoverDirection(null)}
        className={classes.icon}
      >
        <rect width={iconSize} height={iconSize} fill="transparent" />
        {icon}
      </svg>
    </g>
  );
};

export default TranslationIcon;
