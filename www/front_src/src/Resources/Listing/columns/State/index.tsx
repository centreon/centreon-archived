import { FC } from 'react';

import { path } from 'ramda';

import { Grid } from '@mui/material';

import { ComponentColumnProps } from '@centreon/ui';

import { labelInDowntime, labelAcknowledged } from '../../../translatedLabels';
import { Resource } from '../../../models';
import HoverChip from '../HoverChip';
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';

import AcknowledgementDetailsTable from './DetailsTable/Acknowledgement';
import DowntimeDetailsTable from './DetailsTable/Downtime';

interface StateChipProps {
  Chip: () => JSX.Element;
  DetailsTable: FC<{ endpoint: string }>;
  endpoint: string;
  label: string;
}

const StateHoverChip = ({
  endpoint,
  Chip,
  DetailsTable,
  label
}: StateChipProps): JSX.Element => {
  return (
    <HoverChip Chip={Chip} label={label}>
      {(): JSX.Element => <DetailsTable endpoint={endpoint} />}
    </HoverChip>
  );
};

const DowntimeHoverChip = ({
  resource
}: {
  resource: Resource;
}): JSX.Element => {
  const downtimeEndpoint = path(['links', 'endpoints', 'downtime'], resource);

  return (
    <StateHoverChip
      Chip={DowntimeChip}
      DetailsTable={DowntimeDetailsTable}
      endpoint={downtimeEndpoint as string}
      label={`${resource.name} ${labelInDowntime}`}
    />
  );
};

const AcknowledgeHoverChip = ({
  resource
}: {
  resource: Resource;
}): JSX.Element => {
  const acknowledgementEndpoint = path(
    ['links', 'endpoints', 'acknowledgement'],
    resource
  );

  return (
    <StateHoverChip
      Chip={AcknowledgeChip}
      DetailsTable={AcknowledgementDetailsTable}
      endpoint={acknowledgementEndpoint as string}
      label={`${resource.name} ${labelAcknowledged}`}
    />
  );
};

const StateColumn = ({ row }: ComponentColumnProps): JSX.Element => {
  return (
    <Grid container justifyContent="center" spacing={1}>
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
