import subHours from 'date-fns/subHours';
import subDays from 'date-fns/subDays';
import { find, propEq } from 'ramda';

import {
  timeFormat,
  dateTimeFormat,
  dateFormat,
} from '../../../../Graph/format';
import {
  labelLast24h,
  labelLast7Days,
  labelLast31Days,
} from '../../../../translatedLabels';

export type TimePeriodId = 'last_24_h' | 'last_7_days' | 'last_31_days';

export interface TimePeriod {
  id: TimePeriodId;
  name: string;
  getStart: () => Date;
  timeFormat: string;
}

const last24hPeriod: TimePeriod = {
  name: labelLast24h,
  id: 'last_24_h',
  getStart: (): Date => subHours(Date.now(), 24),
  timeFormat,
};

const last7Days: TimePeriod = {
  name: labelLast7Days,
  id: 'last_7_days',
  getStart: (): Date => subDays(Date.now(), 7),
  timeFormat: dateTimeFormat,
};

const last31Days: TimePeriod = {
  name: labelLast31Days,
  id: 'last_31_days',
  getStart: (): Date => subDays(Date.now(), 31),
  timeFormat: dateFormat,
};

const timePeriods: Array<TimePeriod> = [last24hPeriod, last7Days, last31Days];

const getTimePeriodById = (id: TimePeriodId): TimePeriod =>
  find<TimePeriod>(propEq('id', id))(timePeriods) as TimePeriod;

export { timePeriods, getTimePeriodById, last24hPeriod };
