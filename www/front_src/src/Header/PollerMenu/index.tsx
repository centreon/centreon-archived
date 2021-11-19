import * as React from 'react';

import classnames from 'classnames';
import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

import PollerIcon from '@material-ui/icons/DeviceHub';
import StorageIcon from '@material-ui/icons/Storage';
import LatencyIcon from '@material-ui/icons/Speed';
import {
  Avatar,
  ClickAwayListener,
  makeStyles,
  Theme,
  Typography,
} from '@material-ui/core';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import ExpandLessIcon from '@material-ui/icons/ExpandLess';
import { CreateCSSProperties } from '@material-ui/styles';

import {
  getStatusColors,
  SeverityCode,
  getData,
  useRequest,
} from '@centreon/ui';

import styles from '../header.scss';
import MenuLoader from '../../components/MenuLoader';

import { labelPoller } from './translatedLabels';

export const useStyles = makeStyles(() => ({
  link: {
    textDecoration: 'none',
  },
}));

const getIssueSeverity = ({ issues, key }): SeverityCode => {
  if (!isNil(issues[key]?.warning)) {
    return SeverityCode.Medium;
  }
  if (!isNil(issues[key]?.critical)) {
    return SeverityCode.High;
  }

  return SeverityCode.Ok;
};

interface GetPollerStatusIconProps {
  issues: Issues | null;
}

interface StyleProps {
  databaseSeverity: SeverityCode;
  latencySeverity: SeverityCode;
}

const useStatusStyles = makeStyles<Theme, StyleProps>((theme) => {
  const getSeverityColor = (severityCode): CreateCSSProperties<StyleProps> => ({
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
    database: ({ databaseSeverity }): CreateCSSProperties<StyleProps> =>
      getSeverityColor(databaseSeverity),
    latency: ({ latencySeverity }): CreateCSSProperties<StyleProps> =>
      getSeverityColor(latencySeverity),
  };
});

const GetPollerStatusIcon = ({
  issues,
}: GetPollerStatusIconProps): JSX.Element => {
  const databaseSeverity = getIssueSeverity({ issues, key: 'database' });

  const latencySeverity = getIssueSeverity({ issues, key: 'latency' });
  const classes = useStatusStyles({ databaseSeverity, latencySeverity });

  const { t } = useTranslation();

  return (
    <>
      <span className={classnames(styles['wrap-left-icon'], styles.round)}>
        <span
          style={{
            display: 'flex',
            justifyContent: 'center',
          }}
          title={
            databaseSeverity === SeverityCode.Ok
              ? t('OK: all database poller updates are active')
              : t(
                  'Some database poller updates are not active; check your configuration',
                )
          }
        >
          <Avatar className={classes.database}>
            <StorageIcon />
          </Avatar>
        </span>
      </span>
      <span className={classnames(styles['wrap-left-icon'], styles.round)}>
        <span
          style={{
            display: 'flex',
            justifyContent: 'center',
          }}
          title={
            latencySeverity === SeverityCode.Ok
              ? t('OK: no latency detected on your platform')
              : t(
                  'Latency detected, check configuration for better optimization',
                )
          }
        >
          <Avatar className={classes.latency}>
            <LatencyIcon />
          </Avatar>
        </span>
      </span>
    </>
  );
};
interface Props {
  endpoint: string;
  loaderWidth: number;
  refreshInterval: number;
}

interface Issue {
  critical: number;
  warning: number;
}

interface Issues {
  [key: string]: Issue;
}

const PollerMenu = ({
  endpoint,
  loaderWidth,
  refreshInterval,
}: Props): JSX.Element => {
  const { t } = useTranslation();

  const [issues, setIssues] = React.useState<Issues | null>(null);
  const [toggled, setToggled] = React.useState<boolean>();
  const interval = React.useRef<number>();

  const { sendRequest } = useRequest<Issues>({
    request: getData,
  });

  const loadIssues = (): void => {
    sendRequest(`./api/${endpoint}`)
      .then((retrievedIssues) => {
        setIssues(retrievedIssues);
      })
      .catch((error) => {
        if (error.response && error.response.status === 401) {
          setIssues(null);
        }
      });
  };

  React.useEffect(() => {
    loadIssues();

    interval.current = window.setInterval(() => {
      loadIssues();
    }, refreshInterval * 1000);

    return (): void => {
      clearInterval(interval.current);
    };
  }, []);

  const toggleDetailedView = (): void => {
    setToggled(!toggled);
  };

  if (isNil(issues)) {
    return <MenuLoader width={loaderWidth} />;
  }

  return (
    <ClickAwayListener
      onClickAway={(): void => {
        if (!toggled) {
          return;
        }
        toggleDetailedView();
      }}
    >
      <>
        <PollerIcon style={{ color: '#FFFFFF' }} />
        <span className={styles['wrap-left-icon__name']}>
          <Typography variant="caption">{t(labelPoller)}</Typography>
        </span>
        <GetPollerStatusIcon issues={issues} />
      </>
    </ClickAwayListener>
  );
};

export default PollerMenu;
