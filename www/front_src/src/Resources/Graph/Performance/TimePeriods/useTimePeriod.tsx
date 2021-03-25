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
  CustomTimePeriod,
  ChangeCustomTimePeriodProps,
  StoredCustomTimePeriod,
} from '../../../Details/tabs/Graph/models';

dayjs.extend(duration);

interface TimePeriodState {
  changeSelectedTimePeriod: (timePeriod: TimePeriodId) => void;
  selectedTimePeriod: TimePeriod | null;
  periodQueryParameters: string;
  getIntervalDates: () => [string, string];
  customTimePeriod: CustomTimePeriod;
  changeCustomTimePeriod: (props: ChangeCustomTimePeriodProps) => void;
}

interface OnTimePeriodChangeProps {
  selectedTimePeriodId?: TimePeriodId;
  selectedCustomTimePeriod?: StoredCustomTimePeriod;
}

interface Props {
  defaultSelectedTimePeriodId?: TimePeriodId;
  defaultSelectedCustomTimePeriod?: StoredCustomTimePeriod;
  onTimePeriodChange?: ({
    selectedTimePeriodId,
    selectedCustomTimePeriod,
  }: OnTimePeriodChangeProps) => void;
}

interface GraphQueryParametersProps {
  timePeriod?: TimePeriod | null;
  startDate?: Date;
  endDate?: Date;
}

const useTimePeriod = ({
  defaultSelectedTimePeriodId,
  defaultSelectedCustomTimePeriod,
  onTimePeriodChange,
}: Props): TimePeriodState => {
  const defaultTimePeriod = cond([
    [
      (timePeriodId) =>
        and(isNil(timePeriodId), isNil(defaultSelectedCustomTimePeriod)),
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

  const getTimeperiodFromNow = (
    timePeriod: TimePeriod | null,
  ): CustomTimePeriod => {
    return {
      start: new Date(timePeriod?.getStart() || 0),
      end: new Date(Date.now()),
    };
  };

  const getNewCustomTimePeriod = ({ start, end }): CustomTimePeriod => {
    const customTimePeriodInDay = dayjs
      .duration(dayjs(end).diff(dayjs(start)))
      .asDays();
    const xAxisTickFormat = gte(customTimePeriodInDay, 2)
      ? dateFormat
      : timeFormat;
    const timelineLimit = cond<number, number>([
      [gte(1), always(20)],
      [gte(7), always(100)],
      [T, always(500)],
    ])(customTimePeriodInDay);

    return {
      start,
      end,
      xAxisTickFormat,
      timelineLimit,
    };
  };

  const [
    customTimePeriod,
    setCustomTimePeriod,
  ] = React.useState<CustomTimePeriod>(
    defaultSelectedCustomTimePeriod
      ? getNewCustomTimePeriod({
          start: new Date(propOr(0, 'start', defaultSelectedCustomTimePeriod)),
          end: new Date(propOr(0, 'end', defaultSelectedCustomTimePeriod)),
        })
      : getTimeperiodFromNow(defaultTimePeriod),
  );

  const getDates = (timePeriod): [string, string] => {
    if (isNil(timePeriod)) {
      return [
        customTimePeriod.start.toISOString(),
        customTimePeriod.end.toISOString(),
      ];
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
  }: GraphQueryParametersProps): string => {
    if (pipe(isNil, not)(timePeriod)) {
      const [start, end] = getDates(timePeriod);

      return `?start=${start}&end=${end}`;
    }

    return `?start=${startDate?.toISOString()}&end=${endDate?.toISOString()}`;
  };

  const [periodQueryParameters, setPeriodQueryParameters] = React.useState(
    getGraphQueryParameters(
      selectedTimePeriod
        ? { timePeriod: selectedTimePeriod }
        : { startDate: customTimePeriod.start, endDate: customTimePeriod.end },
    ),
  );

  const changeSelectedTimePeriod = (timePeriodId: TimePeriodId): void => {
    const timePeriod = getTimePeriodById(timePeriodId);

    setSelectedTimePeriod(timePeriod);
    onTimePeriodChange?.({ selectedTimePeriodId: timePeriod.id });

    const newTimePeriod = getTimeperiodFromNow(timePeriod);

    setCustomTimePeriod(newTimePeriod);

    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      timePeriod,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
  };

  const changeCustomTimePeriod = ({
    date,
    property,
  }: ChangeCustomTimePeriodProps): void => {
    const newCustomTimePeriod = getNewCustomTimePeriod({
      ...customTimePeriod,
      [property]: date,
    });
    setCustomTimePeriod(newCustomTimePeriod);
    onTimePeriodChange?.({
      selectedCustomTimePeriod: {
        start: newCustomTimePeriod.start.toISOString(),
        end: newCustomTimePeriod.end.toISOString(),
      },
    });
    setSelectedTimePeriod(null);
    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      startDate: newCustomTimePeriod.start,
      endDate: newCustomTimePeriod.end,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
  };

  return {
    changeSelectedTimePeriod,
    selectedTimePeriod,
    periodQueryParameters,
    getIntervalDates: (): [string, string] => getDates(selectedTimePeriod),
    customTimePeriod,
    changeCustomTimePeriod,
  };
};

export default useTimePeriod;
