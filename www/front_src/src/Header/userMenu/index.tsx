/* eslint-disable react/button-has-type */
import * as React from 'react';

import classnames from 'classnames';
import { useTranslation, withTranslation } from 'react-i18next';
import { connect } from 'react-redux';
import { Link } from 'react-router-dom';

import { alpha, Typography } from '@mui/material';
import UserIcon from '@mui/icons-material/AccountCircle';
import FileCopyIcon from '@mui/icons-material/FileCopy';
import CheckIcon from '@mui/icons-material/Check';
import { makeStyles } from '@mui/styles';

import { getData, useRequest } from '@centreon/ui';

import { allowedPagesSelector } from '../../redux/selectors/navigation/allowedPages';
import styles from '../header.scss';
import Clock from '../Clock';
import MenuLoader from '../../components/MenuLoader';

// eslint-disable-next-line @typescript-eslint/naming-convention
const EDIT_PROFILE_TOPOLOGY_PAGE = '50104';

const useStyles = makeStyles((theme) => ({
  fullname: {
    overflow: 'hidden',
    textOverflow: 'ellipsis',
    whiteSpace: 'nowrap',
    width: '115px',
  },
  itemLink: {
    // '#232f39'
    backgroundColor: '#232f39',
    color: 'white',
    display: 'flex',
    flexDirection: 'row',
    fontSize: '0.8rem',
    justifyContent: 'space-between',
    padding: 10,
    textDecoration: 'none',
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
    textAlign: 'left',
    top: '100%',
    width: '100%',
    zIndex: 92,
  },
  subMenuActive: {
    display: 'block',
  },
  subMenuItemContent: {
    backgroundColor: '#232f39',
    padding: 10,
  },
  submenuUserButton: {
    '& span:first-child': {
      display: 'block',
      lineHeight: '17px',
    },
    '&:hover': {
      backgroundColor: 'rgba(255, 161, 37, 1)',
      color: 'rgb(0,9,22)',
    },
    alignItems: 'center',
    backgroundColor: 'transparent',
    border: '1px solid rgba(255, 161, 37, 1)',
    borderRadius: '16px',
    boxSizing: 'border-box',
    color: '#ffa225',
    display: 'flex',
    fontSize: '.7rem',
    justifyContent: 'space-between',
    lineHeight: '31px',
    margin: '0 auto',
    outline: 'none',
    padding: '2px 10px 2px 10px',
    position: 'relative',
    textAlign: 'left',
    textDecoration: 'none',
    width: '95%',
  },
  submenuUserEdit: {
    color: 'white',
    float: 'right',
    marginLeft: 4,
    textDecorationLine: 'underline',
  },
  wrapRightUser: {
    alignItems: 'center',
    display: 'flex',
    flexWrap: 'wrap',
    padding: '6px 22px 6px 61px',
    position: 'relative',
  },
  wrapRightUserActive: {
    backgroundColor: '#000915',
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
  const [data, setData] = React.useState<any>(null);
  const [toggled, setToggled] = React.useState(false);
  const profile = React.useRef<HTMLDivElement>();
  const autologinNode = React.useRef<HTMLTextAreaElement>();
  const refreshTimeout = React.useRef<NodeJS.Timeout>();
  const { sendRequest } = useRequest<any>({
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

  const allowEditProfile = allowedPages?.includes(EDIT_PROFILE_TOPOLOGY_PAGE);

  const gethref = window.location.href;
  const conditionnedhref = gethref + (window.location.search ? '&' : '?');
  const autolink = `${conditionnedhref}autologin=1&useralias=${data?.username}&token=${data?.autologinkey}`;

  return (
    <div
      className={classnames(classes.wrapRightUser, {
        [classes.wrapRightUserActive]: toggled,
      })}
    >
      <div className={classnames(classes.wrapRightUserItems)}>
        <Clock />
        <div ref={profile as React.RefObject<HTMLDivElement>}>
          <UserIcon
            fontSize="large"
            style={{ color: '#FFFFFF', cursor: 'pointer', marginLeft: 8 }}
            onClick={toggle}
          />
          <div
            className={classnames(classes.subMenu, {
              [classes.subMenuActive]: toggled,
            })}
          >
            <div>
              <ul className={classnames(styles['list-unstyled'])}>
                <li className={styles['submenu-item']}>
                  <div
                    className={classnames(
                      classes.itemLink,
                      classes.nameAliasContainer,
                    )}
                  >
                    <div>
                      <Typography className={classes.fullname} variant="body2">
                        {data?.fullname}
                      </Typography>
                      <Typography
                        style={{ wordWrap: 'break-word' }}
                        variant="body2"
                      >
                        {t('as')}
                        {` ${data?.username}`}
                      </Typography>
                    </div>
                    {allowEditProfile && (
                      <Link
                        submenuUserEdit
                        className={classnames(classes.submenuUserEdit)}
                        to={`/main.php?p=${EDIT_PROFILE_TOPOLOGY_PAGE}&o=c`}
                        onClick={toggle}
                      >
                        <Typography variant="body2">
                          {t('Edit profile')}
                        </Typography>
                      </Link>
                    )}
                  </div>
                </li>
                {data && data.autoLoginKey && (
                  <div className={classnames(classes.subMenuItemContent)}>
                    <button
                      className={classnames(classes.submenuUserButton)}
                      onClick={onCopy}
                    >
                      {t('Copy autologin link')}

                      {copied ? <CheckIcon /> : <FileCopyIcon />}
                    </button>
                    <textarea
                      className={styles['hidden-input']}
                      id="autologin-input"
                      ref={
                        autologinNode as React.RefObject<HTMLTextAreaElement>
                      }
                      value={autolink}
                    />
                  </div>
                )}
              </ul>
              <div className={classnames(classes.subMenuItemContent)}>
                <a
                  className={classnames(classes.logoutLink)}
                  href="index.php?disconnect=1"
                >
                  <button
                    className={classnames(
                      styles.btn,
                      styles['btn-small'],
                      styles.logout,
                    )}
                  >
                    {t('Logout')}
                  </button>
                </a>
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
