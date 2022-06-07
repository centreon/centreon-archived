import { useTranslation } from 'react-i18next';
import { equals, not } from 'ramda';

import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';

import { TimeShiftDirection, useTimeShiftContext } from '.';

export const timeShiftIconSize = 20;

interface Props {
  Icon: (props) => JSX.Element;
  ariaLabel: string;
  direction: TimeShiftDirection;
  directionHovered: TimeShiftDirection | null;
  xIcon: number;
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

  const { graphHeight, marginTop, shiftTime, loading } = useTimeShiftContext();

  const displayTimeShiftIcon =
    not(loading) && equals(directionHovered, direction);

  const svgProps = {
    'aria-label': t(ariaLabel),
    className: classes.icon,
    height: timeShiftIconSize,
    onClick: (): void => {
      if (loading) {
        return;
      }
      shiftTime?.(direction);
    },
    width: timeShiftIconSize,
    x: xIcon,
    y: graphHeight / 2 - timeShiftIconSize / 2 + marginTop,
  };

  return useMemoComponent({
    Component: (
      <g>
        <svg {...svgProps}>
          <rect
            fill="transparent"
            height={timeShiftIconSize}
            width={timeShiftIconSize}
          />
          {displayTimeShiftIcon && <Icon color="primary" />}
        </svg>
      </g>
    ),
    memoProps: [
      xIcon,
      direction,
      ariaLabel,
      loading,
      directionHovered,
      graphHeight,
    ],
  });
};

export default TimeShiftIcon;
