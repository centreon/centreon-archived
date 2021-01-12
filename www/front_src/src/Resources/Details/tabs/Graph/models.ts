import dayjs from 'dayjs';
import { find, propEq } from 'ramda';

import { timeFormat, dateFormat } from '@centreon/ui';

import {
  labelLast24h,
  labelLast7Days,
  labelLast31Days,
} from '../../../translatedLabels';

export type TimePeriodId = 'last_24_h' | 'last_7_days' | 'last_31_days';

export interface TimePeriod {
  id: TimePeriodId;
  name: string;
  getStart: () => Date;
  dateTimeFormat: string;
  timelineEventsLimit: number;
}

const last24hPeriod: TimePeriod = {
  name: labelLast24h,
  id: 'last_24_h',
  getStart: (): Date => dayjs(Date.now()).subtract(24, 'hour').toDate(),
  dateTimeFormat: timeFormat,
  timelineEventsLimit: 20,
};

const last7Days: TimePeriod = {
  name: labelLast7Days,
  id: 'last_7_days',
  getStart: (): Date => dayjs(Date.now()).subtract(7, 'day').toDate(),
  dateTimeFormat: dateFormat,
  timelineEventsLimit: 100,
};

const last31Days: TimePeriod = {
  name: labelLast31Days,
  id: 'last_31_days',
  getStart: (): Date => dayjs(Date.now()).subtract(31, 'day').toDate(),
  dateTimeFormat: dateFormat,
  timelineEventsLimit: 500,
};

const timePeriods: Array<TimePeriod> = [last24hPeriod, last7Days, last31Days];

const getTimePeriodById = (id: TimePeriodId): TimePeriod =>
  find<TimePeriod>(propEq('id', id))(timePeriods) as TimePeriod;

export { timePeriods, getTimePeriodById, last24hPeriod, last7Days, last31Days };
