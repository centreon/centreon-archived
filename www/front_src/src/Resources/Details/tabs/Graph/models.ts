import subHours from 'date-fns/subHours';
import subDays from 'date-fns/subDays';
import { find, propEq } from 'ramda';

import { timeFormat, dateTimeFormat, dateFormat } from '../../../Graph/format';
import {
  labelLast24h,
  labelLast7Days,
  labelLast31Days,
} from '../../../translatedLabels';

export type TimePeriodId = 'last_24_h' | 'last_7_days' | 'last_31_days';

export interface TimePeriod {
  getStart: () => Date;
  id: TimePeriodId;
  name: string;
  timeFormat: string;
}

const last24hPeriod: TimePeriod = {
  getStart: (): Date => subHours(Date.now(), 24),
  id: 'last_24_h',
  name: labelLast24h,
  timeFormat,
};

const last7Days: TimePeriod = {
  getStart: (): Date => subDays(Date.now(), 7),
  id: 'last_7_days',
  name: labelLast7Days,
  timeFormat: dateTimeFormat,
};

const last31Days: TimePeriod = {
  getStart: (): Date => subDays(Date.now(), 31),
  id: 'last_31_days',
  name: labelLast31Days,
  timeFormat: dateFormat,
};

const timePeriods: Array<TimePeriod> = [last24hPeriod, last7Days, last31Days];

const getTimePeriodById = (id: TimePeriodId): TimePeriod =>
  find<TimePeriod>(propEq('id', id))(timePeriods) as TimePeriod;

export { timePeriods, getTimePeriodById, last24hPeriod };
