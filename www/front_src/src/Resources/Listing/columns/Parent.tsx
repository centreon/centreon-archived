import { Typography } from '@mui/material';

import { ComponentColumnProps, StatusChip } from '@centreon/ui';

import { useColumnStyles } from '.';

const ParentResourceColumn = ({
  row,
  isHovered,
}: ComponentColumnProps): JSX.Element | null => {
  const classes = useColumnStyles({ isHovered });

  if (!row.parent) {
    return null;
  }

  return (
    <div className={classes.resourceDetailsCell}>
      <StatusChip
        severityCode={row.parent?.status?.severity_code || 0}
        size="small"
      />
      <div className={classes.resourceNameItem}>
        <Typography className={classes.resourceNameText} variant="body2">
          {row.parent.name}
        </Typography>
      </div>
    </div>
  );
};

export default ParentResourceColumn;
