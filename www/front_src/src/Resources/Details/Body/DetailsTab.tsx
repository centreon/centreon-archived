import * as React from 'react';

import { Grid, Typography } from '@material-ui/core';
import { ResourceDetails } from '..';
import ExpandableCard from './ExpandableCard';
import {
  labelStatusInformation,
  labelDowntimeDuration,
  labelFrom,
  labelTo,
  labelAcknowledgedBy,
  labelAt,
  labelCurrentStateDuration,
} from '../../translatedLabels';
import StateCard from '../StateCard';
import getFormattedDate from '../../getFormattedDate';
import DowntimeChip from '../../Chip/Downtime';
import AcknwoledgeChip from '../../Chip/Acknowledge';
import DetailsCard from '../DetailsCard';

const DetailsLine = ({ line }: { line: string }): JSX.Element => (
  <Typography variant="h5">{line}</Typography>
);

interface Props {
  details: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
  const detailCards = [
    {
      title: labelCurrentStateDuration,
      field: details.duration,
      lines: [
        { key: 'duration', line: <DetailsLine line={details.duration} /> },
        {
          key: 'tries',
          line: <DetailsLine key="tries" line={details.tries} />,
        },
      ],
    },
  ];

  return (
    <Grid container direction="column" spacing={2}>
      <Grid item>
        <ExpandableCard
          title={labelStatusInformation}
          content={details.output}
          severityCode={details.status.severity_code}
        />
      </Grid>
      {details.downtimes?.map(({ start_time, end_time }) => (
        <Grid item>
          <StateCard
            title={labelDowntimeDuration}
            contentLines={[
              { prefix: labelFrom, time: start_time },
              { prefix: labelTo, time: end_time },
            ].map(({ prefix, time }) => `${prefix} ${getFormattedDate(time)}`)}
            chip={<DowntimeChip />}
          />
        </Grid>
      ))}
      {details.acknowledgement && (
        <Grid item>
          <StateCard
            title={labelAcknowledgedBy}
            contentLines={[
              `${
                details.acknowledgement.author_name
              } ${labelAt} ${getFormattedDate(
                details.acknowledgement.entry_time,
              )}`,
            ]}
            chip={<AcknwoledgeChip />}
          />
        </Grid>
      )}
      {detailCards.map(
        ({ title, field, lines }) =>
          field && (
            <Grid item xs={6}>
              <DetailsCard title={title} lines={lines} />
            </Grid>
          ),
      )}
    </Grid>
  );
};

export default DetailsTab;
