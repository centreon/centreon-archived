import * as React from 'react';

import classnames from 'classnames';
import { useTranslation, withTranslation } from 'react-i18next';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';

import { Button, Typography, Paper } from '@mui/material';
import UserIcon from '@mui/icons-material/AccountCircle';
import FileCopyIcon from '@mui/icons-material/FileCopy';
import CheckIcon from '@mui/icons-material/Check';
import { makeStyles } from '@mui/styles';

import { getData, useRequest } from '@centreon/ui';

import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';
import Clock from '../Clock';
import MenuLoader from '../../components/MenuLoader';

const editProfileTopologyPage = '50104';

interface UserData {
  autologinkey: string | null;
  fullname: string | null;
  hasAccessToProfile: boolean;
  locale: string | null;
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
  submenuUserButton: {
    '& span:first-child': {
      display: 'block',
      lineHeight: '17px',
    },
    '&:hover': {
      backgroundColor: theme.palette.warning.main,
      color: theme.palette.common.black,
    },
    alignItems: 'center',
    border: `1px solid ${theme.palette.warning.main}`,
    borderRadius: '16px',
    boxSizing: 'border-box',
    color: theme.palette.warning.main,
    display: 'flex',
    fontSize: theme.typography.body2.fontSize,
    justifyContent: 'space-between',
    lineHeight: '31px',
    margin: '0 auto',
    outline: 'none',
    padding: theme.spacing(0.25, 1.25, 0.25, 1.25),
    position: 'relative',
    textAlign: 'left',
    textDecoration: 'none',
    width: '95%',
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
    padding: theme.spacing(0.75, 2.75, 0.75, 7.6),
    position: 'relative',
  },
  wrapRightUserItems: {
    display: 'flex',
    flex: '1 0 76%',
    justifyContent: 'flex-end',
  },
}));

const UserMenu = ({ allowedPages }: StateToProps): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const userMenuInfo = 'internal.php?object=centreon_topcounter&action=user';

  const [copied, setCopied] = React.useState(false);
  const [data, setData] = React.useState<UserData | null>(null);
  const [toggled, setToggled] = React.useState(false);
  const profile = React.useRef<HTMLDivElement>();
  const autologinNode = React.useRef<HTMLTextAreaElement>();
  const refreshTimeout = React.useRef<NodeJS.Timeout>();
  const { sendRequest } = useRequest<UserData>({
    request: getData,
  });

  React.useEffect(() => {
    window.addEventListener('mousedown', handleClick, false);
    loaduserData();
    loaduserData();

    return (): void => {
      window.removeEventListener('mousedown', handleClick, false);
      if (refreshTimeout.current) {
        clearTimeout(refreshTimeout.current);
      }
    };
  }, []);

  const endpoint = userMenuInfo;

  const loaduserData = (): void => {
    sendRequest({ endpoint: `./api/${endpoint}` })
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

  const refreshData = (): void => {
    if (refreshTimeout.current) {
      clearTimeout(refreshTimeout.current);
    }
    refreshTimeout.current = setTimeout(() => {
      loaduserData();
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
    }
  };

  const handleClick = (e): void => {
    if (!profile.current || profile.current.contains(e.target)) {
      return;
    }
    setToggled(false);
  };

  if (!data) {
    return <MenuLoader width={21} />;
  }

  const allowEditProfile = allowedPages?.includes(editProfileTopologyPage);

  const gethref = window.location.href;
  const conditionnedhref = gethref + (window.location.search ? '&' : '?');
  const autolink = `${conditionnedhref}autologin=1&useralias=${data.username}&token=${data.autologinkey}`;

  return (
    <div className={classnames(classes.wrapRightUser)}>
      <div className={classnames(classes.wrapRightUserItems)}>
        <Clock />
        <div ref={profile as React.RefObject<HTMLDivElement>}>
          <UserIcon
            className={classnames(classes.userIcon)}
            fontSize="large"
            onClick={toggle}
          />
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
                        submenuUserEdit
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
              <div className={classnames(classes.logoutLink)}>
                <Paper className={classes.userButton}>
                  <Button fullWidth href="index.php?disconnect=1" size="small">
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

interface StateToProps {
  allowedPages: Array<string>;
}

const mapStateToProps = (state): StateToProps => ({
  allowedPages: allowedPagesSelector(state),
});

const mapDispatchToProps = {};

export default withTranslation()(
  connect(mapStateToProps, mapDispatchToProps)(UserMenu),
);
