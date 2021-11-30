import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { includes, isEmpty, isNil } from 'ramda';
import { withRouter, Link } from 'react-router-dom';
import { connect } from 'react-redux';
import classnames from 'classnames';
import clsx from 'clsx';

import PollerIcon from '@material-ui/icons/DeviceHub';
import {
  Button,
  ClickAwayListener,
  makeStyles,
  Typography,
} from '@material-ui/core';

import {
  getData,
  useRequest,
  IconHeader,
  SubmenuHeader,
  IconToggleSubmenu,
} from '@centreon/ui';

import styles from '../header.scss';
import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';
import MenuLoader from '../../components/MenuLoader';

import {
  labelAllPollers,
  labelConfigurePollers,
  labelDatabaseUpdatesNotActive,
  labelLatencyDetected,
  labelPoller,
  labelPollerNotRunning,
} from './translatedLabels';
import ExportConfiguration from './ExportConfiguration';
import PollerStatusIcon from './PollerStatusIcon';

export const pollerConfigurationNumberPage = '60901';

const pollerIssueKeyToMessage = {
  database: labelDatabaseUpdatesNotActive,
  latency: labelLatencyDetected,
  stability: labelPollerNotRunning,
};

export interface Issue {
  critical: number;
  total: number;
  warning: number;
}
interface Props {
  allowedPages: Array<string>;
  endpoint: string;
  loaderWidth: number;
  refreshInterval: number;
}

interface Issues {
  [key: string]: Issue;
}

interface PollerData {
  issues: Issues;
  totalPoller: number;
}

const useStyles = makeStyles((theme) => ({
  label: {
    color: theme.palette.common.white,
  },
  link: {
    textDecoration: 'none',
  },
  pollerDetailRow: {
    display: 'flex',
    gap: theme.spacing(1),
  },
  pollerDetailTitle: {
    flexGrow: 1,
  },
}));

const PollerMenu = ({
  endpoint,
  loaderWidth,
  refreshInterval,
  allowedPages,
}: Props): JSX.Element => {
  const classes = useStyles();

  const { t } = useTranslation();

  const [issues, setIssues] = React.useState<Issues | null>(null);
  const [totalPoller, setTotal] = React.useState<PollerData | number>(0);

  const [isExporting, setIsExportingConfiguration] = React.useState<boolean>();
  const [toggled, setToggled] = React.useState<boolean>(false);
  const interval = React.useRef<number>();

  const newExporting = (): void => {
    setIsExportingConfiguration(!isExporting);
  };

  const { sendRequest } = useRequest<PollerData>({
    request: getData,
  });

  const closeSubmenu = (): void => {
    setToggled(!toggled);
  };

  // const redirectsToPollersPage = (): void => {
  //   push(`/main.php?p=${pollerConfigurationTopologyPage}`);

  //   closeSubmenu();
  // };

  React.useEffect(() => {
    loadPollerData();

    interval.current = window.setInterval(() => {
      loadPollerData();
    }, refreshInterval * 1000);

    return (): void => {
      clearInterval(interval.current);
    };
  }, []);

  const loadPollerData = (): void => {
    sendRequest(`./api/${endpoint}`)
      .then((retrievedPollerData) => {
        setIssues(
          isEmpty(retrievedPollerData.issues)
            ? null
            : retrievedPollerData.issues,
        );
        setTotal(retrievedPollerData.totalPoller);
      })
      .catch((error) => {
        if (error.response && error.response.status === 401) {
          setIssues(null);
        }
      });
  };

  const toggleDetailedView = (): void => {
    setToggled(!toggled);
  };

  if (isNil(issues)) {
    return <MenuLoader width={loaderWidth} />;
  }

  const allowPollerConfiguration = allowedPages?.includes(
    pollerConfigurationNumberPage,
  );

  const pollerConfigurationTopologyPage = '/main.php?p=60901';

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
        <div className={`${styles.wrapper} wrap-right-services`}>
          <SubmenuHeader active={toggled}>
            <IconHeader
              Icon={PollerIcon}
              iconName={t(labelPoller)}
              onClick={toggleDetailedView}
            />
            <PollerStatusIcon issues={issues} />
            <IconToggleSubmenu
              cursor="pointer"
              iconType="arrow"
              rotate={toggled}
              onClick={toggleDetailedView}
            />
            <div
              className={classnames(styles['submenu-toggle'], {
                [styles['submenu-toggle-active'] as string]: toggled,
              })}
            >
              {!isNil(issues) ? (
                Object.entries(issues).map(([key, issue]) => {
                  return (
                    <div className={classes.pollerDetailRow} key={key}>
                      <Typography
                        className={clsx([
                          classes.label,
                          classes.pollerDetailTitle,
                        ])}
                        variant="body2"
                      >
                        {t(pollerIssueKeyToMessage[key])}
                      </Typography>
                      <Typography className={classes.label} variant="body2">
                        {issue.total ? issue.total : ''}
                      </Typography>
                    </div>
                  );
                })
              ) : (
                <Typography className={classes.label} variant="body2">
                  {t(labelAllPollers)}
                  {totalPoller}
                </Typography>
              )}
              {allowPollerConfiguration && (
                <Link
                  className={classnames(
                    classes.link,
                    styles['wrap-middle-icon'],
                  )}
                  to={pollerConfigurationTopologyPage}
                >
                  <Button
                    size="medium"
                    style={{ marginTop: '8px' }}
                    variant="contained"
                    onClick={closeSubmenu}
                  >
                    {t(labelConfigurePollers)}
                  </Button>
                </Link>
              )}
              <ExportConfiguration setIsExportingConfiguration={newExporting} />
            </div>
          </SubmenuHeader>
        </div>
      </>
    </ClickAwayListener>
  );
};

interface StateToProps {
  allowedPages: Array<string>;
}

const mapStateToProps = (state): StateToProps => ({
  allowedPages: allowedPagesSelector(state),
});

export default connect(mapStateToProps)(withRouter(PollerMenu)) as (
  props: Props,
) => JSX.Element;
