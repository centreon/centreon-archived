import * as React from 'react';

import { ResourceDetails } from '..';
import ExpandableCard from './ExpandableCard';
import {
  labelStatusInformation,
  labelDowntimeDuration,
} from '../../translatedLabels';
import StateCard from '../StateCard';
import getFormattedDate from '../../getFormattedDate';
import DowntimeChip from '../../Chip/Downtime';

interface Props {
  details: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
  return (
    <>
      <ExpandableCard
        title={labelStatusInformation}
        content={details.output}
        severityCode={details.status.severity_code}
      />
      {details.downtimes?.map(({ start_time, end_time }) => (
        <StateCard
          title={labelDowntimeDuration}
          contentLines={[start_time, end_time].map(getFormattedDate)}
          chip={<DowntimeChip />}
        />
      ))}
    </>
  );
};

export default DetailsTab;
