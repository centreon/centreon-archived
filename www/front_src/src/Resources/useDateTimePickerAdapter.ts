/* eslint-disable class-methods-use-this */
import DayjsAdapter from '@date-io/dayjs';
import dayjs from 'dayjs';

import { useLocaleDateTimeFormat } from '@centreon/ui';

const useDateTimePickerAdapter = (): typeof DayjsAdapter => {
  const { format, toDateTime } = useLocaleDateTimeFormat();

  class Adapter extends DayjsAdapter {
    public format(date, formatString): string {
      return format({ date, formatString });
    }

    public date(value): dayjs.Dayjs {
      return dayjs(toDateTime(dayjs(value).toDate()));
    }
  }

  return Adapter;
};

export default useDateTimePickerAdapter;
