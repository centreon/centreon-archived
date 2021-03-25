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
}: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const {
    graphHeight,
    marginTop,
    shiftTime,
    sendingGetGraphDataRequest,
  } = useTimeShiftContext();

  const getIconColor = () =>
    sendingGetGraphDataRequest || not(equals(directionHovered, direction))
      ? 'disabled'
      : 'primary';

  const svgProps = {
    y: graphHeight / 2 - timeShiftIconSize / 2 + marginTop,
    x: xIcon,
    height: timeShiftIconSize,
    width: timeShiftIconSize,
    onClick: () => not(sendingGetGraphDataRequest) && shiftTime?.(direction),
    className: classes.icon,
    'aria-label': t(ariaLabel),
  };

  return useMemoComponent({
    Component: (
      <g>
        <svg {...svgProps}>
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
      graphHeight,
    ],
  });
};

export default TimeShiftIcon;
