import * as React from 'react';

import { Grid } from '@material-ui/core';
import { ResourceDetails } from '..';
import ExpandableCard from './ExpandableCard';
import {
  labelStatusInformation,
  labelDowntimeDuration,
  labelFrom,
  labelTo,
} from '../../translatedLabels';
import StateCard from '../StateCard';
import getFormattedDate from '../../getFormattedDate';
import DowntimeChip from '../../Chip/Downtime';

interface Props {
  details: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
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
    </Grid>
  );
};

export default DetailsTab;
