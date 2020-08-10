import * as React from 'react';

import { isNil } from 'ramda';

import {
  Grid,
  Card,
  CardContent,
  Typography,
  styled,
  Tooltip,
  IconButton,
  makeStyles,
} from '@material-ui/core';
import { Skeleton } from '@material-ui/lab';
import IconCopyFile from '@material-ui/icons/FileCopy';

import { useSnackbar, Severity } from '@centreon/ui';

import ExpandableCard from './ExpandableCard';
import {
  labelCopy,
  labelCommand,
  labelStatusInformation,
  labelDowntimeDuration,
  labelFrom,
  labelTo,
  labelAcknowledgedBy,
  labelAt,
  labelPerformanceData,
  labelCommandCopied,
  labelSomethingWentWrong,
} from '../../../../translatedLabels';
import StateCard from './StateCard';
import { getFormattedDateTime } from '../../../../dateTime';
import DowntimeChip from '../../../../Chip/Downtime';
import AcknowledgeChip from '../../../../Chip/Acknowledge';
import DetailsCard from './DetailsCard';
import getDetailCardLines from './DetailsCard/cards';
import { ResourceDetails } from '../../../models';
import { copy } from './clipboard';

const useStyles = makeStyles((theme) => ({
  loadingSkeleton: {
    display: 'grid',
    gridTemplateRows: '120px 75px 75px',
    gridRowGap: theme.spacing(2),
  },
  details: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
  },
}));

const CardSkeleton = styled(Skeleton)(() => ({
  transform: 'none',
}));

const LoadingSkeleton = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.loadingSkeleton}>
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
      <CardSkeleton height="100%" />
    </div>
  );
};

interface Props {
  details?: ResourceDetails;
}

const DetailsTab = ({ details }: Props): JSX.Element => {
  const classes = useStyles();

  const { showMessage } = useSnackbar();

  if (details === undefined) {
    return <LoadingSkeleton />;
  }

  const copyCommandLine = (): void => {
    try {
      copy(details.command_line);

      showMessage({
        message: labelCommandCopied,
        severity: Severity.success,
      });
    } catch (_) {
      showMessage({
        message: labelSomethingWentWrong,
        severity: Severity.error,
      });
    }
  };

  return (
    <div className={classes.details}>
      <ExpandableCard
        title={labelStatusInformation}
        content={details.output}
        severityCode={details.status.severity_code}
      />
      {details.downtimes?.map(({ start_time, end_time, comment }) => (
        <StateCard
          key={`downtime-${start_time}-${end_time}`}
          title={labelDowntimeDuration}
          contentLines={[
            ...[
              { prefix: labelFrom, time: start_time },
              { prefix: labelTo, time: end_time },
            ].map(
              ({ prefix, time }) => `${prefix} ${getFormattedDateTime(time)}`,
            ),
          ]}
          commentLine={comment}
          chip={<DowntimeChip />}
        />
      ))}
      {details.acknowledgement && (
        <StateCard
          title={labelAcknowledgedBy}
          contentLines={[
            `${
              details.acknowledgement.author_name
            } ${labelAt} ${getFormattedDateTime(
              details.acknowledgement.entry_time,
            )}`,
          ]}
          commentLine={details.acknowledgement.comment}
          chip={<AcknowledgeChip />}
        />
      )}
      <Grid container spacing={2} alignItems="stretch">
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
        <ExpandableCard
          title={labelPerformanceData}
          content={details.performance_data}
        />
      )}
      {details.command_line && (
        <Card>
          <CardContent>
            <Typography variant="subtitle2" color="textSecondary" gutterBottom>
              <Grid container alignItems="center" spacing={1}>
                <Grid item>{labelCommand}</Grid>
                <Grid item>
                  <Tooltip onClick={copyCommandLine} title={labelCopy}>
                    <IconButton size="small">
                      <IconCopyFile color="primary" fontSize="small" />
                    </IconButton>
                  </Tooltip>
                </Grid>
              </Grid>
            </Typography>
            <Typography variant="body2">{details.command_line}</Typography>
          </CardContent>
        </Card>
      )}
    </div>
  );
};

export default DetailsTab;
