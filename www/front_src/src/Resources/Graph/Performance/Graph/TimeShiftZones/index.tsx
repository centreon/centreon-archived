import * as React from 'react';

import { not } from 'ramda';

import ArrowBackIosIcon from '@material-ui/icons/ArrowBackIos';
import ArrowForwardIosIcon from '@material-ui/icons/ArrowForwardIos';

import { labelBackward, labelForward } from '../../../../translatedLabels';

import TimeShiftZone, { timeShiftZoneWidth } from './Zone';
import TimeShiftIcon, { timeShiftIconSize } from './Icon';

export enum TimeShiftDirection {
  backward,
  forward,
}

interface TimeShiftContextProps {
  graphHeight: number;
  graphWidth: number;
  marginLeft: number;
  marginTop: number;
  canAdjustTimePeriod: boolean;
  sendingGetGraphDataRequest: boolean;
  shiftTime?: (direction: TimeShiftDirection) => void;
}

export const TimeShiftContext = React.createContext<
  TimeShiftContextProps | undefined
>(undefined);

export const useTimeShiftContext = (): TimeShiftContextProps =>
  React.useContext(TimeShiftContext) as TimeShiftContextProps;

const TimeShifts = (): JSX.Element | null => {
  const [
    directionHovered,
    setDirectionHovered,
  ] = React.useState<TimeShiftDirection | null>(null);

  const { graphWidth, canAdjustTimePeriod } = useTimeShiftContext();

  const hoverDirection = (direction: TimeShiftDirection | null) => () =>
    setDirectionHovered(direction);

  if (not(canAdjustTimePeriod)) {
    return null;
  }

  return (
    <>
      <TimeShiftZone
        direction={TimeShiftDirection.backward}
        directionHovered={directionHovered}
        hoverDirection={hoverDirection}
      />
      <TimeShiftZone
        direction={TimeShiftDirection.forward}
        directionHovered={directionHovered}
        hoverDirection={hoverDirection}
      />
      <TimeShiftIcon
        xIcon={0}
        Icon={ArrowBackIosIcon}
        directionHovered={directionHovered}
        direction={TimeShiftDirection.backward}
        hoverDirection={hoverDirection}
        ariaLabel={labelBackward}
      />
      <TimeShiftIcon
        xIcon={graphWidth + timeShiftZoneWidth + timeShiftIconSize}
        Icon={ArrowForwardIosIcon}
        directionHovered={directionHovered}
        direction={TimeShiftDirection.forward}
        hoverDirection={hoverDirection}
        ariaLabel={labelForward}
      />
    </>
  );
};

export default TimeShifts;
