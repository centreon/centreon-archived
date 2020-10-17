import moment from 'moment';

type IsoDate = string | undefined;

interface ParseAndFormatParams {
  isoDate: IsoDate;
  to: string;
}

const parseAndFormat = ({ isoDate, to }: ParseAndFormatParams): string =>
  moment.parseZone(isoDate).format(to);

const getFormattedDateTime = (isoDate: IsoDate): string =>
  parseAndFormat({ isoDate, to: 'MM/DD/YYYY HH:mm' });

const getFormattedDate = (isoDate: IsoDate): string =>
  parseAndFormat({ isoDate, to: 'MM/DD/YYYY' });

const getFormattedTime = (isoDate: IsoDate): string =>
  parseAndFormat({ isoDate, to: 'HH:mm' });

export {
  parseAndFormat,
  getFormattedDateTime,
  getFormattedDate,
  getFormattedTime,
};
