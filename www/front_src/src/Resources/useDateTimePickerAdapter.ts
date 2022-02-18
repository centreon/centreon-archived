/* eslint-disable class-methods-use-this */
import DayjsAdapter from '@date-io/dayjs';
import dayjs from 'dayjs';
import { equals, includes } from 'ramda';

import { useUserContext } from '@centreon/ui-context';
import { useLocaleDateTimeFormat } from '@centreon/ui';

interface UseDateTimePickerAdapterProps {
  Adapter: typeof DayjsAdapter;
  isMeridianFormat: (date: Date) => boolean;
}

const meridians = ['AM', 'PM'];

const useDateTimePickerAdapter = (): UseDateTimePickerAdapterProps => {
  const { locale, timezone } = useUserContext();
  const { format, toTime } = useLocaleDateTimeFormat();

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

  class Adapter extends DayjsAdapter {
    public format(date, formatString): string {
      return format({ date, formatString });
    }

    public date(value): dayjs.Dayjs {
      return dayjs(value).locale(locale);
    }

    public isEqual = (value, comparing): boolean => {
      if (value === null && comparing === null) {
        return true;
      }

      return dayjs(value).isSame(dayjs(comparing), 'minute');
    };

    public getHours(date): number {
      return date.locale(locale).tz(timezone).hour();
    }

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
      return date.tz(timezone).isSame(comparing.tz(timezone), 'day');
    };

    public startOfDay = (date: dayjs.Dayjs): dayjs.Dayjs => {
      return date.tz(timezone).startOf('day') as dayjs.Dayjs;
    };

    public endOfDay = (date: dayjs.Dayjs): dayjs.Dayjs => {
      return date.tz(timezone).startOf('day') as dayjs.Dayjs;
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

    public getWeekdays = (): Array<string> => {
      const start = dayjs().locale(locale).tz(timezone).startOf('week');

      return [0, 1, 2, 3, 4, 5, 6].map((diff) =>
        this.format(start.add(diff, 'day'), 'dd'),
      );
    };

    public mergeDateAndTime = (
      date: dayjs.Dayjs,
      time: dayjs.Dayjs,
    ): dayjs.Dayjs => {
      const dateWithTimezone = date.tz(timezone);
      const timeWithTimezone = time.tz(timezone);

      if (equals(timezone, 'UTC')) {
        return dateWithTimezone
          .add(timeWithTimezone.hour(), 'hour')
          .add(timeWithTimezone.minute(), 'minute')
          .add(timeWithTimezone.second(), 'second');
      }

      return dateWithTimezone
        .hour(timeWithTimezone.hour())
        .minute(timeWithTimezone.minute())
        .second(timeWithTimezone.second()) as dayjs.Dayjs;
    };
  }

  const isMeridianFormat = (date: Date): boolean => {
    const localizedTime = toTime(date);

    return meridians.some((meridian) => includes(meridian, localizedTime));
  };

  return {
    Adapter,
    isMeridianFormat,
  };
};

export default useDateTimePickerAdapter;
