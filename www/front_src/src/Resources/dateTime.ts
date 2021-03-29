import moment from 'moment';

const parseAndFormat = ({ isoDate, to }): string =>
  moment.parseZone(isoDate).format(to);

const getFormattedDateTime = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'L LT' });

const getFormattedDate = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'L' });

const getFormattedTime = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'LT' });

export {
  parseAndFormat,
  getFormattedDateTime,
  getFormattedDate,
  getFormattedTime,
};
