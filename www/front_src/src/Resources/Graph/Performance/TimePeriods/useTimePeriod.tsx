import * as React from 'react';

import { always, and, cond, gte, isNil, not, pipe, propOr, T } from 'ramda';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';

import { dateFormat, timeFormat } from '@centreon/ui';

import {
  last24hPeriod,
  TimePeriod,
  getTimePeriodById,
  TimePeriodId,
  Timeframe,
  ChangeTimeframeProps,
  StoredTimeframe,
} from '../../../Details/tabs/Graph/models';

dayjs.extend(duration);

interface TimePeriodState {
  changeSelectedTimePeriod: (timePeriod: TimePeriodId) => void;
  selectedTimePeriod: TimePeriod | null;
  periodQueryParameters: string;
  getIntervalDates: () => [string, string];
  timeframe: Timeframe;
  changeTimeframe: (props: ChangeTimeframeProps) => void;
}

interface OnTimePeriodChangeProps {
  selectedTimePeriodId?: TimePeriodId;
  selectedTimeframe?: StoredTimeframe;
}

interface Props {
  defaultSelectedTimePeriodId?: TimePeriodId;
  defaultSelectedTimeframe?: StoredTimeframe;
  onTimePeriodChange?: ({
    selectedTimePeriodId,
    selectedTimeframe,
  }: OnTimePeriodChangeProps) => void;
}

interface GetGraphQueryParametersProps {
  timePeriod?: TimePeriod | null;
  startDate?: Date;
  endDate?: Date;
}

const useTimePeriod = ({
  defaultSelectedTimePeriodId,
  defaultSelectedTimeframe,
  onTimePeriodChange,
}: Props): TimePeriodState => {
  const defaultTimePeriod = cond([
    [
      (timePeriodId) =>
        and(isNil(timePeriodId), isNil(defaultSelectedTimeframe)),
      always(last24hPeriod),
    ],
    [
      pipe(isNil, not),
      always(getTimePeriodById(defaultSelectedTimePeriodId as TimePeriodId)),
    ],
    [T, always(null)],
  ])(defaultSelectedTimePeriodId);

  const [
    selectedTimePeriod,
    setSelectedTimePeriod,
  ] = React.useState<TimePeriod | null>(defaultTimePeriod);

  const getLocalizedIntervalDates = (
    timePeriod: TimePeriod | null,
  ): Timeframe => {
    return {
      start: new Date(timePeriod?.getStart() || 0),
      end: new Date(Date.now()),
    };
  };

  const getNewTimeframe = ({ start, end }): Timeframe => {
    const timeframeInDay = dayjs
      .duration(dayjs(end).diff(dayjs(start)))
      .asDays();
    const xAxisTickFormat = gte(timeframeInDay, 2) ? dateFormat : timeFormat;
    const timelineLimit = cond<number, number>([
      [gte(1), always(20)],
      [gte(7), always(100)],
      [T, always(500)],
    ])(timeframeInDay);

    return {
      start,
      end,
      xAxisTickFormat,
      timelineLimit,
    };
  };

  const [timeframe, setTimeframe] = React.useState<Timeframe>(
    defaultSelectedTimeframe
      ? getNewTimeframe({
          start: new Date(propOr(0, 'start', defaultSelectedTimeframe)),
          end: new Date(propOr(0, 'end', defaultSelectedTimeframe)),
        })
      : getLocalizedIntervalDates(defaultTimePeriod),
  );

  const getIntervalDates = (timePeriod): [string, string] => {
    if (isNil(timePeriod)) {
      return [timeframe.start.toISOString(), timeframe.end.toISOString()];
    }
    return [
      timePeriod.getStart().toISOString(),
      new Date(Date.now()).toISOString(),
    ];
  };

  const getGraphQueryParameters = ({
    timePeriod,
    startDate,
    endDate,
  }: GetGraphQueryParametersProps): string => {
    if (pipe(isNil, not)(timePeriod)) {
      const [start, end] = getIntervalDates(timePeriod);

      return `?start=${start}&end=${end}`;
    }

    return `?start=${startDate?.toISOString()}&end=${endDate?.toISOString()}`;
  };

  const [periodQueryParameters, setPeriodQueryParameters] = React.useState(
    getGraphQueryParameters(
      selectedTimePeriod
        ? { timePeriod: selectedTimePeriod }
        : { startDate: timeframe.start, endDate: timeframe.end },
    ),
  );

  const changeSelectedTimePeriod = (timePeriodId: TimePeriodId): void => {
    const timePeriod = getTimePeriodById(timePeriodId);

    setSelectedTimePeriod(timePeriod);
    onTimePeriodChange?.({ selectedTimePeriodId: timePeriod.id });

    const newTimeframeDates = getLocalizedIntervalDates(timePeriod);

    setTimeframe(newTimeframeDates);

    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      timePeriod,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
  };

  const changeTimeframe = ({ date, property }: ChangeTimeframeProps): void => {
    const newTimeframe = getNewTimeframe({
      ...timeframe,
      [property]: date,
    });
    setTimeframe(newTimeframe);
    onTimePeriodChange?.({
      selectedTimeframe: {
        start: newTimeframe.start.toISOString(),
        end: newTimeframe.end.toISOString(),
      },
    });
    setSelectedTimePeriod(null);
    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      startDate: newTimeframe.start,
      endDate: newTimeframe.end,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
  };

  return {
    changeSelectedTimePeriod,
    selectedTimePeriod,
    periodQueryParameters,
    getIntervalDates: (): [string, string] =>
      getIntervalDates(selectedTimePeriod),
    timeframe,
    changeTimeframe,
  };
};

export default useTimePeriod;
