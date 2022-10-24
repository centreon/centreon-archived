import dayjs from 'dayjs';
import { atom } from 'jotai';
import { always, cond, gte, isNil, not, pipe, T } from 'ramda';

import { dateFormat, timeFormat } from '@centreon/ui';

import {
  CustomTimePeriod,
  getTimePeriodById,
  lastDayPeriod,
  TimePeriod,
  TimePeriodId
} from '../../../Details/tabs/Graph/models';
import { AdjustTimePeriodProps } from '../models';

interface GraphQueryParametersProps {
  endDate?: Date;
  startDate?: Date;
  timePeriod?: TimePeriod | null;
}

const defaultTimePeriod = lastDayPeriod;

export const resourceDetailsUpdatedAtom = atom(false);
export const selectedTimePeriodAtom = atom<TimePeriod | null>(
  defaultTimePeriod
);

export const getTimeperiodFromNow = (
  timePeriod: TimePeriod | null
): CustomTimePeriod => {
  return {
    end: new Date(Date.now()),
    start: new Date(timePeriod?.getStart() || 0)
  };
};

export const customTimePeriodAtom = atom(
  getTimeperiodFromNow(defaultTimePeriod)
);

interface GetNewCustomTimePeriodProps {
  end: Date;
  start: Date;
}

export const getNewCustomTimePeriod = ({
  start,
  end
}: GetNewCustomTimePeriodProps): CustomTimePeriod => {
  const customTimePeriodInDay = dayjs
    .duration(dayjs(end).diff(dayjs(start)))
    .asDays();
  const xAxisTickFormat = gte(customTimePeriodInDay, 2)
    ? dateFormat
    : timeFormat;
  const timelineLimit = cond<number, number>([
    [gte(1), always(20)],
    [gte(7), always(100)],
    [T, always(500)]
  ])(customTimePeriodInDay);

  return {
    end,
    start,
    timelineLimit,
    xAxisTickFormat
  };
};

export const getDatesDerivedAtom = atom(
  (get) =>
    (timePeriod?: TimePeriod | null): [string, string] => {
      const customTimePeriod = get(customTimePeriodAtom);

      if (isNil(timePeriod)) {
        return [
          customTimePeriod.start.toISOString(),
          customTimePeriod.end.toISOString()
        ];
      }

      return [
        timePeriod.getStart().toISOString(),
        new Date(Date.now()).toISOString()
      ];
    }
);

export const graphQueryParametersDerivedAtom = atom(
  (get) =>
    ({ timePeriod, startDate, endDate }: GraphQueryParametersProps): string => {
      const getDates = get(getDatesDerivedAtom);

      if (pipe(isNil, not)(timePeriod)) {
        const [start, end] = getDates(timePeriod);

        return `?start=${start}&end=${end}`;
      }

      return `?start=${startDate?.toISOString()}&end=${endDate?.toISOString()}`;
    }
);

export const changeSelectedTimePeriodDerivedAtom = atom(
  null,
  (_, set, timePeriodId: TimePeriodId) => {
    const timePeriod = getTimePeriodById(timePeriodId);

    set(selectedTimePeriodAtom, timePeriod);

    const newCustomTimePeriod = getTimeperiodFromNow(timePeriod);

    set(customTimePeriodAtom, newCustomTimePeriod);
    set(resourceDetailsUpdatedAtom, false);
  }
);

export const changeCustomTimePeriodDerivedAtom = atom(
  null,
  (get, set, { date, property }) => {
    const customTimePeriod = get(customTimePeriodAtom);

    const newCustomTimePeriod = getNewCustomTimePeriod({
      ...customTimePeriod,
      [property]: date
    });

    set(customTimePeriodAtom, newCustomTimePeriod);
    set(selectedTimePeriodAtom, null);
    set(resourceDetailsUpdatedAtom, false);
  }
);

export const adjustTimePeriodDerivedAtom = atom(
  null,
  (_, set, adjustTimePeriodProps: AdjustTimePeriodProps) => {
    set(resourceDetailsUpdatedAtom, false);
    set(customTimePeriodAtom, getNewCustomTimePeriod(adjustTimePeriodProps));
    set(selectedTimePeriodAtom, null);
  }
);
