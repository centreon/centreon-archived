import { useUserContext } from './UserContext';

interface DateOptions {
  date: Date;
  options?: Omit<Intl.DateTimeFormatOptions, 'timeZone'>;
}

const useLocaleDateTimeFormat = (): ((dateOptions: DateOptions) => string) => {
  const { locale, timezone } = useUserContext();

  const localeDateTimeFormat = ({
    date,
    options = {},
  }: DateOptions): string => {
    const normalizedLocale = locale?.replace('_', '-');

    return date.toLocaleString(normalizedLocale, {
      ...options,
      timeZone: timezone,
    });
  };

  return localeDateTimeFormat;
};

export default useLocaleDateTimeFormat;
