import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';

import { Stack } from '@mui/material';
import SaveAsImageIcon from '@mui/icons-material/SaveAlt';

import { labelExportToCSV } from '../../../translatedLabels';
import { detailsAtom } from '../../detailsAtoms';
import ResourceActionButton from '../../../Actions/Resource/ResourceActionButton';

const ExportToCsv = (): JSX.Element => {
  const { t } = useTranslation();

  const details = useAtomValue(detailsAtom);
  const downloadTimelineFile = `${details?.links.endpoints.timeline}/download`;

  const exportToCsv = (): void => {
    window.open(downloadTimelineFile, 'noopener', 'noreferrer');
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
