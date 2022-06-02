import { useState, useRef, useEffect } from 'react';

import { useTranslation } from 'react-i18next';
import { isEmpty, isNil } from 'ramda';
import clsx from 'clsx';
import { useAtomValue } from 'jotai/utils';
import { useNavigate } from 'react-router-dom';
import MenuLoader from 'centreon-frontend/packages/centreon-ui/src/MenuSkeleton';

import PollerIcon from '@mui/icons-material/DeviceHub';
import { Button, ClickAwayListener, Paper, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

import {
  getData,
  useRequest,
  IconHeader,
  SubmenuHeader,
  IconToggleSubmenu,
} from '@centreon/ui';
import { refreshIntervalAtom } from '@centreon/ui-context';

import useNavigation from '../../Navigation/useNavigation';

import { Issues } from './models';
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

export const pollerConfigurationPageNumber = '60901';

const pollerIssueKeyToMessage = {
  database: labelDatabaseUpdatesNotActive,
  latency: labelLatencyDetected,
  stability: labelPollerNotRunning,
};

interface PollerData {
  issues: Issues;
  total: number;
}

const useStyles = makeStyles((theme) => ({
  confButton: {
    display: 'flex',
    marginTop: theme.spacing(1),
  },
  label: {
    color: theme.palette.common.white,
  },
  link: {
    textDecoration: 'none',
  },
  pollerDetailRow: {
    borderBottomStyle: 'solid',
    borderWidth: '1px',
    color: theme.palette.common.white,
    display: 'flex',
    justifyContent: 'space-between',
  },
  pollerDetailTitle: {
    flexGrow: 1,
  },
  subMenuToggle: {
    backgroundColor: theme.palette.common.black,
    boxSizing: 'border-box',
    display: 'none',
    left: theme.spacing(0),
    padding: theme.spacing(1),
    position: 'absolute',
    textAlign: 'left',
    top: '100%',
    width: '100%',
    zIndex: theme.zIndex.mobileStepper,
  },
  subMenuToggleActive: {
    display: 'block',
  },
}));

const PollerMenu = (): JSX.Element | null => {
  const classes = useStyles();

  const { t } = useTranslation();
  const { allowedPages } = useNavigation();
  const allowPollerConfiguration = allowedPages?.includes(
    pollerConfigurationPageNumber,
  );

  const [issues, setIssues] = useState<Issues | null>(null);
  const [pollerCount, setPollerCount] = useState<PollerData | number>(0);
  const [isExporting, setIsExportingConfiguration] = useState<boolean>();
  const [isAllowed, setIsAllowed] = useState<boolean>(true);
  const [toggled, setToggled] = useState<boolean>(false);
  const interval = useRef<number>();
  const navigate = useNavigate();
  const { sendRequest } = useRequest<PollerData>({
    httpCodesBypassErrorSnackbar: [401],
    request: getData,
  });
  const refreshInterval = useAtomValue(refreshIntervalAtom);

  const newExporting = (): void => {
    setIsExportingConfiguration(!isExporting);
  };

  const closeSubmenu = (): void => {
    setToggled(!toggled);
  };

  useEffect(() => {
    loadPollerData();

    interval.current = window.setInterval(() => {
      loadPollerData();
    }, refreshInterval * 1000);

    return (): void => {
      clearInterval(interval.current);
    };
  }, []);
  const loaderWidth = '35%';
  const pollerListIssues =
    'internal.php?object=centreon_topcounter&action=pollersListIssues';

  const endpoint = pollerListIssues;

  const loadPollerData = (): void => {
    sendRequest({ endpoint: `./api/${endpoint}` })
      .then((retrievedPollerData) => {
        setIssues(retrievedPollerData.issues);
        setPollerCount(retrievedPollerData.total);
      })
      .catch((error) => {
        if (error.response && error.response.status === 401) {
          setIsAllowed(false);

          return;
        }
        setIssues(null);
      });
  };

  const toggleDetailedView = (): void => {
    setToggled(!toggled);
  };

  if (!isAllowed) {
    return null;
  }

  if (isNil(issues)) {
    return <MenuLoader width={loaderWidth} />;
  }

  const redirectToPollerConfiguration = (): void => {
    closeSubmenu();
    navigate(`/main.php?p=${pollerConfigurationPageNumber}`);
  };

  return (
    <ClickAwayListener
      onClickAway={(): void => {
        if (!toggled) {
          return;
        }
        toggleDetailedView();
      }}
    >
      <div>
        <SubmenuHeader active={toggled}>
          <IconHeader
            Icon={PollerIcon}
            iconName={t(labelPoller)}
            onClick={toggleDetailedView}
          />
          <PollerStatusIcon issues={issues} />

          <IconToggleSubmenu
            cursor="pointer"
            data-testid="submenu-poller"
            iconType="arrow"
            rotate={toggled}
            onClick={toggleDetailedView}
          />
          <div
            className={clsx(classes.subMenuToggle, {
              [classes.subMenuToggleActive]: toggled,
            })}
          >
            {!isEmpty(issues) ? (
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
                      <li>{t(pollerIssueKeyToMessage[key])}</li>
                    </Typography>
                    <Typography className={classes.label} variant="body2">
                      {issue.total ? issue.total : ''}
                    </Typography>
                  </div>
                );
              })
            ) : (
              <div className={classes.pollerDetailRow}>
                <Typography className={classes.label} variant="body2">
                  {t(labelAllPollers)}
                </Typography>
                <Typography className={classes.label} variant="body2">
                  {pollerCount as number}
                </Typography>
              </div>
            )}
            {allowPollerConfiguration && (
              <Paper className={classes.confButton}>
                <Button
                  fullWidth
                  data-testid={labelConfigurePollers}
                  size="small"
                  onClick={redirectToPollerConfiguration}
                >
                  {t(labelConfigurePollers)}
                </Button>
              </Paper>
            )}
            <ExportConfiguration
              setIsExportingConfiguration={newExporting}
              toggleDetailedView={toggleDetailedView}
            />
          </div>
        </SubmenuHeader>
      </div>
    </ClickAwayListener>
  );
};

export default PollerMenu;
