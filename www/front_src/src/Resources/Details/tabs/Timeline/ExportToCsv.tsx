import { useAtomValue } from 'jotai';
import { useTranslation } from 'react-i18next';

import SaveIcon from '@mui/icons-material/SaveAlt';
import { Button, Stack } from '@mui/material';

import {
  getDatesDerivedAtom,
  selectedTimePeriodAtom,
} from '../../../Graph/Performance/TimePeriods/timePeriodAtoms';
import { labelExportToCSV } from '../../../translatedLabels';
import { detailsAtom } from '../../detailsAtoms';

const ExportToCsv = (): JSX.Element => {
  const { t } = useTranslation();

  const details = useAtomValue(detailsAtom);
  const getIntervalDates = useAtomValue(getDatesDerivedAtom);
  const selectedTimePeriod = useAtomValue(selectedTimePeriodAtom);

  const [start, end] = getIntervalDates(selectedTimePeriod);

  const timelineDownloadEndpoint = `${details?.links.endpoints.timeline}/download`;

  const search = {
    $and: [{ date: { $gt: start } }, { date: { $lt: end } }],
  };

  const exportToCSVEndpoint = `${timelineDownloadEndpoint}?search=${JSON.stringify(
    search,
  )}`;

  const exportToCsv = (): void => {
    window.open(exportToCSVEndpoint, 'noopener', 'noreferrer');
  };

  return (
    <Stack direction="row" justifyContent="flex-end">
      <Button
        data-testid={labelExportToCSV}
        size="small"
        startIcon={<SaveIcon />}
        variant="contained"
        onClick={exportToCsv}
      >
        {t(labelExportToCSV)}
      </Button>
    </Stack>
  );
};

export default ExportToCsv;
