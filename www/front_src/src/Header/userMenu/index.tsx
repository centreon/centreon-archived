import { MouseEvent, RefObject, useEffect, useRef, useState } from 'react';

import clsx from 'clsx';
import { useTranslation, withTranslation } from 'react-i18next';
import { useNavigate } from 'react-router-dom';
import { useUpdateAtom } from 'jotai/utils';
import { equals, gt, isNil, not, __ } from 'ramda';

import { grey } from '@mui/material/colors';
import Divider from '@mui/material/Divider';
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
import UserIcon from '@mui/icons-material/Person';
import FileCopyIcon from '@mui/icons-material/FileCopy';
import CheckIcon from '@mui/icons-material/Check';
import LogoutIcon from '@mui/icons-material/Logout';
import SettingsIcon from '@mui/icons-material/Settings';
import { makeStyles, styled } from '@mui/styles';

import {
  MenuSkeleton,
  postData,
  getData,
  useRequest,
  useSnackbar,
  useLocaleDateTimeFormat,
} from '@centreon/ui';
import { ThemeMode } from '@centreon/ui-context';

import SwitchMode from '../SwitchThemeMode/index';
import Clock from '../Clock';
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
  badge: {
    alignItems: 'center',
    borderRadius: '50%',
    display: 'flex',
    fontSize: '10px',
    height: 15,
    justifyContent: 'spaceBetween',
    minWidth: 15,
  },
  button: {
    '&:hover': {
      '&:after': {
        backgroundColor: theme.palette.common.white,
        content: '""',
        height: '100%',
        left: 0,
        opacity: 0.08,
        position: 'absolute',
        right: 0,
        top: 0,
      },
    },
  },
  clock: {
    [theme.breakpoints.down(648)]: {
      display: 'none',
    },
  },
  containerList: {
    padding: theme.spacing(0.5, 0, 0.5, 0),
  },
  divider: {
    borderColor: grey[600],
    margin: theme.spacing(0, 1.25, 0, 1.25),
  },
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
  icon: {
    minWidth: theme.spacing(3.75),
  },
  icons: {
    alignItems: 'center',
    borderLeft: `1px solid ${theme.palette.common.white}`,
    display: 'flex',
    gap: theme.spacing(2),
    [theme.breakpoints.down(1200)]: {
      gap: theme.spacing(1),
      paddingLeft: theme.spacing(2.5),
    },
    paddingLeft: theme.spacing(4),
    [theme.breakpoints.down(900)]: {
      paddingLeft: theme.spacing(1.5),
    },
    [theme.breakpoints.down(640)]: {
      borderLeft: 'none',
    },
  },
  menu: {
    backgroundColor: equals(theme.palette.mode, ThemeMode.dark)
      ? theme.palette.background.default
      : theme.palette.primary.main,
    border: 'none',
    borderRadius: 0,
    color: theme.palette.common.white,
    minWidth: 190,
  },
  menuItem: {
    padding: theme.spacing(0, 2, 0.25, 2),
  },
  nameContainer: {
    padding: theme.spacing(0, 2, 0.25, 2.25),
  },
  passwordExpiration: {
    color: theme.palette.warning.main,
  },
  popper: {
    border: 'none',
    outline: 'none',
    overflow: 'hidden',
    zIndex: theme.zIndex.tooltip,
  },
  switchItem: {
    padding: theme.spacing(0, 2, 0.25, 11 / 8),
  },
  text: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
  },
  userIcon: {
    color: theme.palette.common.white,
    cursor: 'pointer',
    fontSize: theme.spacing(4),
  },
  wrapRightUser: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'wrap',
    position: 'relative',
    width: '100%',
  },
  wrapRightUserItems: {
    display: 'flex',
    gap: theme.spacing(4),
    justifyContent: 'flex-end',
    [theme.breakpoints.down(1200)]: {
      gap: theme.spacing(2.5),
    },
    [theme.breakpoints.down(900)]: {
      gap: theme.spacing(1.5),
    },
    width: '100%',
  },
}));
interface Props {
  headerRef?: RefObject<HTMLElement>;
}

