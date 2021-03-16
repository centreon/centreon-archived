import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { equals, not } from 'ramda';

import { makeStyles } from '@material-ui/core';

import { useMemoComponent } from '@centreon/ui';

import { TimeShiftDirection, useTimeShiftContext } from '.';

export const timeShiftIconSize = 20;

interface Props {
  xIcon: number;
  Icon: (props) => JSX.Element;
  direction: TimeShiftDirection;
  directionHovered: TimeShiftDirection | null;
  hoverDirection: (direction: TimeShiftDirection | null) => () => void;
  ariaLabel: string;
}

const useStyles = makeStyles({
  icon: {
    cursor: 'pointer',
  },
});

const TimeShiftIcon = ({
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
    shiftTime,
    sendingGetGraphDataRequest,
  } = useTimeShiftContext();

  const translateWithIcon = () =>
    not(sendingGetGraphDataRequest) && shiftTime?.(direction);

  const getIconColor = () =>
    sendingGetGraphDataRequest || not(equals(directionHovered, direction))
      ? 'disabled'
      : 'primary';

  return useMemoComponent({
    Component: (
      <g>
        <svg
          y={graphHeight / 2 - timeShiftIconSize / 2 + marginTop}
          x={xIcon}
          height={timeShiftIconSize}
          width={timeShiftIconSize}
          onClick={translateWithIcon}
          onMouseEnter={hoverDirection(direction)}
          onMouseLeave={hoverDirection(null)}
          className={classes.icon}
          aria-label={t(ariaLabel)}
        >
          <rect
            width={timeShiftIconSize}
            height={timeShiftIconSize}
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

export default TimeShiftIcon;
