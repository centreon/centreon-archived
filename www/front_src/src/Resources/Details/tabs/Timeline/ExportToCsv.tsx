import { useTranslation } from 'react-i18next';

import SaveIcon from '@mui/icons-material/SaveAlt';
import { Button, Stack } from '@mui/material';

import { SearchParameter, getSearchQueryParameterValue } from '@centreon/ui';

import { labelExportToCSV } from '../../../translatedLabels';

interface Props {
  getSearch: () => SearchParameter | undefined;
  timelineEndpoint: string;
}

const ExportToCsv = ({ getSearch, timelineEndpoint }: Props): JSX.Element => {
  const { t } = useTranslation();

  const timelineDownloadEndpoint = `${timelineEndpoint}/download`;

  const exportToCsv = (): void => {
    const data = getSearch();

    const params = getSearchQueryParameterValue(data);
    const exportToCSVEndpoint = `${timelineDownloadEndpoint}?search=${JSON.stringify(
      params,
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
