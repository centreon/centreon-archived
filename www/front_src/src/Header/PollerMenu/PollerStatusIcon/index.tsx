import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';
import { makeStyles } from 'tss-react/mui';
import { CSSObject } from '@emotion/react';

import StorageIcon from '@mui/icons-material/Storage';
import LatencyIcon from '@mui/icons-material/Speed';
import { Avatar } from '@mui/material';

import { getStatusColors, SeverityCode } from '@centreon/ui';

import {
  labelDatabaseNotActive,
  labelDatabaseUpdateAndActive,
  labelLatencyDetected,
  labelNoLatencyDetected,
} from '../translatedLabels';
import { Issues } from '../models';

interface PollerStatusIconProps {
  issues: Issues | null;
}

interface StyleProps {
  databaseSeverity: SeverityCode;
  latencySeverity: SeverityCode;
}

const getIssueSeverity = ({ issues, key }): SeverityCode => {
  if (!isNil(issues[key]?.warning)) {
    return SeverityCode.Medium;
  }
  if (!isNil(issues[key]?.critical)) {
    return SeverityCode.High;
  }

  return SeverityCode.Ok;
};

const useStyles = makeStyles<StyleProps>()(
  (theme, { databaseSeverity, latencySeverity }) => {
    const getSeverityColor = (severityCode): CSSObject => ({
      background: getStatusColors({
        severityCode,
        theme,
      }).backgroundColor,
      color: getStatusColors({
        severityCode,
        theme,
      }).color,
    });

    return {
      avatar: {
        fontSize: theme.typography.body1.fontSize,
        height: theme.spacing(2.5),
        width: theme.spacing(2.5),
      },
      container: {
        display: 'flex',
        gap: theme.spacing(0.5),
        [theme.breakpoints.down(768)]: {
          bottom: 0,
          right: theme.spacing(1),
        },
      },
      database: getSeverityColor(databaseSeverity),
      icon: {
        height: theme.spacing(1.75),
        width: theme.spacing(1.75),
      },
      latency: getSeverityColor(latencySeverity),
    };
  },
);

const PollerStatusIcon = ({ issues }: PollerStatusIconProps): JSX.Element => {
  const databaseSeverity = getIssueSeverity({ issues, key: 'database' });
  const latencySeverity = getIssueSeverity({ issues, key: 'latency' });

  const { classes, cx } = useStyles({ databaseSeverity, latencySeverity });

  const { t } = useTranslation();

  return (
    <div className={classes.container}>
      <Avatar
        className={cx(classes.database, classes.avatar)}
        title={
          databaseSeverity === SeverityCode.Ok
            ? t(labelDatabaseUpdateAndActive)
            : t(labelDatabaseNotActive)
        }
      >
        <StorageIcon className={classes.icon} />
      </Avatar>
      <Avatar
        className={cx(classes.latency, classes.avatar)}
        title={
          latencySeverity === SeverityCode.Ok
            ? t(labelNoLatencyDetected)
            : t(labelLatencyDetected)
        }
      >
        <LatencyIcon className={classes.icon} />
      </Avatar>
    </div>
  );
};

export default PollerStatusIcon;
