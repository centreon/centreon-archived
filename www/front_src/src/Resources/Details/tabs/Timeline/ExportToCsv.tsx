import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';

import { Stack } from '@mui/material';
import SaveAsImageIcon from '@mui/icons-material/SaveAlt';

import { labelExportToCSV } from '../../../translatedLabels';
import { detailsAtom } from '../../detailsAtoms';
import ResourceActionButton from '../../../Actions/Resource/ResourceActionButton';
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

  const timelineFileCsv = `${details?.links.endpoints.timeline}/download?search=start_date=${start}&end_date=${end}`;

  const exportToCsv = (): void => {
    window.open(timelineFileCsv, 'noopener', 'noreferrer');
  };

  return (
    <Stack
      alignItems="center"
      direction="row"
      justifyContent="flex-end"
      spacing={0.5}
    >
      <ResourceActionButton
        data-testid={labelExportToCSV}
        disabled={false}
        icon={<SaveAsImageIcon />}
        label={t(labelExportToCSV)}
        onClick={exportToCsv}
      />
    </Stack>
  );
};

export default ExportToCsv;
