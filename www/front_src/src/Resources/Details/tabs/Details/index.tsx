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
} from '../../../translatedLabels';
import StateCard from './StateCard';
import { getFormattedDateTime } from '../../../dateTime';
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';
import DetailsCard from './DetailsCard';
import getDetailCardLines from './DetailsCard/cards';
import { ResourceDetails } from '../../models';
import { copy } from './clipboard';

const useStyles = makeStyles((theme) => ({
  details: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
  },
  loadingSkeleton: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
    gridTemplateRows: '120px 75px 75px',
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
      copy(details.command_line as string);

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
        content={details.output}
        severityCode={details.status.severity_code}
        title={labelStatusInformation}
      />
      {details.downtimes?.map(({ start_time, end_time, comment }) => (
        <StateCard
          chip={<DowntimeChip />}
          commentLine={comment}
          contentLines={[
            ...[
              { prefix: labelFrom, time: start_time },
              { prefix: labelTo, time: end_time },
            ].map(
              ({ prefix, time }) => `${prefix} ${getFormattedDateTime(time)}`,
            ),
          ]}
          key={`downtime-${start_time}-${end_time}`}
          title={labelDowntimeDuration}
        />
      ))}
      {details.acknowledgement && (
        <StateCard
          chip={<AcknowledgeChip />}
          commentLine={details.acknowledgement.comment}
          contentLines={[
            `${
              details.acknowledgement.author_name
            } ${labelAt} ${getFormattedDateTime(
              details.acknowledgement.entry_time,
            )}`,
          ]}
          title={labelAcknowledgedBy}
        />
      )}
      <Grid container alignItems="stretch" spacing={2}>
        {getDetailCardLines(details).map(
          ({ title, field, getLines }) =>
            !isNil(field) && (
              <Grid item key={title} xs={6}>
                <DetailsCard lines={getLines()} title={title} />
              </Grid>
            ),
        )}
      </Grid>
      {details.performance_data && (
        <ExpandableCard
          content={details.performance_data}
          title={labelPerformanceData}
        />
      )}
      {details.command_line && (
        <Card>
          <CardContent>
            <Typography gutterBottom color="textSecondary" variant="subtitle2">
              <Grid container alignItems="center" spacing={1}>
                <Grid item>{labelCommand}</Grid>
                <Grid item>
                  <Tooltip title={labelCopy} onClick={copyCommandLine}>
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
