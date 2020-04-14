import * as React from 'react';

import { Grid } from '@material-ui/core';

import { useCancelTokenSource } from '@centreon/ui';

import { ColumnProps } from '..';
import { DetailsTableProps } from './DetailsTable';
import DowntimeDetailsTable from './DetailsTable/Downtime';
import AcknowledgementDetailsTable from './DetailsTable/Acknowledgement';
import { labelInDowntime, labelAcknowledged } from '../../translatedLabels';
import { Listing, Resource } from '../../models';
import HoverChip from '../HoverChip';
import DowntimeChip from '../../Chip/Downtime';
import AcknowledgeChip from '../../Chip/Acknowledge';
import { getData } from '../../api';

type CustomDetailsTableProps<TDetails> = Pick<
  DetailsTableProps<TDetails>,
  'loading' | 'data'
>;

interface StateChipProps<TDetails> {
  endpoint: string;
  Chip: () => JSX.Element;
  DetailsTable: React.SFC<CustomDetailsTableProps<TDetails>>;
  label: string;
}

const StateHoverChip = <TDetails extends unknown>({
  endpoint,
  Chip,
  DetailsTable,
  label,
}: StateChipProps<TDetails>): JSX.Element => {
  const [loading, setLoading] = React.useState(false);
  const [data, setData] = React.useState<Listing<TDetails> | null>();
  const { cancel, token } = useCancelTokenSource();

  const fetchData = (): void => {
    setLoading(true);
    getData<Listing<TDetails>>({
      endpoint,
      requestParams: { cancelToken: token },
    })
      .then((retrievedData) => setData(retrievedData))
      .catch(() => setData(null))
      .finally(() => setLoading(false));
  };

  React.useEffect(() => {
    return (): void => {
      cancel();
    };
  }, []);

  return (
    <HoverChip Chip={Chip} label={label} onHover={fetchData}>
      <DetailsTable loading={loading} data={data} />
    </HoverChip>
  );
};

const DowntimeHoverChip = ({
  resource,
}: {
  resource: Resource;
}): JSX.Element => {
  return (
    <StateHoverChip
      endpoint={resource.downtime_endpoint as string}
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
  return (
    <StateHoverChip
      endpoint={resource.acknowledgement_endpoint as string}
      label={`${resource.name} ${labelAcknowledged}`}
      DetailsTable={AcknowledgementDetailsTable}
      Chip={AcknowledgeChip}
    />
  );
};

const StateColumn = ({ row }: ColumnProps): JSX.Element => {
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
