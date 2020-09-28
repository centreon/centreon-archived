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

  const formatSuffix = base1024 ? ' ib' : 'a';

  return numeral(value)
    .format(`0.[00]${formatSuffix}`)
    .replace(/\s|i|B/g, '');
};

export default formatMetricValue;
