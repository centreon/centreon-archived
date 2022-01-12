/* eslint-disable class-methods-use-this */
import DayjsAdapter from '@date-io/dayjs';
import dayjs from 'dayjs';
import { equals } from 'ramda';
import { useAtomValue } from 'jotai/utils';

import { useLocaleDateTimeFormat } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

interface UseDateTimePickerAdapterProps {
  Adapter;
  getLocalAndConfiguredTimezoneOffset: () => number;
}

const useDateTimePickerAdapter = (): UseDateTimePickerAdapterProps => {
  const { timezone } = useAtomValue(userAtom);
  const { format } = useLocaleDateTimeFormat();

  const getLocalAndConfiguredTimezoneOffset = (): number => {
    const localTimezone = new Intl.DateTimeFormat().resolvedOptions().timeZone;
    const now = new Date();

    const localDate = dayjs.tz(now, localTimezone).format('H');
    const dateWithConfiguredTimezone = dayjs.tz(now, timezone).format('H');

    return Number(localDate) - Number(dateWithConfiguredTimezone);
  };

  class Adapter extends DayjsAdapter {
    public formatByString = (value, formatKey: string): string => {
      return format({ date: value, formatString: formatKey });
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
      return date.set('hour', count + getLocalAndConfiguredTimezoneOffset());
    };
  }

  return {
    Adapter,
    getLocalAndConfiguredTimezoneOffset,
  };
};

export default useDateTimePickerAdapter;
