import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { not } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { useMemoComponent } from '@centreon/ui';

import { TranslationDirection, useTranslationsContext } from '.';

export const translationIconSize = 20;

interface Props {
  xIcon: number;
  icon: JSX.Element | false;
  direction: TranslationDirection;
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
  ariaLabel,
  hoverDirection,
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    graphHeight,
    marginTop,
    translate,
    sendingGetGraphDataRequest,
  } = useTranslationsContext();

  return useMemoComponent({
    Component: (
      <g>
        <svg
          y={graphHeight / 2 - translationIconSize / 2 + marginTop}
          x={xIcon}
          height={translationIconSize}
          width={translationIconSize}
          onClick={() =>
            not(sendingGetGraphDataRequest) && translate?.(direction)}
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
    memoProps: [xIcon, direction, ariaLabel, sendingGetGraphDataRequest, icon],
  });
};

export default TranslationIcon;
