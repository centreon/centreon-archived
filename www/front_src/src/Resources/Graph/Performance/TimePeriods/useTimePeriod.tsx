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
import {
  GraphOptions,
  GraphTabParameters,
  ResourceDetails,
} from '../../../Details/models';
import { AdjustTimePeriodProps } from '../models';
import { useResourceContext } from '../../../Context';

dayjs.extend(duration);

interface TimePeriodState {
  changeSelectedTimePeriod: (timePeriod: TimePeriodId) => void;
  selectedTimePeriod: TimePeriod | null;
  periodQueryParameters: string;
  getIntervalDates: () => [string, string];
  customTimePeriod: CustomTimePeriod;
  resourceDetailsUpdated: boolean;
  changeCustomTimePeriod: (props: ChangeCustomTimePeriodProps) => void;
  adjustTimePeriod: (props: AdjustTimePeriodProps) => void;
}

interface Props {
  defaultSelectedTimePeriodId?: TimePeriodId;
  defaultSelectedCustomTimePeriod?: StoredCustomTimePeriod;
  defaultGraphOptions?: GraphOptions;
  details?: ResourceDetails;
  onTimePeriodChange?: ({
    selectedTimePeriodId,
    selectedCustomTimePeriod,
    graphOptions,
  }: GraphTabParameters) => void;
}

interface GraphQueryParametersProps {
  timePeriod?: TimePeriod | null;
  startDate?: Date;
  endDate?: Date;
}

const useTimePeriod = ({
  defaultSelectedTimePeriodId,
  defaultSelectedCustomTimePeriod,
  defaultGraphOptions,
  details,
  onTimePeriodChange,
}: Props): TimePeriodState => {
  const [
    resourceDetailsUpdated,
    setResourceDetailsUpdated,
  ] = React.useState<boolean>(false);
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

  const { sending } = useResourceContext();

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
    onTimePeriodChange?.({
      selectedTimePeriodId: timePeriod.id,
      graphOptions: defaultGraphOptions,
    });

    const newTimePeriod = getTimeperiodFromNow(timePeriod);

    setCustomTimePeriod(newTimePeriod);

    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      timePeriod,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
    setResourceDetailsUpdated(false);
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
      graphOptions: defaultGraphOptions,
    });
    setSelectedTimePeriod(null);
    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      startDate: newCustomTimePeriod.start,
      endDate: newCustomTimePeriod.end,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
    setResourceDetailsUpdated(false);
  };

  const adjustTimePeriod = (adjustTimePeriodProps: AdjustTimePeriodProps) => {
    setResourceDetailsUpdated(false);
    setCustomTimePeriod(getNewCustomTimePeriod(adjustTimePeriodProps));
    setSelectedTimePeriod(null);

    const { start, end } = adjustTimePeriodProps;

    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      startDate: start,
      endDate: end,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
    onTimePeriodChange?.({
      selectedCustomTimePeriod: {
        start: start.toISOString(),
        end: end.toISOString(),
      },
      graphOptions: defaultGraphOptions,
    });
  };

  React.useEffect(() => {
    if (isNil(selectedTimePeriod) || isNil(details) || not(sending)) {
      return;
    }

    setPeriodQueryParameters(
      getGraphQueryParameters({
        timePeriod: selectedTimePeriod,
      }),
    );

    const newTimePeriod = getTimeperiodFromNow(selectedTimePeriod);

    setCustomTimePeriod(newTimePeriod);
    setResourceDetailsUpdated(true);
  }, [sending]);

  return {
    changeSelectedTimePeriod,
    selectedTimePeriod,
    periodQueryParameters,
    getIntervalDates: (): [string, string] => getDates(selectedTimePeriod),
    customTimePeriod,
    changeCustomTimePeriod,
    adjustTimePeriod,
    resourceDetailsUpdated,
  };
};

export default useTimePeriod;
