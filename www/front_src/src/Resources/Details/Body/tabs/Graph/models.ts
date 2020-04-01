import subHours from 'date-fns/subHours';
import subDays from 'date-fns/subDays';
import { find, propEq } from 'ramda';

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
}

const last24hPeriod: TimePeriod = {
  name: labelLast24h,
  id: 'last_24_h',
  getStart: (): Date => subHours(Date.now(), 24),
};

const last7Days: TimePeriod = {
  name: labelLast7Days,
  id: 'last_7_days',
  getStart: (): Date => subDays(Date.now(), 7),
};

const last31Days: TimePeriod = {
  name: labelLast31Days,
  id: 'last_31_days',
  getStart: (): Date => subDays(Date.now(), 31),
};

const timePeriods: Array<TimePeriod> = [last24hPeriod, last7Days, last31Days];

const getTimePeriodById = (id): TimePeriod =>
  find(propEq('id', id))(timePeriods);

export { timePeriods, getTimePeriodById };
