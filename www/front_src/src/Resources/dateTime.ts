import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';

const parseAndFormat = ({ isoDate, to }): string =>
  format(parseISO(isoDate), to);

const getFormattedDateTime = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'MM/dd/yyyy H:mm' });

const getFormattedDate = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'MM/dd/yyyy' });

const getFormattedTime = (isoDate): string =>
  parseAndFormat({ isoDate, to: 'H:mm' });

export { getFormattedDateTime, getFormattedDate, getFormattedTime };
