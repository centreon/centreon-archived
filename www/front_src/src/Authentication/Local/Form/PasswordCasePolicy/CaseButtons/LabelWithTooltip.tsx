import { useTranslation } from 'react-i18next';

import { Tooltip } from '@mui/material';

interface Props {
  label: string;
  tooltipLabel: string;
}

const LabelWithTooltip = ({ tooltipLabel, label }: Props): JSX.Element => {
  const { t } = useTranslation();

  return (
    <Tooltip placement="top" title={t(tooltipLabel) as string}>
      <div>{label}</div>
    </Tooltip>
  );
};

export default LabelWithTooltip;
