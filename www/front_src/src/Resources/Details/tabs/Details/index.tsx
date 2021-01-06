import * as React from 'react';

import { isNil, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';
import { ParentSize } from '@visx/visx';

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

import { useSnackbar, Severity, useLocaleDateTimeFormat } from '@centreon/ui';
import copyToClipBoard from '@centreon/ui/src/utils/copy';

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
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';
import { ResourceDetails } from '../../models';

import ExpandableCard from './ExpandableCard';
import StateCard from './StateCard';
import DetailsCard from './DetailsCard';
import getDetailCardLines from './DetailsCard/cards';

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
  const { t } = useTranslation();
  const { toDateTime, toDate, toTime } = useLocaleDateTimeFormat();
  const classes = useStyles();

  const { showMessage } = useSnackbar();

  if (isNil(details)) {
    return <LoadingSkeleton />;
  }

  const copyCommandLine = (): void => {
    try {
      copyToClipBoard(details.command_line as string);

      showMessage({
        message: t(labelCommandCopied),
        severity: Severity.success,
      });
    } catch (_) {
      showMessage({
        message: t(labelSomethingWentWrong),
        severity: Severity.error,
      });
    }
  };

  return (
    <ParentSize>
      {({ width }): JSX.Element => (
        <div className={classes.details}>
          <ExpandableCard
            title={t(labelStatusInformation)}
            content={details.information}
            severityCode={details.status.severity_code}
          />
          {details.downtimes?.map(({ start_time, end_time, comment }) => (
            <StateCard
              key={`downtime-${start_time}-${end_time}`}
              title={t(labelDowntimeDuration)}
              contentLines={[
                ...[
                  { prefix: labelFrom, time: start_time },
                  { prefix: labelTo, time: end_time },
                ].map(({ prefix, time }) => `${prefix} ${toDateTime(time)}`),
              ]}
              commentLine={comment}
              chip={<DowntimeChip />}
            />
          ))}
          {details.acknowledgement && (
            <StateCard
              title={t(labelAcknowledgedBy)}
              contentLines={[
                `${details.acknowledgement.author_name} ${t(
                  labelAt,
                )} ${toDateTime(details.acknowledgement.entry_time)}`,
              ]}
              commentLine={details.acknowledgement.comment}
              chip={<AcknowledgeChip />}
            />
          )}
          <Grid container spacing={2} alignItems="stretch">
            {getDetailCardLines({ details, toDate, toTime }).map(
              ({ title, field, xs = 6, getLines }) => {
                const variableXs = (width > 600 ? xs / 2 : xs) as 3 | 6 | 12;
                const displayCard = !isNil(field) && !isEmpty(field);

                return (
                  displayCard && (
                    <Grid key={title} item xs={variableXs}>
                      <DetailsCard title={t(title)} lines={getLines()} />
                    </Grid>
                  )
                );
              },
            )}
          </Grid>
          {details.performance_data && (
            <ExpandableCard
              title={t(labelPerformanceData)}
              content={details.performance_data}
            />
          )}
          {details.command_line && (
            <Card>
              <CardContent>
                <Typography
                  variant="subtitle2"
                  color="textSecondary"
                  gutterBottom
                >
                  <Grid container alignItems="center" spacing={1}>
                    <Grid item>{t(labelCommand)}</Grid>
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
      )}
    </ParentSize>
  );
};

export default DetailsTab;
