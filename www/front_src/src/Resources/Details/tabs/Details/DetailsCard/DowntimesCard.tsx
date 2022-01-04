import * as React from 'react';

import { useTranslation } from 'react-i18next';
import makeStyles from '@mui/styles/makeStyles';

import { useLocaleDateTimeFormat } from '@centreon/ui';

import {
  labelDowntimeDuration,
  labelFrom,
  labelTo,
} from '../../../../translatedLabels';
import DowntimeChip from '../../../../Chip/Downtime';
import StateCard from '../StateCard';
import { ResourceDetails } from '../../../models';

interface Props {
  details: ResourceDetails;
}

const useStyles = makeStyles((theme) => ({
  downtimes: {
    display: 'grid',
    rowGap: theme.spacing(1),
  },
}));

const DowntimesCard = ({ details }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { toDateTime } = useLocaleDateTimeFormat();

  return (
    <div className={classes.downtimes}>
      {details.downtimes?.map(({ start_time, end_time, comment }) => (
        <StateCard
          chip={<DowntimeChip />}
          commentLine={comment}
          contentLines={[
            ...[
              { prefix: t(labelFrom), time: start_time },
              { prefix: t(labelTo), time: end_time },
            ].map(({ prefix, time }) => `${prefix} ${toDateTime(time)}`),
          ]}
          key={`downtime-${start_time}-${end_time}`}
          title={t(labelDowntimeDuration)}
        />
      ))}
    </div>
  );
};

export default DowntimesCard;
