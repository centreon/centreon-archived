import * as React from 'react';

import { Typography } from '@material-ui/core';

import { ComponentColumnProps, StatusChip } from '@centreon/ui';

import { useColumnStyles } from '.';

const ParentResourceColumn = ({
  row,
}: ComponentColumnProps): JSX.Element | null => {
  const classes = useColumnStyles();

  if (!row.parent) {
    return null;
  }

  return (
    <div className={classes.resourceDetailsCell}>
      <StatusChip severityCode={row.parent?.status?.severity_code || 0} />
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.parent.name}</Typography>
      </div>
    </div>
  );
};

export default ParentResourceColumn;
