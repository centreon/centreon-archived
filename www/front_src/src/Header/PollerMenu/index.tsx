import { useState, useRef, useEffect } from 'react';

import { useTranslation } from 'react-i18next';
import { equals, isEmpty, isNil } from 'ramda';
import clsx from 'clsx';
import { useAtomValue } from 'jotai/utils';
import { useNavigate } from 'react-router-dom';

import PollerIcon from '@mui/icons-material/DeviceHub';
import { Button, ClickAwayListener, Typography } from '@mui/material';
import { makeStyles } from '@mui/styles';

import {
  MenuSkeleton,
  getData,
  useRequest,
  IconHeader,
  IconToggleSubmenu,
} from '@centreon/ui';
import { refreshIntervalAtom, ThemeMode } from '@centreon/ui-context';

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
    '&:hover': {
      background: theme.palette.grey[500],
    },
    backgroundColor: equals(theme.palette.mode, ThemeMode.dark)
      ? theme.palette.background.default
      : theme.palette.primary.main,
    border: '1px solid white',
    color: theme.palette.common.white,
    display: 'flex',
    fontSize: theme.typography.body2.fontSize,
    marginTop: theme.spacing(1),
  },
  container: {
    borderRight: '1px solid white',
    display: 'flex',
    paddingRight: theme.spacing(3),
    [theme.breakpoints.down(768)]: {
      paddingRight: theme.spacing(1),
    },
    position: 'relative',
  },
  iconToggleMenu: {
    alignItems: 'flex-end',
    display: 'flex',
    [theme.breakpoints.down(768)]: {
      alignItems: 'center',
      justifyContent: 'center',
      position: 'relative',
      right: theme.spacing(0.5),
    },
  },
  link: {
    textDecoration: 'none',
  },
  pollarHeaderRight: {
    display: 'flex',
    flexDirection: 'column',
    justifyContent: 'space-between',
    [theme.breakpoints.down(768)]: {
      flexDirection: 'row',
      gap: theme.spacing(0.5),
    },
  },
  pollerDetailRow: {
    borderBottom: '1px solid',
    display: 'flex',
    justifyContent: 'space-between',
  },
  pollerDetailTitle: {
    flexGrow: 1,
  },
  subMenuToggle: {
    backgroundColor: theme.palette.background.default,
    boxShadow: theme.shadows[3],
    boxSizing: 'border-box',
    color: theme.palette.text.primary,
    display: 'none',
    left: 0,
    padding: theme.spacing(1),
    position: 'absolute',
    textAlign: 'left',
    top: `calc(100% + ${theme.spacing(1.25)})`,
    width: theme.spacing(20),
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
    return <MenuSkeleton />;
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
      <div className={classes.container}>
        <IconHeader
          Icon={PollerIcon}
          iconName={t(labelPoller)}
          onClick={toggleDetailedView}
        />

        <div className={classes.pollarHeaderRight}>
          <PollerStatusIcon issues={issues} />
          <div className={classes.iconToggleMenu}>
            <IconToggleSubmenu
              data-testid="submenu-poller"
              rotate={toggled}
              onClick={toggleDetailedView}
            />
          </div>
        </div>

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
                    className={classes.pollerDetailTitle}
                    variant="body2"
                  >
                    <li>{t(pollerIssueKeyToMessage[key])}</li>
                  </Typography>
                  <Typography variant="body2">
                    {issue.total ? issue.total : ''}
                  </Typography>
                </div>
              );
            })
          ) : (
            <div className={classes.pollerDetailRow}>
              <Typography variant="body2">{t(labelAllPollers)}</Typography>
              <Typography variant="body2">{pollerCount as number}</Typography>
            </div>
          )}
          {allowPollerConfiguration && (
            <Button
              fullWidth
              className={classes.confButton}
              data-testid={labelConfigurePollers}
              size="small"
              onClick={redirectToPollerConfiguration}
            >
              {t(labelConfigurePollers)}
            </Button>
          )}
          <ExportConfiguration
            setIsExportingConfiguration={newExporting}
            toggleDetailedView={toggleDetailedView}
          />
        </div>
      </div>
    </ClickAwayListener>
  );
};

export default PollerMenu;
