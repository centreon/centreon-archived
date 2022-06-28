import { ReactNode } from 'react';

import { isNil } from 'ramda';

import Tooltip, { TooltipProps } from '@mui/material/Tooltip';
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

interface Props {
  children: ReactNode;
  title: TooltipProps['title'];
}

const WrapperTooltip = ({ title, children }: Props): JSX.Element => {
  return (
    <Tooltip title={title}>
      <div>{children}</div>
    </Tooltip>
  );
};

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
  const isSeverityIcon = !isNil(row?.severity?.icon.url);
  const isSeverityLevel = !isNil(row?.severity?.level);

  if (!row?.severity) {
    return null;
  }

  if (isSeverityIcon) {
    return (
      <WrapperTooltip title={<Title severity={row?.severity} />}>
        <img alt="" height={24} src={row.severity.icon.url} width={24} />
      </WrapperTooltip>
    );
  }

  return (
    <div>
      {isSeverityLevel && (
        <WrapperTooltip title={<Title severity={row?.severity} />}>
          <ShortTypeChip label={row.severity_level?.toString()} />
        </WrapperTooltip>
      )}
    </div>
  );
};

export default SeverityColumn;
