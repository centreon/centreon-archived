import * as React from 'react';

import classnames from 'classnames';
import { useTranslation } from 'react-i18next';
import { isNil, not } from 'ramda';

import PollerIcon from '@material-ui/icons/DeviceHub';
import StorageIcon from '@material-ui/icons/Storage';
import LatencyIcon from '@material-ui/icons/Speed';
import { ClickAwayListener, makeStyles } from '@material-ui/core';

import { getData, useRequest } from '@centreon/ui';

import styles from '../header.scss';
import MenuLoader from '../../components/MenuLoader';

export const useStyles = makeStyles(() => ({
  link: {
    textDecoration: 'none',
  },
}));

const getIssueClass = ({ issues, key }): string => {
  if (not(issues[key].warning)) {
    return 'orange';
  }
  if (not(issues[key].critical)) {
    return 'red';
  }

  return 'green';
};

interface GetPollerStatusIconProps {
  issues: Issues | null;
}

const GetPollerStatusIcon = ({
  issues,
}: GetPollerStatusIconProps): JSX.Element => {
  const databaseClass = getIssueClass({ issues, key: 'database' });

  const latencyClass = getIssueClass({ issues, key: 'latency' });
  const { t } = useTranslation();

  return (
    <>
      <span
        className={classnames(
          styles['wrap-left-icon'],
          styles.round,
          styles[databaseClass],
        )}
      >
        <span
          style={{
            display: 'flex',
            justifyContent: 'center',
          }}
          title={
            databaseClass === 'green'
              ? t('OK: all database poller updates are active')
              : t(
                  'Some database poller updates are not active; check your configuration',
                )
          }
        >
          <StorageIcon />
        </span>
      </span>
      <span
        className={classnames(
          styles['wrap-left-icon'],
          styles.round,
          styles[latencyClass],
        )}
      >
        <span
          style={{
            display: 'flex',
            justifyContent: 'center',
          }}
          title={
            latencyClass === 'green'
              ? t('OK: no latency detected on your platform')
              : t(
                  'Latency detected, check configuration for better optimization',
                )
          }
        >
          <LatencyIcon />
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
      <PollerIcon />
      <GetPollerStatusIcon issues={issues} />
    </ClickAwayListener>
  );
};

export default PollerMenu;
