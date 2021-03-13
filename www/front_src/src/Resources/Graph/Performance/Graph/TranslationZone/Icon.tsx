import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { makeStyles } from '@material-ui/core';

import { useMemoComponent } from '@centreon/ui/src';

import { TranslationDirection } from '..';

export const translationIconSize = 20;

interface Props {
  xIcon: number;
  icon: JSX.Element | false;
  direction: TranslationDirection;
  disabled: boolean;
  translate?: (direction: TranslationDirection) => void;
  hoverDirection: (direction: TranslationDirection | null) => () => void;
  ariaLabel: string;
  graphHeight: number;
  marginTop: number;
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
  ariaLabel,
  translate,
  hoverDirection,
  graphHeight,
  marginTop,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  return useMemoComponent({
    Component: (
      <g>
        <svg
          y={graphHeight / 2 - translationIconSize / 2 + marginTop}
          x={xIcon}
          height={translationIconSize}
          width={translationIconSize}
          onClick={() => !disabled && translate && translate?.(direction)}
          onMouseEnter={hoverDirection(direction)}
          onMouseLeave={hoverDirection(null)}
          className={classes.icon}
          aria-label={t(ariaLabel)}
        >
          <rect
            width={translationIconSize}
            height={translationIconSize}
            fill="transparent"
          />
          {icon}
        </svg>
      </g>
    ),
    memoProps: [xIcon, direction, ariaLabel, disabled, icon],
  });
};

export default TranslationIcon;
