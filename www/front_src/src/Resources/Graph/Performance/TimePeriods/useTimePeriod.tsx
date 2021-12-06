import * as React from 'react';

import { always, cond, equals, gte, isNil, not, pipe, propOr, T } from 'ramda';
import dayjs from 'dayjs';
import duration from 'dayjs/plugin/duration';

import { dateFormat, timeFormat } from '@centreon/ui';

import {
  TimePeriod,
  getTimePeriodById,
  TimePeriodId,
  CustomTimePeriod,
  ChangeCustomTimePeriodProps,
  lastDayPeriod,
} from '../../../Details/tabs/Graph/models';
import { ResourceDetails } from '../../../Details/models';
import { AdjustTimePeriodProps } from '../models';

dayjs.extend(duration);

interface TimePeriodState {
  adjustTimePeriod: (props: AdjustTimePeriodProps) => void;
  changeCustomTimePeriod: (props: ChangeCustomTimePeriodProps) => void;
  changeSelectedTimePeriod: (timePeriod: TimePeriodId) => void;
  customTimePeriod: CustomTimePeriod;
  getIntervalDates: () => [string, string];
  periodQueryParameters: string;
  resourceDetailsUpdated: boolean;
  selectedTimePeriod: TimePeriod | null;
}

interface Props {
  defaultSelectedCustomTimePeriod?: CustomTimePeriod;
  defaultSelectedTimePeriodId?: TimePeriodId;
  details?: ResourceDetails;
  sending?: boolean;
}

interface GraphQueryParametersProps {
  endDate?: Date;
  startDate?: Date;
  timePeriod?: TimePeriod | null;
}

const useTimePeriod = ({
  details,
  sending = false,
  defaultSelectedTimePeriodId,
  defaultSelectedCustomTimePeriod,
}: Props): TimePeriodState => {
  const [resourceDetailsUpdated, setResourceDetailsUpdated] =
    React.useState<boolean>(false);

  const defaultTimePeriod = lastDayPeriod;

  const [selectedTimePeriod, setSelectedTimePeriod] =
    React.useState<TimePeriod | null>(defaultTimePeriod);

  const getTimeperiodFromNow = (
    timePeriod: TimePeriod | null,
  ): CustomTimePeriod => {
    return {
      end: new Date(Date.now()),
      start: new Date(timePeriod?.getStart() || 0),
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
      end,
      start,
      timelineLimit,
      xAxisTickFormat,
    };
  };

  const [customTimePeriod, setCustomTimePeriod] =
    React.useState<CustomTimePeriod>(getTimeperiodFromNow(defaultTimePeriod));

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
        : { endDate: customTimePeriod.end, startDate: customTimePeriod.start },
    ),
  );

  const changeSelectedTimePeriod = (timePeriodId: TimePeriodId): void => {
    const timePeriod = getTimePeriodById(timePeriodId);

    setSelectedTimePeriod(timePeriod);

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
    setSelectedTimePeriod(null);
    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      endDate: newCustomTimePeriod.end,
      startDate: newCustomTimePeriod.start,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
    setResourceDetailsUpdated(false);
  };

  const adjustTimePeriod = (
    adjustTimePeriodProps: AdjustTimePeriodProps,
  ): void => {
    setResourceDetailsUpdated(false);
    setCustomTimePeriod(getNewCustomTimePeriod(adjustTimePeriodProps));
    setSelectedTimePeriod(null);

    const { start, end } = adjustTimePeriodProps;

    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      endDate: end,
      startDate: start,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
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

  React.useEffect(() => {
    if (
      not(isNil(defaultSelectedTimePeriodId)) ||
      isNil(defaultSelectedCustomTimePeriod) ||
      (equals(defaultSelectedCustomTimePeriod.start, customTimePeriod.start) &&
        equals(defaultSelectedCustomTimePeriod.end, customTimePeriod.end))
    ) {
      return;
    }

    const newCustomTimePeriod = getNewCustomTimePeriod({
      end: new Date(propOr(0, 'end', defaultSelectedCustomTimePeriod)),
      start: new Date(propOr(0, 'start', defaultSelectedCustomTimePeriod)),
    });

    setCustomTimePeriod(newCustomTimePeriod);
    setSelectedTimePeriod(null);
    const queryParams = getGraphQueryParameters({
      endDate: newCustomTimePeriod.end,
      startDate: newCustomTimePeriod.start,
    });
    setPeriodQueryParameters(queryParams);
  }, [defaultSelectedCustomTimePeriod]);

  React.useEffect(() => {
    if (
      isNil(defaultSelectedTimePeriodId) ||
      equals(defaultSelectedTimePeriodId, selectedTimePeriod?.id)
    ) {
      return;
    }

    const newTimePeriod = getTimePeriodById(
      defaultSelectedTimePeriodId as TimePeriodId,
    );

    setSelectedTimePeriod(newTimePeriod);
    const queryParamsForSelectedPeriodId = getGraphQueryParameters({
      timePeriod: newTimePeriod,
    });
    setPeriodQueryParameters(queryParamsForSelectedPeriodId);
  }, [defaultSelectedTimePeriodId]);

  return {
    adjustTimePeriod,
    changeCustomTimePeriod,
    changeSelectedTimePeriod,
    customTimePeriod,
    getIntervalDates: (): [string, string] => getDates(selectedTimePeriod),
    periodQueryParameters,
    resourceDetailsUpdated,
    selectedTimePeriod,
  };
};

export default useTimePeriod;
