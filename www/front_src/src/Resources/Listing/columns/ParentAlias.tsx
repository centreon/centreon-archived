import { isNil } from 'ramda';

import { Typography } from '@mui/material';

import { ComponentColumnProps } from '@centreon/ui';

import { useColumnStyles } from '.';

const ParentAlias = ({ row }: ComponentColumnProps): JSX.Element | null => {
  const classes = useColumnStyles();

  if (isNil(row.alias)) {
    return null;
  }

  return (
    <div className={classes.resourceNameItem}>
      <Typography variant="body2">{row.parent.alias}</Typography>
    </div>
  );
};

export default ParentAlias;
