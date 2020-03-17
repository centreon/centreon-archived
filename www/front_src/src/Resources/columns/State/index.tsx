import React from 'react';

import { Grid, Avatar, makeStyles, fade, Tooltip } from '@material-ui/core';
import { Person as IconAcknowledged } from '@material-ui/icons';
import { lime, purple } from '@material-ui/core/colors';

import IconDowntime from '../icons/Downtime';
import { ColumnProps } from '..';
import DowntimeDetailsTable from './DetailsTable/Downtime';
import AcknowledgementDetailsTable from './DetailsTable/Acknowledgement';
import { labelInDowntime, labelAcknowledged } from '../../translatedLabels';
import { Resource } from '../../models';

const useStyles = makeStyles((theme) => ({
  stateChip: {
    width: theme.spacing(4),
    height: theme.spacing(4),
  },
  acknowledged: {
    backgroundColor: fade(lime[900], 0.1),
    color: lime[900],
  },
  downtime: {
    backgroundColor: fade(purple[500], 0.1),
    color: purple[500],
  },
  tooltip: {
    maxWidth: 'none',
    backgroundColor: 'transparent',
  },
}));

interface StateChipProps {
  endpoint: string;
  className: string;
  Icon: React.SFC;
  DetailsTable: React.SFC<{ endpoint: string }>;
  ariaLabel: string;
}

const StateChip = ({
  endpoint,
  className,
  Icon,
  DetailsTable,
  ariaLabel,
}: StateChipProps): JSX.Element => {
  const classes = useStyles();

  return (
    <Tooltip
      placement="left"
      title={<DetailsTable endpoint={endpoint} />}
      classes={{ tooltip: classes.tooltip }}
      enterDelay={0}
    >
      <Avatar
        className={`${classes.stateChip} ${className}`}
        aria-label={ariaLabel}
      >
        <Icon />
      </Avatar>
    </Tooltip>
  );
};

const DowntimeChip = ({ resource }: { resource: Resource }): JSX.Element => {
  const classes = useStyles();

  return (
    <StateChip
      endpoint={resource.downtime_endpoint as string}
      className={classes.downtime}
      ariaLabel={`${resource.name} ${labelInDowntime}`}
      DetailsTable={DowntimeDetailsTable}
      Icon={IconDowntime}
    />
  );
};

const AcknowledgedChip = ({
  resource,
}: {
  resource: Resource;
}): JSX.Element => {
  const classes = useStyles();

  return (
    <StateChip
      endpoint={resource.acknowledgement_endpoint as string}
      className={classes.acknowledged}
      ariaLabel={`${resource.name} ${labelAcknowledged}`}
      DetailsTable={AcknowledgementDetailsTable}
      Icon={IconAcknowledged}
    />
  );
};

const StateColumn = ({ row }: ColumnProps): JSX.Element => {
  return (
    <Grid container spacing={1}>
      {row.in_downtime && (
        <Grid item>
          <DowntimeChip resource={row} />
        </Grid>
      )}
      {row.acknowledged && (
        <Grid item>
          <AcknowledgedChip resource={row} />
        </Grid>
      )}
    </Grid>
  );
};

export default StateColumn;
