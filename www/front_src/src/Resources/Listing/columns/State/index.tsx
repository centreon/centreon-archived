import React from 'react';

import { path } from 'ramda';

import { Grid } from '@material-ui/core';

import { ComponentColumnProps } from '@centreon/ui';

import { labelInDowntime, labelAcknowledged } from '../../../translatedLabels';
import { Resource } from '../../../models';
import HoverChip from '../HoverChip';
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';

import AcknowledgementDetailsTable from './DetailsTable/Acknowledgement';
import DowntimeDetailsTable from './DetailsTable/Downtime';

interface StateChipProps {
  endpoint: string;
  Chip: () => JSX.Element;
  DetailsTable: React.SFC<{ endpoint: string }>;
  label: string;
}

const StateHoverChip = ({
  endpoint,
  Chip,
  DetailsTable,
  label,
}: StateChipProps): JSX.Element => {
  return (
    <HoverChip Chip={Chip} label={label}>
      <DetailsTable endpoint={endpoint} />
    </HoverChip>
  );
};

const DowntimeHoverChip = ({
  resource,
}: {
  resource: Resource;
}): JSX.Element => {
  const downtimeEndpoint = path(['links', 'endpoints', 'downtime'], resource);

  return (
    <StateHoverChip
      endpoint={downtimeEndpoint as string}
      label={`${resource.name} ${labelInDowntime}`}
      DetailsTable={DowntimeDetailsTable}
      Chip={DowntimeChip}
    />
  );
};

const AcknowledgeHoverChip = ({
  resource,
}: {
  resource: Resource;
}): JSX.Element => {
  const acknowledgementEndpoint = path(
    ['links', 'endpoints', 'acknowledgement'],
    resource,
  );

  return (
    <StateHoverChip
      endpoint={acknowledgementEndpoint as string}
      label={`${resource.name} ${labelAcknowledged}`}
      DetailsTable={AcknowledgementDetailsTable}
      Chip={AcknowledgeChip}
    />
  );
};

const StateColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  return (
    <Grid container spacing={1}>
      {row.in_downtime && (
        <Grid item>
          <DowntimeHoverChip resource={row} />
        </Grid>
      )}
      {row.acknowledged && (
        <Grid item>
          <AcknowledgeHoverChip resource={row} />
        </Grid>
      )}
    </Grid>
  );
};

export default StateColumn;
