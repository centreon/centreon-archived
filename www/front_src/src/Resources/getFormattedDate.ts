import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';

const getFormattedDate = (isoDate): string =>
  format(parseISO(isoDate), 'MM/dd/yyyy H:m');

export default getFormattedDate;
