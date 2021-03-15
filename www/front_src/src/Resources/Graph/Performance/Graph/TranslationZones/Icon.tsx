import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { equals, not } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { useMemoComponent } from '@centreon/ui';

import { TranslationDirection, useTranslationsContext } from '.';

export const translationIconSize = 20;

interface Props {
  xIcon: number;
  Icon: (props) => JSX.Element;
  direction: TranslationDirection;
  directionHovered: TranslationDirection | null;
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
  Icon,
  direction,
  directionHovered,
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

  const translateWithIcon = () =>
    not(sendingGetGraphDataRequest) && translate?.(direction);

  const getIconColor = () =>
    sendingGetGraphDataRequest || not(equals(directionHovered, direction))
      ? 'disabled'
      : 'primary';

  return useMemoComponent({
    Component: (
      <g>
        <svg
          y={graphHeight / 2 - translationIconSize / 2 + marginTop}
          x={xIcon}
          height={translationIconSize}
          width={translationIconSize}
          onClick={translateWithIcon}
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
          <Icon color={getIconColor()} />
        </svg>
      </g>
    ),
    memoProps: [
      xIcon,
      direction,
      ariaLabel,
      sendingGetGraphDataRequest,
      directionHovered,
    ],
  });
};

export default TranslationIcon;
