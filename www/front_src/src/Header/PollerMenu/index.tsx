import * as React from 'react';

import classnames from 'classnames';
import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';
import clsx from 'clsx';

import PollerIcon from '@material-ui/icons/DeviceHub';
import StorageIcon from '@material-ui/icons/Storage';
import LatencyIcon from '@material-ui/icons/Speed';
import {
  Avatar,
  ClickAwayListener,
  Grid,
  makeStyles,
  Theme,
  useTheme,
} from '@material-ui/core';
import { CreateCSSProperties } from '@material-ui/styles';

import {
  getStatusColors,
  SeverityCode,
  getData,
  useRequest,
  IconToggleSubmenu,
  IconHeader,
} from '@centreon/ui';

import styles from '../header.scss';
import MenuLoader from '../../components/MenuLoader';

import {
  labelDatabaseNotActive,
  labelDatabaseUpdateAndActive,
  labelLatencyDetected,
  labelNoLatencyDetected,
  labelPoller,
} from './translatedLabels';

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
    icon: {
      cursor: 'pointer',
      fontSize: theme.typography.body1.fontSize,
      height: theme.spacing(3.5),
      margin: '6px',
      position: 'relative',
      width: theme.spacing(3.5),
    },
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
      <Avatar
        className={clsx(classes.database, classes.icon)}
        title={
          databaseSeverity === SeverityCode.Ok
            ? t(labelDatabaseUpdateAndActive)
            : t(labelDatabaseNotActive)
        }
      >
        <StorageIcon />
      </Avatar>
      <Avatar
        className={clsx(classes.latency, classes.icon)}
        title={
          latencySeverity === SeverityCode.Ok
            ? t(labelNoLatencyDetected)
            : t(labelLatencyDetected)
        }
      >
        <LatencyIcon />
      </Avatar>
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
  const theme = useTheme();
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
        <Grid
          container
          alignItems="center"
          direction="row"
          justifyContent="flex-start"
          style={{
            padding: theme.spacing('6px', '6px', '6px', '16px'),
            paddingLeft: theme.spacing(2),
          }}
        >
          <IconHeader
            Icon={PollerIcon}
            iconName={t(labelPoller)}
            onClick={toggleDetailedView}
          />

          <GetPollerStatusIcon issues={issues} />
          <IconToggleSubmenu
            iconType="arrow"
            rotate={toggled}
            onClick={toggleDetailedView}
          />
        </Grid>
      </>
    </ClickAwayListener>
  );
};

export default PollerMenu;
