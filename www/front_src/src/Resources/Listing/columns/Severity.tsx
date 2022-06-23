import Tooltip from '@mui/material/Tooltip';
import { makeStyles } from '@mui/styles';

import { ComponentColumnProps } from '@centreon/ui';

import ShortTypeChip from '../../ShortTypeChip';
import { Severity } from '../../models';

const useStyles = makeStyles({
  root: {
    display: 'grid',
    gridTemplateColumns: '1fr',
    gridTemplateRows: 'repeat(2,minmax(15px,5px))',
    rowGap: 3,
  },
});

const Title = ({ severity }: { severity: Severity }): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.root}>
      <div style={{ background: 'orange', display: 'flex' }}>
        <div style={{ background: 'red' }}>severity_name:</div>
        <div> {severity.name}</div>
      </div>
      <div style={{ background: 'green', display: 'flex' }}>
        <div style={{ background: 'blue' }}>level</div>
        <div>{severity.level}</div>
      </div>
    </div>
  );
};

const SeverityColumn = ({ row }: ComponentColumnProps): JSX.Element | null => {
  console.log('rooooow', row);

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
