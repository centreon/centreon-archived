import * as React from 'react';

import { Bar } from '@visx/visx';
import { equals, negate } from 'ramda';

import { fade, makeStyles, useTheme } from '@material-ui/core';

import { useMemoComponent } from '@centreon/ui';

import { useTranslationsContext, TranslationDirection } from '.';

export const translationZoneWidth = 50;

const useStyles = makeStyles({
  translationZone: {
    cursor: 'pointer',
  },
});

interface Props {
  direction: TranslationDirection;
  directionHovered: TranslationDirection | null;
  hoverDirection: (direction: TranslationDirection | null) => () => void;
}

const TranslationZone = ({
  direction,
  hoverDirection,
  directionHovered,
}: Props): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();

  const {
    graphHeight,
    graphWidth,
    marginLeft,
    marginTop,
    translate,
  } = useTranslationsContext();

  return useMemoComponent({
    Component: (
      <Bar
        x={
          (equals(direction, TranslationDirection.backward)
            ? negate(translationZoneWidth)
            : graphWidth) + marginLeft
        }
        y={marginTop}
        width={translationZoneWidth}
        height={graphHeight}
        onMouseOver={hoverDirection(direction)}
        onMouseLeave={hoverDirection(null)}
        onClick={() => translate?.(direction)}
        fill={
          equals(directionHovered, direction)
            ? fade(theme.palette.common.white, 0.5)
            : 'transparent'
        }
        className={classes.translationZone}
      />
    ),
    memoProps: [
      directionHovered,
      graphHeight,
      graphWidth,
      marginLeft,
      marginTop,
    ],
  });
};

export default TranslationZone;
