import { MouseEvent, RefObject, useEffect, useRef, useState } from 'react';

import clsx from 'clsx';
import { useTranslation, withTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useUpdateAtom } from 'jotai/utils';
import { gt, isNil, not, __ } from 'ramda';

import {
  Typography,
  Paper,
  Badge,
  Tooltip,
  List,
  ListItem,
  ListItemText,
  Popper,
  ListItemButton,
  ListItemIcon as MUIListItemIcon,
  Fade,
} from '@mui/material';
import UserIcon from '@mui/icons-material/AccountCircle';
import FileCopyIcon from '@mui/icons-material/FileCopy';
import CheckIcon from '@mui/icons-material/Check';
import LogoutIcon from '@mui/icons-material/Logout';
import SettingsIcon from '@mui/icons-material/Settings';
import { makeStyles, styled } from '@mui/styles';

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
  labelCopyAutologinLink,
  labelEditProfile,
  labelLogout,
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

const ListItemIcon = styled(MUIListItemIcon)(({ theme }) => ({
  '& .MuiSvgIcon-root': {
    color: theme.palette.common.white,
  },
}));

const useStyles = makeStyles((theme) => ({
  fullname: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
  hiddenInput: {
    height: theme.spacing(0),
    opacity: 0,
    position: 'absolute',
    top: theme.spacing(-13),
    width: theme.spacing(0),
  },
  menu: {
    backgroundColor: theme.palette.common.black,
    color: theme.palette.common.white,
    maxWidth: 230,
    width: '100%',
  },
  passwordExpiration: {
    color: theme.palette.warning.main,
  },
  popper: {
    overflow: 'hidden',
    zIndex: theme.zIndex.tooltip,
  },
  text: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
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
    marginLeft: theme.spacing(0.5),
    padding: theme.spacing(0.75, 2.75, 0.75, 1.5),
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

  const [copied, setCopied] = useState(false);
  const [data, setData] = useState<UserData | null>(null);
  const [anchorEl, setAnchorEl] = useState<SVGSVGElement | null>(null);
  const profile = useRef<HTMLDivElement>();
  const userMenu = useRef<HTMLDivElement>();
  const autologinNode = useRef<HTMLTextAreaElement>();
  const refreshTimeout = useRef<NodeJS.Timeout>();
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

  const toggle = (event: MouseEvent<SVGSVGElement>): void => {
    if (anchorEl) {
      setAnchorEl(null);

      return;
    }
    setAnchorEl(event.currentTarget);
  };

  const closeUserMenu = (): void => {
    setAnchorEl(null);
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
    const isProfileClicked =
      !profile.current || profile.current.contains(e.target);
    const isUserMenuClicked =
      !userMenu.current || userMenu.current.contains(e.target);

    if (isProfileClicked || isUserMenuClicked) {
      return;
    }
    setAnchorEl(null);
  };

  const navigateToUserSettingsAndCloseUserMenu = (): void => {
    navigate(`/main.php?p=${editProfileTopologyPage}&o=c`);
    closeUserMenu();
  };

  const logoutFromSession = (e: MouseEvent): void => {
    e.preventDefault();
    logout();
  };

  useEffect(() => {
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

  const primaryTypographyProps = {
    className: classes.text,
  };

  return (
    <div className={clsx(classes.wrapRightUser)}>
      <div
        className={clsx(classes.wrapRightUserItems)}
        ref={profile as RefObject<HTMLDivElement>}
      >
        <Clock />
        <div>
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
                className={clsx(classes.userIcon)}
                fontSize="large"
                onClick={toggle}
              />
            </Badge>
          </Tooltip>
          <Popper
            transition
            anchorEl={anchorEl}
            className={classes.popper}
            open={not(isNil(anchorEl))}
            placement="bottom-end"
          >
            {({ TransitionProps }): JSX.Element => (
              <Fade {...TransitionProps} timeout={350}>
                <Paper
                  className={classes.menu}
                  ref={userMenu as RefObject<HTMLDivElement>}
                  sx={{
                    display: isNil(anchorEl) ? 'none' : 'block',
                  }}
                >
                  <List dense>
                    <ListItem>
                      <ListItemText
                        primaryTypographyProps={primaryTypographyProps}
                      >
                        {data.fullname}
                      </ListItemText>
                    </ListItem>
                    <ListItem>
                      <ListItemText
                        primaryTypographyProps={primaryTypographyProps}
                      >{`${t('as')} ${data.username}`}</ListItemText>
                    </ListItem>
                    {not(passwordIsNotYetAboutToExpire) && (
                      <ListItem>
                        <div className={classes.passwordExpiration}>
                          <Typography variant="body2">
                            {t(labelPasswordWillExpireIn)}:
                          </Typography>
                          <Typography variant="body2">
                            {formattedPasswordRemainingTime}
                          </Typography>
                        </div>
                      </ListItem>
                    )}
                    {allowEditProfile && (
                      <ListItem disableGutters>
                        <ListItemButton
                          onClick={navigateToUserSettingsAndCloseUserMenu}
                        >
                          <ListItemIcon>
                            <SettingsIcon fontSize="small" />
                          </ListItemIcon>
                          <ListItemText>{t(labelEditProfile)}</ListItemText>
                        </ListItemButton>
                      </ListItem>
                    )}
                    {data.autologinkey && (
                      <ListItem disableGutters>
                        <ListItemButton onClick={onCopy}>
                          <ListItemIcon>
                            {copied ? (
                              <CheckIcon fontSize="small" />
                            ) : (
                              <FileCopyIcon fontSize="small" />
                            )}
                          </ListItemIcon>
                          <ListItemText>
                            {t(labelCopyAutologinLink)}
                          </ListItemText>
                        </ListItemButton>
                        <textarea
                          readOnly
                          className={clsx(classes.hiddenInput)}
                          id="autologin-input"
                          ref={autologinNode as RefObject<HTMLTextAreaElement>}
                          value={autolink}
                        />
                      </ListItem>
                    )}
                    <ListItem disableGutters>
                      <ListItemButton onClick={logoutFromSession}>
                        <ListItemIcon>
                          <LogoutIcon fontSize="small" />
                        </ListItemIcon>
                        <ListItemText>{t(labelLogout)}</ListItemText>
                      </ListItemButton>
                    </ListItem>
                  </List>
                </Paper>
              </Fade>
            )}
          </Popper>
        </div>
      </div>
    </div>
  );
};

export default withTranslation()(UserMenu);
