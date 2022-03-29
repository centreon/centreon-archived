import * as React from 'react';

import classnames from 'classnames';
import { useTranslation, withTranslation } from 'react-i18next';
import { Link, useNavigate } from 'react-router-dom';
import { useUpdateAtom } from 'jotai/utils';
import { gt, isNil, not, __ } from 'ramda';

import { Button, Typography, Paper, Badge, Tooltip } from '@mui/material';
import UserIcon from '@mui/icons-material/AccountCircle';
import FileCopyIcon from '@mui/icons-material/FileCopy';
import CheckIcon from '@mui/icons-material/Check';
import { makeStyles } from '@mui/styles';

import {
  postData,
  getData,
  useRequest,
  useSnackbar,
  useLocaleDateTimeFormat,
} from '@centreon/ui';

import Clock from '../Clock';
import MenuLoader from '../../components/MenuLoader';
import useNavigation from '../../Navigation/useNavigation';
import { areUserParametersLoadedAtom } from '../../Main/useUser';
import { logoutEndpoint } from '../../api/endpoint';
import reactRoutes from '../../reactRoutes/routeMap';
import { passwordResetInformationsAtom } from '../../ResetPassword/passwordResetInformationsAtom';
import {
  selectedNavigationItemsAtom,
  hoveredNavigationItemsAtom,
} from '../../Navigation/Sidebar/sideBarAtoms';

import { userEndpoint } from './api/endpoint';
import {
  labelPasswordWillExpireIn,
  labelProfile,
  labelYouHaveBeenLoggedOut,
} from './translatedLabels';

const editProfileTopologyPage = '50104';
const sevenDays = 60 * 60 * 24 * 7;
const isGreaterThanSevenDays = gt(__, sevenDays);

interface UserData {
  autologinkey: string | null;
  fullname: string | null;
  hasAccessToProfile: boolean;
  locale: string | null;
  password_remaining_time?: number | null;
  soundNotificationsEnabled: boolean;
  timezone: string | null;
  userId: string | null;
  username: string | null;
}

const useStyles = makeStyles((theme) => ({
  fullname: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
    width: theme.spacing(14),
  },
  hiddenInput: {
    height: theme.spacing(0),
    opacity: 0,
    position: 'absolute',
    top: theme.spacing(-13),
    width: theme.spacing(0),
  },
  itemLink: {
    backgroundColor: theme.palette.common.black,
    color: theme.palette.common.white,
    display: 'flex',
    flexDirection: 'row',
    justifyContent: 'space-between',
    padding: theme.spacing(1),
    textDecoration: 'none',
  },
  listUnstyled: {
    listStyle: 'none',
    margin: theme.spacing(0),
    paddingLeft: theme.spacing(0),
  },
  logoutLink: {
    display: 'grid',
    justifyContent: 'flex-end',
  },
  nameAliasContainer: {
    display: 'grid',
    gridTemplateColumns: '2fr 1fr',
  },
  passwordExpiration: {
    color: theme.palette.warning.main,
  },
  subMenu: {
    boxSizing: 'border-box',
    display: 'none',
    height: '100vh',
    left: 0,
    overflowY: 'auto',
    position: 'absolute',
    top: '100%',
    width: '100%',
    zIndex: theme.zIndex.mobileStepper,
  },
  subMenuActive: {
    display: 'block',
  },
  subMenuItemContent: {
    backgroundColor: theme.palette.common.black,
    padding: theme.spacing(1),
  },
  submenuUserEdit: {
    color: theme.palette.common.white,
    float: 'right',
    textDecorationLine: 'underline',
    textTransform: 'capitalize',
  },
  userButton: {
    display: 'flex',
    marginTop: theme.spacing(1),
  },
  userIcon: {
    color: theme.palette.common.white,
    cursor: 'pointer',
    marginLeft: theme.spacing(1),
  },
  wrapRightUser: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'wrap',
    padding: theme.spacing(0.75, 2.75, 0.75, 1),
    position: 'relative',
  },
  wrapRightUserItems: {
    display: 'flex',
    flex: '1 0 76%',
    justifyContent: 'flex-end',
  },
}));