const UserMenu = ({ headerRef }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const { allowedPages } = useNavigation();

  const [copied, setCopied] = useState(false);
  const [data, setData] = useState<UserData | null>(null);
  const [anchorEl, setAnchorEl] = useState<SVGSVGElement | null>(null);
  const [anchorHeight, setAnchorHeight] = useState(12);
  const profile = useRef<HTMLDivElement>();
  const userMenu = useRef<HTMLDivElement>();
  const autologinNode = useRef<HTMLTextAreaElement>();
  const refreshTimeout = useRef<NodeJS.Timeout>();
  const userIconRef = useRef<SVGSVGElement | null>(null);
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

  const getPositionOfPopper = (): void => {
    if (isNil(headerRef?.current) || isNil(userIconRef?.current)) {
      return;
    }
    const headerHeight = headerRef?.current?.getBoundingClientRect()?.height;

    const userMenuBottom =
      userIconRef?.current?.getBoundingClientRect()?.bottom;

    if (isNil(headerHeight)) {
      return;
    }
    setAnchorHeight(headerHeight - userMenuBottom);
  };

  const toggle = (event: MouseEvent<SVGSVGElement>): void => {
    if (anchorEl) {
      setAnchorEl(null);

      return;
    }
    setAnchorEl(event.currentTarget);
    getPositionOfPopper();
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
    window.addEventListener('resize', getPositionOfPopper);

    loadUserData();

    return (): void => {
      window.removeEventListener('mousedown', handleClick, false);
      window.removeEventListener('resize', getPositionOfPopper);

      if (refreshTimeout.current) {
        clearTimeout(refreshTimeout.current);
      }
    };
  }, []);

  if (!data) {
    return <MenuSkeleton width={24} />;
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
    <div className={classes.wrapRightUser}>
      <div
        className={classes.wrapRightUserItems}
        ref={profile as RefObject<HTMLDivElement>}
      >
        <div className={classes.clock}>
          <Clock />
        </div>
        <div className={classes.icons}>
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
                className={classes.userIcon}
                data-cy="userIcon"
                fontSize="large"
                ref={userIconRef}
                onClick={toggle}
              />
            </Badge>
          </Tooltip>
          <Popper
            transition
            anchorEl={anchorEl}
            className={classes.popper}
            data-cy="popper"
            modifiers={[
              {
                name: 'offset',
                options: {
                  offset: [22, anchorHeight],
                },
              },
            ]}
            open={not(isNil(anchorEl))}
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
                  <List dense className={classes.containerList}>
                    <ListItem className={classes.nameContainer}>
                      <ListItemText
                        primaryTypographyProps={primaryTypographyProps}
                      >
                        {data.username}
                      </ListItemText>
                    </ListItem>
                    <Divider className={classes.divider} />

                    {not(passwordIsNotYetAboutToExpire) && (
                      <ListItem className={classes.menuItem}>
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
                      <ListItem disableGutters disablePadding>
                        <ListItemButton
                          className={classes.button}
                          onClick={navigateToUserSettingsAndCloseUserMenu}
                        >
                          <ListItemIcon className={classes.icon}>
                            <SettingsIcon fontSize="small" />
                          </ListItemIcon>
                          <ListItemText>{t(labelEditProfile)}</ListItemText>
                        </ListItemButton>
                      </ListItem>
                    )}
                    {data.autologinkey && (
                      <ListItem disableGutters disablePadding>
                        <ListItemButton onClick={onCopy}>
                          <ListItemIcon className={classes.icon}>
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
                    <div className={classes.switchItem}>
                      <SwitchMode />
                    </div>

                    <Divider className={classes.divider} />

                    <ListItem disableGutters disablePadding>
                      <ListItemButton
                        className={classes.button}
                        onClick={logoutFromSession}
                      >
                        <ListItemIcon className={classes.icon}>
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
