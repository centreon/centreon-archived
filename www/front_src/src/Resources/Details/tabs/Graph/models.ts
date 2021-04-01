import dayjs from 'dayjs';
import { find, propEq } from 'ramda';

import { timeFormat, dateFormat } from '@centreon/ui';

import {
  label1D,
  label7D,
  label31D,
  label31Days,
  labelLast31Days,
  label7Days,
  labelLast7Days,
  label1Day,
  labelLastDay,
} from '../../../translatedLabels';

export type TimePeriodId = 'last_24_h' | 'last_7_days' | 'last_31_days';

export interface TimePeriod {
  id: TimePeriodId;
  compactName: string;
  name: string;
  largeName: string;
  getStart: () => Date;
  dateTimeFormat: string;
  timelineEventsLimit: number;
}

export interface CustomTimePeriod {
  start: Date;
  end: Date;
  xAxisTickFormat?: string;
  timelineLimit?: number;
}

export interface StoredCustomTimePeriod {
  start: string;
  end: string;
}

export enum CustomTimePeriodProperty {
  start = 'start',
  end = 'end',
}

export interface ChangeCustomTimePeriodProps {
  date: Date;
  property: CustomTimePeriodProperty;
}

const last24hPeriod: TimePeriod = {
  compactName: label1D,
  name: label1Day,
  largeName: labelLastDay,
  id: 'last_24_h',
  getStart: (): Date => dayjs(Date.now()).subtract(24, 'hour').toDate(),
  dateTimeFormat: timeFormat,
  timelineEventsLimit: 20,
};

const last7Days: TimePeriod = {
  compactName: label7D,
  name: label7Days,
  largeName: labelLast7Days,
  id: 'last_7_days',
  getStart: (): Date => dayjs(Date.now()).subtract(7, 'day').toDate(),
  dateTimeFormat: dateFormat,
  timelineEventsLimit: 100,
};

const last31Days: TimePeriod = {
  compactName: label31D,
  name: label31Days,
  largeName: labelLast31Days,
  id: 'last_31_days',
  getStart: (): Date => dayjs(Date.now()).subtract(31, 'day').toDate(),
  dateTimeFormat: dateFormat,
  timelineEventsLimit: 500,
};

const timePeriods: Array<TimePeriod> = [last24hPeriod, last7Days, last31Days];

const getTimePeriodById = (id: TimePeriodId): TimePeriod =>
  find<TimePeriod>(propEq('id', id))(timePeriods) as TimePeriod;

export { timePeriods, getTimePeriodById, last24hPeriod, last7Days, last31Days };
