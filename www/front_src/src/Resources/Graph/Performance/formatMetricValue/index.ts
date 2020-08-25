import numeral from 'numeral';

const formatMetricValue = ({ value, unit, base = 1000 }): string => {
  const base2Units = [
    'B',
    'bytes',
    'bytespersecond',
    'B/s',
    'B/sec',
    'o',
    'octets',
  ];

  const base1024 = base2Units.includes(unit) || base === 1024;

  const format = base1024 ? '0b' : '0.[00]a';

  return numeral(value).format(format).replace('B', '');
};

export default formatMetricValue;
