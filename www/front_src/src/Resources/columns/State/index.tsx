import React from 'react';

import { Grid, makeStyles, fade } from '@material-ui/core';
import IconAcknowledged from '@material-ui/icons/Person';

import IconDowntime from '../icons/Downtime';
import { ColumnProps } from '..';
import DowntimeDetailsTable from './DetailsTable/Downtime';
import AcknowledgementDetailsTable from './DetailsTable/Acknowledgement';
import { labelInDowntime, labelAcknowledged } from '../../translatedLabels';
import { Resource } from '../../models';
import HoverChip from '../HoverChip';

const useStyles = makeStyles((theme) => ({
  acknowledged: {
    backgroundColor: fade(theme.palette.action.acknowledged, 0.1),
    color: theme.palette.action.acknowledged,
  },
  downtime: {
    backgroundColor: fade(theme.palette.action.inDowntime, 0.1),
    color: theme.palette.action.inDowntime,
  },
  tooltip: {
    maxWidth: 'none',
    backgroundColor: 'transparent',
  },
}));

interface StateChipProps {
  endpoint: string;
  className: string;
  Icon: React.ReactType;
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
  return (
    <HoverChip className={className} ariaLabel={ariaLabel} Icon={Icon}>
      <DetailsTable endpoint={endpoint} />
    </HoverChip>
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
