import * as React from 'react';

import { not } from 'ramda';

import ArrowBackIosIcon from '@material-ui/icons/esm/ArrowBackIos';
import ArrowForwardIosIcon from '@material-ui/icons/esm/ArrowForwardIos';

import { labelBackward, labelForward } from '../../../../translatedLabels';

import TimeShiftZone, { timeShiftZoneWidth } from './Zone';
import TimeShiftIcon, { timeShiftIconSize } from './Icon';

export enum TimeShiftDirection {
  backward,
  forward,
}

interface TimeShiftContextProps {
  canAdjustTimePeriod: boolean;
  graphHeight: number;
  graphWidth: number;
  loading: boolean;
  marginLeft: number;
  marginTop: number;
  shiftTime?: (direction: TimeShiftDirection) => void;
}

export const TimeShiftContext =
  React.createContext<TimeShiftContextProps | undefined>(undefined);

export const useTimeShiftContext = (): TimeShiftContextProps =>
  React.useContext(TimeShiftContext) as TimeShiftContextProps;

const TimeShifts = (): JSX.Element | null => {
  const [directionHovered, setDirectionHovered] =
    React.useState<TimeShiftDirection | null>(null);

  const { graphWidth, canAdjustTimePeriod } = useTimeShiftContext();

  const hoverDirection = (direction: TimeShiftDirection | null) => () =>
    setDirectionHovered(direction);

  if (not(canAdjustTimePeriod)) {
    return null;
  }

  return (
    <>
      <TimeShiftIcon
        Icon={ArrowBackIosIcon}
        ariaLabel={labelBackward}
        direction={TimeShiftDirection.backward}
        directionHovered={directionHovered}
        xIcon={0}
      />
      <TimeShiftIcon
        Icon={ArrowForwardIosIcon}
        ariaLabel={labelForward}
        direction={TimeShiftDirection.forward}
        directionHovered={directionHovered}
        xIcon={graphWidth + timeShiftZoneWidth + timeShiftIconSize}
      />
      <TimeShiftZone
        direction={TimeShiftDirection.backward}
        directionHovered={directionHovered}
        onDirectionHover={hoverDirection}
      />
      <TimeShiftZone
        direction={TimeShiftDirection.forward}
        directionHovered={directionHovered}
        onDirectionHover={hoverDirection}
      />
    </>
  );
};

export default TimeShifts;
