import * as React from 'react';

import { useTranslation } from 'react-i18next';

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
  ariaLabel: string;
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
  ariaLabel,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

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
        aria-label={t(ariaLabel)}
      >
        <rect width={iconSize} height={iconSize} fill="transparent" />
        {icon}
      </svg>
    </g>
  );
};

export default TranslationIcon;
