import { Typography } from '@mui/material';

import { ComponentColumnProps } from '@centreon/ui';

import { useColumnStyles } from '.';

const ParentAliasColumn = ({
  row,
  isHovered,
}: ComponentColumnProps): JSX.Element | null => {
  const classes = useColumnStyles({ isHovered });

  if (!row.parent) {
    return null;
  }

  return (
    <div className={classes.resourceDetailsCell}>
      <div className={classes.resourceNameItem}>
        <Typography className={classes.resourceNameText} variant="body2">
          {row.parent.alias}
        </Typography>
      </div>
    </div>
  );
};

export default ParentAliasColumn;
