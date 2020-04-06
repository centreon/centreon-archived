import format from 'date-fns/format';

const formatTimeAxis = (tick): string => {
  const tickTimestamp = Number(tick) * 1000;

  return format(new Date(tickTimestamp), 'HH:mm');
};

export { formatTimeAxis };
