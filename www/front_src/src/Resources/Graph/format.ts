import format from 'date-fns/format';

const timeFormat = 'HH:mm';
const dateTimeFormat = 'MM/dd HH:mm';
const dateFormat = 'MM/dd';

const formatTo = ({ time, to }): string => {
  const tickTimestamp = Number(time) * 1000;

  return format(new Date(tickTimestamp), to);
};

export { timeFormat, dateTimeFormat, dateFormat, formatTo };
