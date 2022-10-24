import { useTranslation } from 'react-i18next';

import SaveIcon from '@mui/icons-material/SaveAlt';
import { Button, Stack } from '@mui/material';

import { getSearchQueryParameterValue, SearchParameter } from '@centreon/ui';

import { labelExportToCSV } from '../../../translatedLabels';

interface Props {
  getSearch: () => SearchParameter | undefined;
  timelineDownloadEndpoint: string;
}

const ExportToCsv = ({
  getSearch,
  timelineDownloadEndpoint
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const exportToCsv = (): void => {
    const data = getSearch();

    const parameters = getSearchQueryParameterValue(data);
    const exportToCSVEndpoint = `${timelineDownloadEndpoint}?search=${JSON.stringify(
      parameters
    )}`;

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
