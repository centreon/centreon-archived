import * as React from 'react';

import { isNil } from 'ramda';

import { Grid, Card, CardContent, Typography, styled } from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';

import ExpandableCard from './ExpandableCard';
import {
  labelStatusInformation,
  labelDowntimeDuration,
  labelFrom,
  labelTo,
  labelAcknowledgedBy,
  labelAt,
  labelPerformanceData,
} from '../../../../translatedLabels';
import StateCard from './StateCard';
import { getFormattedDateTime } from '../../../../dateTime';
import DowntimeChip from '../../../../Chip/Downtime';
import AcknowledgeChip from '../../../../Chip/Acknowledge';
import DetailsCard from './DetailsCard';
import getDetailCardLines from './DetailsCard/cards';
import { ResourceDetails } from '../../../models';

const CardSkeleton = styled(Skeleton)(() => ({
  transform: 'none',
}));

const LoadingSkeleton = (): JSX.Element => (
  <Grid container spacing={2} direction="column">
    <Grid item>
      <CardSkeleton height={120} />
    </Grid>
    <Grid item>
      <CardSkeleton height={75} />
    </Grid>
    <Grid item>
      <CardSkeleton height={75} />
    </Grid>
  </Grid>
);

interface Props {
  details?: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
  if (details === undefined) {
    return <LoadingSkeleton />;
  }

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
              chip={<AcknowledgeChip />}
            />
          </Grid>
        )}
      </Grid>
      <Grid item container spacing={2} alignItems="stretch">
        {getDetailCardLines(details).map(
          ({ title, field, getLines }) =>
            !isNil(field) && (
              <Grid key={title} item xs={6}>
                <DetailsCard title={title} lines={getLines()} />
              </Grid>
            ),
        )}
      </Grid>
      {details.performance_data && (
        <Grid item>
          <ExpandableCard
            title={labelPerformanceData}
            content={details.performance_data}
          />
        </Grid>
      )}
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
