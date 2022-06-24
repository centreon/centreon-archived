import Tooltip from '@mui/material/Tooltip';
import { makeStyles } from '@mui/styles';

import { ComponentColumnProps } from '@centreon/ui';

import ShortTypeChip from '../../ShortTypeChip';
import { Severity } from '../../models';

const useStyles = makeStyles((theme) => ({
  firstColumn: {
    display: 'flex',
    minWidth: theme.spacing(5),
  },
  root: {
    display: 'flex',
    flexDirection: 'column',
  },
  rowContainer: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'wrap',
  },
  text: {
    display: 'flex',
  },
}));

const Title = ({ severity }: { severity: Severity }): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.root}>
      <div className={classes.rowContainer}>
        <div className={classes.firstColumn}>name:</div>
        <div className={classes.text}>{severity.name}</div>
      </div>

      <div className={classes.rowContainer}>
        <div className={classes.firstColumn}>level:</div>
        <div className={classes.text}>{severity.level}</div>
      </div>
    </div>
  );
};

const SeverityColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  if (!row.severity_level) {
    return null;
  }

  return (
    <Tooltip title={<Title severity={row?.severity} />}>
      <div>
        <ShortTypeChip label={row.severity_level?.toString()} />
      </div>
    </Tooltip>
  );
};

export default SeverityColumn;
