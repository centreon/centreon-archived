/* eslint-disable class-methods-use-this */
import DayjsAdapter from '@date-io/dayjs';
import dayjs from 'dayjs';
import { includes } from 'ramda';

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

  class Adapter extends DayjsAdapter {
    public format(date, formatString): string {
      return format({ date, formatString });
    }

    public date(value): dayjs.Dayjs {
      return dayjs(value).locale(locale);
    }

    public startOfMonth(date: dayjs.Dayjs): dayjs.Dayjs {
      return dayjs(date.tz()).startOf('month');
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

    public setHours(date: dayjs.Dayjs, count: number): dayjs.Dayjs {
      return date.locale(locale).tz(timezone).set('hour', count);
    }
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
