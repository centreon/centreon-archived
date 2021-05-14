import * as React from 'react';

import { isNil, isEmpty } from 'ramda';
import { useTranslation } from 'react-i18next';
import { ParentSize } from '@visx/visx';

import {
  Grid,
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

import Card from './Card';
import ExpandableCard from './ExpandableCard';
import StateCard from './StateCard';
import DetailsCard from './DetailsCard';
import getDetailCardLines from './DetailsCard/cards';
import CommandWithArguments from './CommandLine';

const useStyles = makeStyles((theme) => ({
  details: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
  },
  loadingSkeleton: {
    display: 'grid',
    gridRowGap: theme.spacing(2),
    gridTemplateRows: '67px',
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
  const { toDateTime } = useLocaleDateTimeFormat();
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
            content={details.information}
            severityCode={details.status.severity_code}
            title={t(labelStatusInformation)}
          />
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
          {details.acknowledgement && (
            <StateCard
              chip={<AcknowledgeChip />}
              commentLine={details.acknowledgement.comment}
              contentLines={[
                `${details.acknowledgement.author_name} ${t(
                  labelAt,
                )} ${toDateTime(details.acknowledgement.entry_time)}`,
              ]}
              title={t(labelAcknowledgedBy)}
            />
          )}
          <Grid container spacing={1}>
            {getDetailCardLines({ details, t, toDateTime }).map(
              ({ title, field, xs = 6, line, active }) => {
                const variableXs = (width > 600 ? xs / 2 : xs) as 3 | 6 | 12;
                const displayCard = !isNil(field) && !isEmpty(field);

                return (
                  displayCard && (
                    <Grid item key={title} xs={variableXs}>
                      <DetailsCard
                        active={active}
                        line={line}
                        title={t(title)}
                      />
                    </Grid>
                  )
                );
              },
            )}
          </Grid>
          {details.performance_data && (
            <ExpandableCard
              content={details.performance_data}
              title={t(labelPerformanceData)}
            />
          )}
          {details.command_line && (
            <Card>
              <Typography
                gutterBottom
                color="textSecondary"
                component="div"
                variant="body1"
              >
                <Grid container alignItems="center" spacing={1}>
                  <Grid item>{t(labelCommand)}</Grid>
                  <Grid item>
                    <Tooltip title={labelCopy} onClick={copyCommandLine}>
                      <IconButton size="small">
                        <IconCopyFile color="primary" fontSize="small" />
                      </IconButton>
                    </Tooltip>
                  </Grid>
                </Grid>
              </Typography>
              <CommandWithArguments commandLine={details.command_line} />
            </Card>
          )}
        </div>
      )}
    </ParentSize>
  );
};

export default DetailsTab;
