import { useTranslation } from 'react-i18next';

import { Box, Link, Stack, Tooltip } from '@mui/material';
import FileDownloadIcon from '@mui/icons-material/FileDownload';

import { labelExportToCsv } from '../../../translatedLabels';

const ExportToCsv = (): JSX.Element => {
  const { t } = useTranslation();

  return (
    <Box sx={{ width: '100%' }}>
      <Stack sx={{ alignItems: 'center', padding: 1 }}>
        <Tooltip title={t(labelExportToCsv)}>
          <Link
            data-testid={labelExportToCsv}
            href="http://localhost:4000/centreon/api/latest/monitoring/hosts/14/services/25/timeline/download"
          >
            <FileDownloadIcon style={{ fontSize: 30 }} />
          </Link>
        </Tooltip>
      </Stack>
    </Box>
  );
};

export default ExportToCsv;

//     path: /monitoring/hosts/{hostId}/services/{serviceId}/timeline/download
