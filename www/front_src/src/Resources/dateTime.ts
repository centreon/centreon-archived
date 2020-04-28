import moment from 'moment';

const parseAndFormat = ({ isoDate, to }): string =>
  moment.parseZone(isoDate).format(to);

const getFormattedDateTime = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'MM/DD/YYYY HH:mm' });

const getFormattedDate = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'MM/DD/YYYY' });

const getFormattedTime = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'HH:mm' });

export { getFormattedDateTime, getFormattedDate, getFormattedTime };
