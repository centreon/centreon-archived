import React from 'react';

import clsx from 'clsx';

import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import makeStyles from '@mui/styles/makeStyles';

import { Page } from '../../models';

import Expand from './Expand';

interface Props {
  data: Page;
  hover: boolean;
  icon?: React.ReactNode;
  isDrawerOpen?: boolean;
  isOpen: boolean;
  isRoot?: boolean;
  onClick?: React.MouseEventHandler<HTMLDivElement>;
  onMouseEnter: (e: React.MouseEvent<HTMLElement>) => void;
}
const useStyles = makeStyles((theme) => ({
  activated: {
    '& .MuiListItemText-root': {
      '& .MuiTypography-root': {
        color: theme.palette.background.paper,
      },
    },
    '& .MuiSvgIcon-root': {
      color: theme.palette.background.paper,
    },
    '&:hover': {
      backgroundColor: theme.palette.primary.main,
    },

    backgroundColor: theme.palette.primary.main,
  },
  containerIcon: {
    alignItems: 'center',
  },
  icon: {
    color: theme.palette.text.primary,
  },
}));

const ListButton = ({
  onMouseEnter,
  onClick,
  isOpen,
  icon,
  hover,
  data,
  isDrawerOpen,
  isRoot,
}: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <ListItemButton
      className={clsx({
        [classes.activated]: hover,
      })}
      component="div"
      sx={{ pl: 2 }}
      onClick={!isRoot ? onClick : undefined}
      onDoubleClick={isRoot ? onClick : undefined}
      onMouseEnter={onMouseEnter}
    >
      {isRoot ? (
        <>
          <ListItemIcon className={classes.containerIcon}>
            {icon}
            {isDrawerOpen &&
              Array.isArray(data?.children) &&
              data.children.length > 0 && (
                <Expand className={classes.icon} isOpen={isOpen} />
              )}
          </ListItemIcon>
          <ListItemText primary={data.label} />
        </>
      ) : (
        <>
          <ListItemIcon>
            {Array.isArray(data?.groups) && data.groups.length > 0 && (
              <Expand isOpen={isOpen} size="small" />
            )}
          </ListItemIcon>
          <ListItemText primary={data.label} />
        </>
      )}
    </ListItemButton>
  );
};

export default ListButton;
