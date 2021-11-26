import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';
import classnames from 'classnames';
import { withRouter } from 'react-router-dom';
import { connect } from 'react-redux';
import { push } from 'connected-react-router';

import PollerIcon from '@material-ui/icons/DeviceHub';
import {
  Button,
  ClickAwayListener,
  Grid,
  makeStyles,
  Typography,
  useTheme,
} from '@material-ui/core';

import {
  getData,
  useRequest,
  IconToggleSubmenu,
  IconHeader,
  SeverityCode,
} from '@centreon/ui';

import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';
import styles from '../header.scss';
import MenuLoader from '../../components/MenuLoader';

import { labelPoller } from './translatedLabels';
import ExportConfiguration from './ExportConfiguration';
import PollerStatusIcon from './PollerStatusIcon';

export const useStyles = makeStyles(() => ({
  link: {
    textDecoration: 'none',
  },
}));

export const pollerConfigurationTopologyPage = '60901';

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

const PollerMenu = ({
  endpoint,
  loaderWidth,
  refreshInterval,
  allowedPages,
}: Props): JSX.Element => {
  const theme = useTheme();
  const { t } = useTranslation();
  const [issues, setIssues] = React.useState<Issues | null>(null);
  const [isExporting, setIsExportingConfiguration] = React.useState<boolean>();
  const [toggled, setToggled] = React.useState<boolean>();
  const interval = React.useRef<number>();

  const newExporting = (): void => {
    setIsExportingConfiguration(!isExporting);
  };

  const { sendRequest } = useRequest<Issues>({
    request: getData,
  });

  const allowPollerConfiguration = allowedPages.includes(
    pollerConfigurationTopologyPage,
  );

  const closeSubmenu = (): void => {
    setToggled(!toggled);
  };

  const redirectsToPollersPage = (): void => {
    closeSubmenu();

    push(`/main.php?p=${pollerConfigurationTopologyPage}`);
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

          <PollerStatusIcon issues={issues} />

          <IconToggleSubmenu
            iconType="arrow"
            issues={issues}
            rotate={toggled}
            onClick={toggleDetailedView}
          />
        </Grid>
        <Grid
          container
          alignItems="center"
          direction="row"
          justifyContent="flex-start"
          style={{
            color: theme.palette.background.paper,
            display: 'block',
            fontSize: 'small',
            paddingLeft: theme.spacing(2),
            textTransform: 'uppercase',
          }}
        >
          {issues
            ? Object.entries(issues).map(([key, issue]) => {
                let message = '';

                if (key === 'database') {
                  message = t('Database updates not active');
                } else if (key === 'stability') {
                  message = t('Pollers not running');
                } else if (key === 'latency') {
                  message = t('Latency detected');
                }

                return (
                  <li className={styles['submenu-top-item']} key={key}>
                    <span className={styles['submenu-item-link']}>
                      <Grid>
                        <Grid>
                          {message}
                          {issue.total ? issue.total : '...'}
                        </Grid>
                      </Grid>
                    </span>
                    {Object.entries(issue).map(([elem, values]) => {
                      if (values.poller) {
                        const pollers = values.poller;

                        return pollers.map((poller) => {
                          let color = SeverityCode.High;
                          if (elem === 'warning') {
                            color = SeverityCode.Medium;
                          }

                          return (
                            <Grid
                              className={styles['submenu-item-link']}
                              key={poller.name}
                            >
                              <Grid
                                className={classnames(
                                  styles['dot-colored'],
                                  styles[color],
                                )}
                              >
                                <Typography variant="body2">
                                  {poller.name}
                                </Typography>
                              </Grid>
                            </Grid>
                          );
                        });
                      }

                      return null;
                    })}
                  </li>
                );
              })
            : null}
          {allowPollerConfiguration && (
            <Button
              size="small"
              style={{ marginTop: '8px' }}
              variant="contained"
              onClick={redirectsToPollersPage}
            >
              {t('Configure pollers')}
            </Button>
          )}
          <ExportConfiguration setIsExportingConfiguration={newExporting} />
        </Grid>
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
