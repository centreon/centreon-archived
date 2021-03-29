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
      return dayjs(value).locale(locale).tz(timezone);
    }

    public startOfMonth(date: dayjs.Dayjs) {
      return date;
    }
  }

  return Adapter;
};

export default useDateTimePickerAdapter;
