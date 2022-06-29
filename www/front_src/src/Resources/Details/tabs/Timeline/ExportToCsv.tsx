import { useTranslation } from 'react-i18next';
import { useAtomValue } from 'jotai';
import { IconButton } from 'centreon-frontend/packages/centreon-ui/src';
import { path } from 'ramda';

import { Box, Stack } from '@mui/material';
import FileDownloadIcon from '@mui/icons-material/FileDownload';

import { labelExportToCsv } from '../../../translatedLabels';
import { detailsAtom } from '../../detailsAtoms';

const ExportToCsv = (): JSX.Element => {
  const { t } = useTranslation();

  const details = useAtomValue(detailsAtom);

  const openInNewTab = (url): void => {
    window.open(url, 'noopener', 'noreferrer');
  };

  const downloadTimelineFile = `${details?.links.endpoints.timeline}/download`;

  const exportToCsv = (): void => {
    openInNewTab(downloadTimelineFile);
  };

  return (
    <Box>
      <Stack sx={{ alignItems: 'center', padding: 1 }}>
        <IconButton
          data-testid={labelExportToCsv}
          title={t(labelExportToCsv)}
          onClick={exportToCsv}
        >
          <FileDownloadIcon style={{ fontSize: 30 }} />
        </IconButton>
      </Stack>
    </Box>
  );
};

export default ExportToCsv;

// Snackbar
// si href non ok => window open
//     path: /monitoring/hosts/{hostId}/services/{serviceId}/timeline/download
