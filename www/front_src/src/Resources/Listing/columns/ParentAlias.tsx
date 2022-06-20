import { Typography } from '@mui/material';

import { ComponentColumnProps } from '@centreon/ui';

import { useColumnStyles } from '.';

const ParentAliasColumn = ({
  row,
}: ComponentColumnProps): JSX.Element | null => {
  const classes = useColumnStyles();

  if (!row.parent) {
    return null;
  }

  return (
    <div className={classes.resourceDetailsCell}>
      <div className={classes.resourceNameItem}>
        <Typography variant="body2">{row.parent.alias}</Typography>
      </div>
    </div>
  );
};

export default ParentAliasColumn;
