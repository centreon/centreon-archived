import * as React from 'react';

import { makeStyles } from '@material-ui/core';

import { SeverityCode, StatusChip } from '@centreon/ui/src';

const useStyles = makeStyles((theme) => ({
  extraSmallChipContainer: {
    height: 19,
  },
  smallChipLabel: {
    padding: theme.spacing(0.5),
  },
}));

interface Props {
  label: string;
}

const ShortTypeChip = ({ label }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <StatusChip
      label={label}
      severityCode={SeverityCode.None}
      classes={{
        root: classes.extraSmallChipContainer,
        label: classes.smallChipLabel,
      }}
    />
  );
};

export default ShortTypeChip;
