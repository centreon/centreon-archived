import * as React from 'react';

import makeStyles from '@mui/styles/makeStyles';

import { SeverityCode, StatusChip } from '@centreon/ui';

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
      classes={{
        label: classes.smallChipLabel,
        root: classes.extraSmallChipContainer,
      }}
      label={label}
      severityCode={SeverityCode.None}
    />
  );
};

export default ShortTypeChip;