const UserMenu = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { allowedPages } = useNavigation();

  const [copied, setCopied] = React.useState(false);
  const [data, setData] = React.useState<UserData | null>(null);
  const [toggled, setToggled] = React.useState(false);
  const profile = React.useRef<HTMLDivElement>();
  const autologinNode = React.useRef<HTMLTextAreaElement>();
  const refreshTimeout = React.useRef<NodeJS.Timeout>();
  const { sendRequest: logoutRequest } = useRequest({
    request: postData,
  });
  const { sendRequest } = useRequest<UserData>({
    request: getData,
  });

  const navigate = useNavigate();
  const { showSuccessMessage } = useSnackbar();
  const { toHumanizedDuration } = useLocaleDateTimeFormat();

  const setAreUserParametersLoaded = useUpdateAtom(areUserParametersLoadedAtom);
  const setPasswordResetInformationsAtom = useUpdateAtom(
    passwordResetInformationsAtom,
  );
  const setSelectedNavigationItems = useUpdateAtom(selectedNavigationItemsAtom);
  const setHoveredNavigationItems = useUpdateAtom(hoveredNavigationItemsAtom);

  const loadUserData = (): void => {
    sendRequest({ endpoint: userEndpoint })
      .then((retrievedUserData) => {
        setData(retrievedUserData);
        refreshData();
      })
      .catch((error) => {
        if (error.response && error.response.status === 401) {
          setData(null);
        }
      });
  };

  const logout = (): void => {
    logoutRequest({
      data: {},
      endpoint: logoutEndpoint,
    }).then(() => {
      setAreUserParametersLoaded(false);
      setPasswordResetInformationsAtom(null);
      setSelectedNavigationItems(null);
      setHoveredNavigationItems(null);
      navigate(reactRoutes.login);
      showSuccessMessage(t(labelYouHaveBeenLoggedOut));
    });
  };

  const refreshData = (): void => {
    if (refreshTimeout.current) {
      clearTimeout(refreshTimeout.current);
    }
    refreshTimeout.current = setTimeout(() => {
      loadUserData();
    }, 60000);
  };

  const toggle = (): void => {
    setToggled(!toggled);
  };

  const onCopy = (): void => {
    if (autologinNode && autologinNode.current) {
      autologinNode.current.select();
      window.document.execCommand('copy');
      setCopied(true);
      setTimeout(() => {
        setCopied(false);
      }, 700);
    }
  };

  const handleClick = (e): void => {
    if (!profile.current || profile.current.contains(e.target)) {
      return;
    }
    setToggled(false);
  };

  React.useEffect(() => {
    window.addEventListener('mousedown', handleClick, false);
    loadUserData();

    return (): void => {
      window.removeEventListener('mousedown', handleClick, false);
      if (refreshTimeout.current) {
        clearTimeout(refreshTimeout.current);
      }
    };
  }, []);

  if (!data) {
    return <MenuLoader width={21} />;
  }

  const allowEditProfile = allowedPages?.includes(editProfileTopologyPage);

  const gethref = window.location.href;
  const conditionnedhref = gethref + (window.location.search ? '&' : '?');
  const autolink = `${conditionnedhref}autologin=1&useralias=${data.username}&token=${data.autologinkey}`;

  const passwordIsNotYetAboutToExpire =
    isNil(data.password_remaining_time) ||
    isGreaterThanSevenDays(data.password_remaining_time);

  const formattedPasswordRemainingTime = toHumanizedDuration(
    data.password_remaining_time as number,
  );

  return (
    <div className={classnames(classes.wrapRightUser)}>
      <div className={classnames(classes.wrapRightUserItems)}>
        <Clock />
        <div ref={profile as React.RefObject<HTMLDivElement>}>
          <Tooltip
            title={
              passwordIsNotYetAboutToExpire
                ? ''
                : `${t(
                    labelPasswordWillExpireIn,
                  )}: ${formattedPasswordRemainingTime}`
            }
          >
            <Badge
              color="warning"
              invisible={passwordIsNotYetAboutToExpire}
              variant="dot"
            >
              <UserIcon
                aria-label={t(labelProfile)}
                className={classnames(classes.userIcon)}
                fontSize="large"
                onClick={toggle}
              />
            </Badge>
          </Tooltip>
          <div
            className={classnames(classes.subMenu, {
              [classes.subMenuActive]: toggled,
            })}
          >
            <div className={classes.subMenuItemContent}>
              <ul className={classnames(classes.listUnstyled)}>
                <li>
                  <div
                    className={classnames(
                      classes.itemLink,
                      classes.nameAliasContainer,
                    )}
                  >
                    <div>
                      <Typography className={classes.fullname} variant="body2">
                        {data.fullname}
                      </Typography>
                      <Typography
                        style={{ wordWrap: 'break-word' }}
                        variant="body2"
                      >
                        {t('as')}
                        {` ${data.username}`}
                      </Typography>
                    </div>
                    {allowEditProfile && (
                      <Link
                        className={classnames(classes.submenuUserEdit)}
                        to={`/main.php?p=${editProfileTopologyPage}&o=c`}
                        onClick={toggle}
                      >
                        <Typography variant="body2">
                          {t('Edit profile')}
                        </Typography>
                      </Link>
                    )}
                  </div>
                </li>
                {data.autologinkey && (
                  <Paper className={classes.userButton}>
                    <Button
                      fullWidth
                      endIcon={copied ? <CheckIcon /> : <FileCopyIcon />}
                      size="small"
                      onClick={onCopy}
                    >
                      {t('Copy autologin link')}
                    </Button>

                    <textarea
                      readOnly
                      className={classnames(classes.hiddenInput)}
                      id="autologin-input"
                      ref={
                        autologinNode as React.RefObject<HTMLTextAreaElement>
                      }
                      value={autolink}
                    />
                  </Paper>
                )}
              </ul>
              {not(passwordIsNotYetAboutToExpire) && (
                <div
                  className={classnames(
                    classes.subMenuItemContent,
                    classes.passwordExpiration,
                  )}
                >
                  <Typography variant="body2">
                    {t(labelPasswordWillExpireIn)}:
                  </Typography>
                  <Typography variant="body2">
                    {formattedPasswordRemainingTime}
                  </Typography>
                </div>
              )}
              <div className={classnames(classes.logoutLink)}>
                <Paper className={classes.userButton}>
                  <Button
                    fullWidth
                    aria-label={t('Logout')}
                    href="index.php"
                    size="small"
                    onClick={(e: React.MouseEvent): void => {
                      e.preventDefault();
                      logout();
                    }}
                  >
                    {t('Logout')}
                  </Button>
                </Paper>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};

export default withTranslation()(UserMenu);
