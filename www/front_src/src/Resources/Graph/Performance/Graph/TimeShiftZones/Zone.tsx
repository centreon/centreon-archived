import * as React from 'react';

import { Bar } from '@visx/visx';
import { equals, negate } from 'ramda';

import { fade, makeStyles, useTheme } from '@material-ui/core';

import { useMemoComponent } from '@centreon/ui';

import { useTimeShiftContext, TimeShiftDirection } from '.';

export const timeShiftZoneWidth = 50;

const useStyles = makeStyles({
  translationZone: {
    cursor: 'pointer',
  },
});

interface Props {
  direction: TimeShiftDirection;
  directionHovered: TimeShiftDirection | null;
  onDirectionHover: (direction: TimeShiftDirection | null) => () => void;
}

const TimeShiftZone = ({
  direction,
  onDirectionHover,
  directionHovered,
}: Props): JSX.Element => {
  const theme = useTheme();
  const classes = useStyles();

  const {
    graphHeight,
    graphWidth,
    marginLeft,
    marginTop,
    shiftTime,
  } = useTimeShiftContext();

  return useMemoComponent({
    Component: (
      <Bar
        x={
          (equals(direction, TimeShiftDirection.backward)
            ? negate(timeShiftZoneWidth)
            : graphWidth) + marginLeft
        }
        y={marginTop}
        width={timeShiftZoneWidth}
        height={graphHeight}
        onMouseOver={onDirectionHover(direction)}
        onMouseLeave={onDirectionHover(null)}
        onClick={() => shiftTime?.(direction)}
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

export default TimeShiftZone;
