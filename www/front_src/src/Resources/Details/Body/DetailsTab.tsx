import * as React from 'react';

import { Grid, Card, CardContent, Typography } from '@material-ui/core';
import { ResourceDetails } from '..';
import ExpandableCard from './ExpandableCard';
import {
  labelStatusInformation,
  labelDowntimeDuration,
  labelFrom,
  labelTo,
  labelAcknowledgedBy,
  labelAt,
  labelPerformanceData,
} from '../../translatedLabels';
import StateCard from './StateCard';
import { getFormattedDateTime } from '../../dateTime';
import DowntimeChip from '../../Chip/Downtime';
import AcknwoledgeChip from '../../Chip/Acknowledge';
import DetailsCard from './DetailsCard';
import getDetailCardLines from './DetailsCard/cards';

interface Props {
  details: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
  return (
    <Grid container direction="column" spacing={2}>
      <Grid item container direction="column" spacing={2}>
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
              ].map(
                ({ prefix, time }) => `${prefix} ${getFormattedDateTime(time)}`,
              )}
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
                } ${labelAt} ${getFormattedDateTime(
                  details.acknowledgement.entry_time,
                )}`,
              ]}
              chip={<AcknwoledgeChip />}
            />
          </Grid>
        )}
      </Grid>
      <Grid item container spacing={2} alignItems="stretch">
        {getDetailCardLines(details).map(
          ({ title, field, lines }) =>
            field && (
              <Grid item xs={6}>
                <DetailsCard title={title} lines={lines} />
              </Grid>
            ),
        )}
      </Grid>
      <Grid item>
        <ExpandableCard
          title={labelPerformanceData}
          content={details.performance_data}
        />
      </Grid>
      <Grid item>
        <Card>
          <CardContent>
            <Typography variant="body2">{details.check_command}</Typography>
          </CardContent>
        </Card>
      </Grid>
    </Grid>
  );
};

export default DetailsTab;
