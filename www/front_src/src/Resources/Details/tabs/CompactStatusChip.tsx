import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import { StatusChip, StatusChipProps } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  root: {
    height: 18,
    fontSize: theme.typography.body2.fontSize,
  },
}));

const CompactStatusChip = (props: StatusChipProps): JSX.Element => {
  const classes = useStyles();

  return <StatusChip classes={{ root: classes.root }} {...props} />;
};

export default CompactStatusChip;
export { useStyles as useCompactStatusChipStyles };
