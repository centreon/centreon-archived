import * as React from 'react';

import { ComponentColumnProps, SeverityCode, StatusChip } from '@centreon/ui';

import { useColumnStyles } from '.';

const SeverityColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  const classes = useColumnStyles();

  if (!row.severity_level) {
    return null;
  }

  return (
    <StatusChip
      label={row.severity_level?.toString()}
      severityCode={SeverityCode.None}
      classes={{
        root: classes.extraSmallChipContainer,
        label: classes.smallChipLabel,
      }}
    />
  );
};

export default SeverityColumn;
