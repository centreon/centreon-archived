import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';

import { Button, Stack } from '@mui/material';
import SaveAsImageIcon from '@mui/icons-material/SaveAlt';

import { labelExportToCSV } from '../../../translatedLabels';
import { detailsAtom } from '../../detailsAtoms';
import {
  getDatesDerivedAtom,
  selectedTimePeriodAtom,
} from '../../../Graph/Performance/TimePeriods/timePeriodAtoms';

const ExportToCsv = (): JSX.Element => {
  const { t } = useTranslation();

  const details = useAtomValue(detailsAtom);

  const getIntervalDates = useAtomValue(getDatesDerivedAtom);
  const selectedTimePeriod = useAtomValue(selectedTimePeriodAtom);

  const [start, end] = getIntervalDates(selectedTimePeriod);

  const timelineFileCsv = `${details?.links.endpoints.timeline}/download?start_date=${start}&end_date=${end}`;

  const exportToCsv = (): void => {
    window.open(timelineFileCsv, 'noopener', 'noreferrer');
  };

  return (
    <Stack direction="row" justifyContent="flex-end" spacing={1}>
      <Button
        data-testid={labelExportToCSV}
        size="small"
        startIcon={<SaveAsImageIcon />}
        variant="contained"
        onClick={exportToCsv}
      >
        {t(labelExportToCSV)}
      </Button>
    </Stack>
  );
};

export default ExportToCsv;
