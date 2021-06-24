import moment from 'moment';

interface ParseAndFormatProps {
  isoDate: string;
  locale?: string;
  to: string;
}

const parseAndFormat = ({ isoDate, to, locale }: ParseAndFormatProps): string =>
  moment
    .parseZone(isoDate)
    .locale(locale || 'en')
    .format(to);

const getFormattedDateTime = (isoDate: string): string =>
  parseAndFormat({ isoDate, to: 'MM/DD/YYYY HH:mm' });

const getFormattedDate = (isoDate: string): string =>
  parseAndFormat({ isoDate, to: 'MM/DD/YYYY' });

const getFormattedTime = (isoDate: string): string =>
  parseAndFormat({ isoDate, to: 'HH:mm' });

export {
  parseAndFormat,
  getFormattedDateTime,
  getFormattedDate,
  getFormattedTime,
};
