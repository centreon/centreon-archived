import * as React from 'react';

import { Typography } from '@mui/material';

import { ComponentColumnProps } from '@centreon/ui';

import ShortTypeChip from '../../ShortTypeChip';

import { useColumnStyles } from '.';

const ResourceColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  const classes = useColumnStyles();

  return (
    <div className={classes.resourceDetailsCell}>
      {row.icon ? (
        <img alt={row.icon.name} height={16} src={row.icon.url} width={16} />
      ) : (
        <ShortTypeChip label={row.short_type} />
      )}
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.name}</Typography>
      </div>
    </div>
  );
};

export default ResourceColumn;
