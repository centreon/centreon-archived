import dayjs from 'dayjs';
import { find, propEq } from 'ramda';

import { timeFormat, dateFormat } from '@centreon/ui';

import {
  label31Days,
  labelLast31Days,
  label7Days,
  labelLast7Days,
  label1Day,
  labelLastDay,
} from '../../../translatedLabels';

export type TimePeriodId = 'last_24_h' | 'last_7_days' | 'last_31_days';

export interface TimePeriod {
  dateTimeFormat: string;
  getStart: () => Date;
  id: TimePeriodId;
  largeName: string;
  name: string;
  timelineEventsLimit: number;
}

export interface CustomTimePeriod {
  end: Date;
  start: Date;
  timelineLimit?: number;
  xAxisTickFormat?: string;
}

export interface StoredCustomTimePeriod {
  end: string;
  start: string;
}

export enum CustomTimePeriodProperty {
  end = 'end',
  start = 'start',
}

export interface ChangeCustomTimePeriodProps {
  date: Date;
  property: CustomTimePeriodProperty;
}

const lastDayPeriod: TimePeriod = {
  dateTimeFormat: timeFormat,
  getStart: (): Date => dayjs(Date.now()).subtract(24, 'hour').toDate(),
  id: 'last_24_h',
  largeName: labelLastDay,
  name: label1Day,
  timelineEventsLimit: 20,
};

const last7Days: TimePeriod = {
  dateTimeFormat: dateFormat,
  getStart: (): Date => dayjs(Date.now()).subtract(7, 'day').toDate(),
  id: 'last_7_days',
  largeName: labelLast7Days,
  name: label7Days,
  timelineEventsLimit: 100,
};

const last31Days: TimePeriod = {
  dateTimeFormat: dateFormat,
  getStart: (): Date => dayjs(Date.now()).subtract(31, 'day').toDate(),
  id: 'last_31_days',
  largeName: labelLast31Days,
  name: label31Days,
  timelineEventsLimit: 500,
};

const timePeriods: Array<TimePeriod> = [lastDayPeriod, last7Days, last31Days];

const getTimePeriodById = (id: TimePeriodId): TimePeriod =>
  find<TimePeriod>(propEq('id', id))(timePeriods) as TimePeriod;

export { timePeriods, getTimePeriodById, lastDayPeriod, last7Days, last31Days };
