import * as React from 'react';

import { Typography } from '@material-ui/core';

import {
  ComponentColumnProps,
  SeverityCode,
  StatusChip,
} from '@centreon/ui/src';

import { useColumnStyles } from '.';

const ResourceColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  const classes = useColumnStyles();

  return (
    <div className={classes.resourceDetailsCell}>
      {row.icon ? (
        <img src={row.icon.url} alt={row.icon.name} width={16} height={16} />
      ) : (
        <StatusChip
          label={row.short_type}
          severityCode={SeverityCode.None}
          classes={{
            root: classes.extraSmallChipContainer,
            label: classes.smallChipLabel,
          }}
        />
      )}
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.name}</Typography>
      </div>
    </div>
  );
};

export default ResourceColumn;
