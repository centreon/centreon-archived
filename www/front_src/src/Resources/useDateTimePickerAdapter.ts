/* eslint-disable class-methods-use-this */
import DayjsAdapter from '@date-io/dayjs';
import dayjs from 'dayjs';

import { useUserContext } from '@centreon/ui-context';
import { useLocaleDateTimeFormat } from '@centreon/ui';

const useDateTimePickerAdapter = (): typeof DayjsAdapter => {
  const { locale, timezone } = useUserContext();
  const { format } = useLocaleDateTimeFormat();

  class Adapter extends DayjsAdapter {
    public format(date, formatString): string {
      return format({ date, formatString });
    }

    public date(value): dayjs.Dayjs {
      return dayjs(value).locale(locale);
    }

    public startOfMonth(date: dayjs.Dayjs) {
      return dayjs(date.tz()).startOf('month');
    }

    public isEqual = (value, comparing) => {
      if (value === null && comparing === null) {
        return true;
      }

      return dayjs(value).isSame(dayjs(comparing), 'minute');
    };

    public getHours(date) {
      return date.locale(locale).tz(timezone).hour();
    }

    public setHours(date: dayjs.Dayjs, count: number) {
      return date.locale(locale).tz(timezone).set('hour', count);
    }
  }

  return Adapter;
};

export default useDateTimePickerAdapter;
