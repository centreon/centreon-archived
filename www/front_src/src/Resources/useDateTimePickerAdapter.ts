/* eslint-disable class-methods-use-this */
import DayjsAdapter from '@date-io/dayjs';
import dayjs from 'dayjs';
import { equals, isNil } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useLocaleDateTimeFormat } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

interface UseDateTimePickerAdapterProps {
  Adapter;
  formatKeyboardValue: (value?: string) => string | undefined;
  getLocalAndConfiguredTimezoneOffset: () => number;
}

const useDateTimePickerAdapter = (): UseDateTimePickerAdapterProps => {
  const { timezone, locale } = useAtomValue(userAtom);
  const { format } = useLocaleDateTimeFormat();

  const normalizedLocale = locale.substring(0, 2);

  const getLocalAndConfiguredTimezoneOffset = (): number => {
    const now = new Date();
    const currentTimezoneDate = new Date(now.toLocaleString('en-US'));
    const destinationTimezoneDate = new Date(
      now.toLocaleString('en-US', {
        timeZone: timezone,
      }),
    );

    return (
      (currentTimezoneDate.getTime() - destinationTimezoneDate.getTime()) /
      60 /
      60 /
      1000
    );
  };

  const formatKeyboardValue = (value?: string): string | undefined => {
    if (equals(normalizedLocale, 'en') || isNil(value)) {
      return value;
    }
    const month = value.substring(0, 2);
    const day = value.substring(3, 5);

    const newValue = `${day}/${month}/${value.substring(6, 16)}`;

    return newValue;
  };

  const isUTCTimezone = equals('UTC');

  class Adapter extends DayjsAdapter {
    public formatByString = (value, formatKey: string): string => {
      return format({ date: value.tz(timezone), formatString: formatKey });
    };

    public isEqual = (value, comparing): boolean => {
      if (value === null && comparing === null) {
        return true;
      }

      return equals(
        format({ date: value, formatString: 'LT' }),
        format({ date: comparing, formatString: 'LT' }),
      );
    };

    public getHours = (date): number => {
      if (isUTCTimezone(timezone)) {
        return date.tz(timezone).get('hour');
      }

      return date.tz(timezone).clone().get('hour');
    };

    public setHours = (date: dayjs.Dayjs, count: number): dayjs.Dayjs => {
      if (equals(timezone, 'UTC')) {
        return date
          .tz(timezone)
          .set('hour', count - getLocalAndConfiguredTimezoneOffset());
      }

      return date.tz(timezone).set('hour', count);
    };

    public isSameHour = (
      date: dayjs.Dayjs,
      comparing: dayjs.Dayjs,
    ): boolean => {
      return date.tz(timezone).isSame(comparing.tz(timezone), 'hour');
    };

    public isSameDay = (date: dayjs.Dayjs, comparing: dayjs.Dayjs): boolean => {
      return equals(
        this.startOfDay(date).get('date'),
        this.startOfDay(comparing).get('date'),
      );
    };

    public startOfDay = (date: dayjs.Dayjs): dayjs.Dayjs => {
      if (isUTCTimezone(timezone)) {
        return date.tz(timezone).endOf('day');
      }

      return date.tz(timezone).clone().endOf('day') as dayjs.Dayjs;
    };

    public endOfDay = (date: dayjs.Dayjs): dayjs.Dayjs => {
      if (isUTCTimezone(timezone)) {
        return date.tz(timezone).endOf('day');
      }

      return date.tz(timezone).clone().endOf('day') as dayjs.Dayjs;
    };

    public startOfMonth = (date: dayjs.Dayjs): dayjs.Dayjs => {
      return date.tz(timezone).endOf('month') as dayjs.Dayjs;
    };

    public endOfMonth = (date: dayjs.Dayjs): dayjs.Dayjs => {
      return date.tz(timezone).endOf('month') as dayjs.Dayjs;
    };

    public isSameMonth = (
      date: dayjs.Dayjs,
      comparing: dayjs.Dayjs,
    ): boolean => {
      return date.tz(timezone).isSame(comparing.tz(timezone), 'month');
    };

    public getMonth = (date: dayjs.Dayjs): number => {
      return date.tz(timezone).month();
    };

    public getDaysInMonth = (date: dayjs.Dayjs): number => {
      return date.tz(timezone).daysInMonth();
    };

    public getWeekdays = (): Array<string> => {
      const start = dayjs().locale(locale).tz(timezone).startOf('week');

      return [0, 1, 2, 3, 4, 5, 6].map((diff) =>
        this.formatByString(start.add(diff, 'day'), 'dd'),
      );
    };

    public mergeDateAndTime = (
      date: dayjs.Dayjs,
      time: dayjs.Dayjs,
    ): dayjs.Dayjs => {
      if (isUTCTimezone(timezone)) {
        const dateWithTimezone = date.tz(timezone);
        const timeWithTimezone = time.tz(timezone);

        return dateWithTimezone
          .add(timeWithTimezone.hour(), 'hour')
          .add(timeWithTimezone.minute(), 'minute')
          .add(timeWithTimezone.second(), 'second');
      }

      const dateWithTimezone = date.tz(timezone).clone();
      const timeWithTimezone = time.tz(timezone).clone();

      return dateWithTimezone
        .hour(timeWithTimezone.hour())
        .minute(timeWithTimezone.minute())
        .second(timeWithTimezone.second()) as dayjs.Dayjs;
    };
  }

  return {
    Adapter,
    formatKeyboardValue,
    getLocalAndConfiguredTimezoneOffset,
  };
};

export default useDateTimePickerAdapter;
