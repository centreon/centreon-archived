import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';

import { Box } from '@mui/material';
import SaveAsImageIcon from '@mui/icons-material/SaveAlt';

import { IconButton } from '@centreon/ui';

import { labelExportToCSV } from '../../../translatedLabels';
import { detailsAtom } from '../../detailsAtoms';

const ExportToCsv = (): JSX.Element => {
  const { t } = useTranslation();

  const details = useAtomValue(detailsAtom);
  const downloadTimelineFile = `${details?.links.endpoints.timeline}/download`;

  const exportToCsv = (): void => {
    window.open(downloadTimelineFile, 'noopener', 'noreferrer');
  };

  return (
    <Box>
      <IconButton
        data-testid={labelExportToCSV}
        title={t(labelExportToCSV)}
        onClick={exportToCsv}
      >
        <SaveAsImageIcon style={{ fontSize: 18 }} />
      </IconButton>
    </Box>
  );
};

export default ExportToCsv;
