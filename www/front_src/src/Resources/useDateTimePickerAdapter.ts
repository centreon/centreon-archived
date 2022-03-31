/* eslint-disable class-methods-use-this */
import * as React from 'react';

import DayjsAdapter from '@date-io/dayjs';
import dayjs from 'dayjs';
import { equals, isNil, not } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useLocaleDateTimeFormat } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

interface UseDateTimePickerAdapterProps {
  Adapter;
  formatKeyboardValue: (value?: string) => string | undefined;
  getLocalAndConfiguredTimezoneOffset: () => number;
}

enum DSTState {
  SUMMER,
  WINTER,
  UNKNOWN,
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

  const hasDST = React.useCallback(
    (date: dayjs.Dayjs = dayjs()): DSTState => {
      const currentYear = new Date(
        new Date().toLocaleDateString('en-US', { timeZone: timezone }),
      ).getFullYear();
      const january = new Date(
        new Date(currentYear, 0, 1).toLocaleDateString('en-US', {
          timeZone: timezone,
        }),
      ).getTimezoneOffset();
      const july = new Date(
        new Date(currentYear, 6, 1).toLocaleDateString('en-US', {
          timeZone: timezone,
        }),
      ).getTimezoneOffset();

      if (equals(january, july)) {
        return DSTState.UNKNOWN;
      }

      return Math.max(january, july) !==
        new Date(
          date.toDate().toLocaleDateString('en-US', { timeZone: timezone }),
        ).getTimezoneOffset()
        ? DSTState.SUMMER
        : DSTState.WINTER;
    },
    [timezone],
  );

  const formatKeyboardValue = (value?: string): string | undefined => {
    if (equals(normalizedLocale, 'en') || isNil(value)) {
      return value;
    }
    const month = value.substring(0, 2);
    const day = value.substring(3, 5);

    const newValue = `${day}/${month}/${value.substring(6, 16)}`;

    return newValue;
  };

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
      return date.tz(timezone).get('hour');
    };

    public setHours = (date: dayjs.Dayjs, count: number): dayjs.Dayjs => {
      if (
        (equals(getLocalAndConfiguredTimezoneOffset(), 1) &&
          not(equals(hasDST(date), DSTState.SUMMER))) ||
        equals(timezone, 'UTC')
      ) {
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
      const isSameMonth = this.isSameYear(date, comparing)
        ? this.isSameMonth(date, comparing)
        : false;

      return (
        isSameMonth && date.tz(timezone).isSame(comparing.tz(timezone), 'day')
      );
    };

    public startOfDay = (date: dayjs.Dayjs): dayjs.Dayjs => {
      return date.tz(timezone).endOf('day') as dayjs.Dayjs;
    };

    public endOfDay = (date: dayjs.Dayjs): dayjs.Dayjs => {
      return date.tz(timezone).endOf('day') as dayjs.Dayjs;
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
      const dateWithTimezone = date.tz(timezone);
      const timeWithTimezone = time.tz(timezone);

      if (
        equals(timezone, 'UTC') &&
        not(equals(hasDST(dateWithTimezone), DSTState.WINTER))
      ) {
        return dateWithTimezone
          .add(
            timeWithTimezone.hour() -
              (equals(hasDST(), DSTState.UNKNOWN) ? 0 : 1),
            'hour',
          )
          .add(timeWithTimezone.minute(), 'minute')
          .add(timeWithTimezone.second(), 'second');
      }

      if (not(equals(hasDST(dateWithTimezone), DSTState.WINTER))) {
        return dateWithTimezone
          .hour(timeWithTimezone.hour())
          .minute(timeWithTimezone.minute())
          .second(timeWithTimezone.second());
      }

      if (hasDST() !== hasDST(dateWithTimezone)) {
        const offset =
          new Date(
            dateWithTimezone.toDate().toLocaleDateString('en-US', {
              timeZone: timezone,
            }),
          ).getTimezoneOffset() / 60;

        return dateWithTimezone
          .add(timeWithTimezone.hour() + (offset - 1), 'hour')
          .add(timeWithTimezone.minute(), 'minute')
          .add(timeWithTimezone.second(), 'second');
      }

      return dateWithTimezone
        .add(timeWithTimezone.hour(), 'hour')
        .add(timeWithTimezone.minute(), 'minute')
        .add(timeWithTimezone.second(), 'second');
    };
  }

  return {
    Adapter,
    formatKeyboardValue,
    getLocalAndConfiguredTimezoneOffset,
  };
};

export default useDateTimePickerAdapter;
