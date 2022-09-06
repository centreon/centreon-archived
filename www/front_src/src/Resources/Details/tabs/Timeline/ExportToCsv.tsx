import { useTranslation } from 'react-i18next';

import SaveIcon from '@mui/icons-material/SaveAlt';
import { Button, Stack } from '@mui/material';

import { SearchParameter } from '@centreon/ui';

import { labelExportToCSV } from '../../../translatedLabels';

interface Props {
  getSearch: () => SearchParameter | undefined;
  timelineEndpoint: string;
}

const ExportToCsv = ({ getSearch, timelineEndpoint }: Props): JSX.Element => {
  const { t } = useTranslation();

  const timelineDownloadEndpoint = `${timelineEndpoint}/download`;

  const data = getSearch();

  const paramsDate = data?.conditions?.map((item) => {
    const { field } = item;

    const dateValues = Object.entries(item?.values as Record<string, string>);

    const results = dateValues.map(([key, value]) => ({
      [field]: { [key]: value },
    }));

    return results;
  });

  const paramsType = data?.lists?.map((item) => {
    const { field } = item;

    const types = { $in: item?.values };

    return { [field]: types };
  });

  const exportToCsv = (): void => {
    const operator = '$and';

    const search = {
      [operator]: [{ [operator]: paramsType }, { [operator]: paramsDate }],
    };

    const exportToCSVEndpoint = `${timelineDownloadEndpoint}?search=${JSON.stringify(
      search,
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
