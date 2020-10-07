import timezonePlugin from 'dayjs/plugin/timezone';
import dayjs from 'dayjs';

import { useUserContext } from './UserContext';

dayjs.extend(timezonePlugin);

interface DateOptions {
  date: Date | string;
  options?: Omit<Intl.DateTimeFormatOptions, 'timeZone'>;
}

interface LocaleDateTimeFormat {
  format: (dateOptions: DateOptions) => string;
  toDate: (date: Date | string) => string;
  toDateTime: (date: Date | string) => string;
  toTime: (date: Date | string) => string;
  toIsoString: (date: Date) => string;
}

const timeFormatOptions = {
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
};

const dateFormatOptions = {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
};

const dateTimeFormatOptions = {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric',
  hour: '2-digit',
  minute: '2-digit',
  hour12: false,
};

const useLocaleDateTimeFormat = (): LocaleDateTimeFormat => {
  const { locale, timezone } = useUserContext();

  const format = ({ date, options = {} }: DateOptions): string => {
    const normalizedLocale = locale?.replace('_', '-');

    return new Date(date).toLocaleString(normalizedLocale, {
      ...options,
      timeZone: timezone,
    });
  };

  const toDateTime = (date: Date | string): string => {
    return format({
      date,
      options: dateTimeFormatOptions,
    });
  };

  const toDate = (date: Date | string): string => {
    return format({
      date,
      options: dateFormatOptions,
    });
  };

  const toTime = (date: Date | string): string => {
    return format({
      date,
      options: timeFormatOptions,
    });
  };

  const toIsoString = (date: Date): string => {
    return `${new Date(date).toISOString().substring(0, 19)}Z`;
  };

  return { format, toDateTime, toDate, toTime, toIsoString };
};

export default useLocaleDateTimeFormat;
export { timeFormatOptions, dateFormatOptions, dateTimeFormatOptions };
