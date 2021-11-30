import * as React from 'react';

import { useTranslation } from 'react-i18next';

import { Tooltip } from '@material-ui/core';

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
