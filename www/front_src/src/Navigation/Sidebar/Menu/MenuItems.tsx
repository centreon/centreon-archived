import React from 'react';

import clsx from 'clsx';

import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import makeStyles from '@mui/styles/makeStyles';

import { Page } from '../../models';

import ArrowIcon from './ArrowIcon';

export const heightItem = 37;

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
    minWidth: theme.spacing(5.75),
  },
  icon: {
    color: theme.palette.text.primary,
  },
  label: {
    '& .MuiTypography-root': {
      fontSize: 11,
    },
    margin: theme.spacing(0),
  },
  listButton: {
    alignItems: 'center',
    height: theme.spacing(heightItem / 8),
    marginBottom: 1,
  },
  rootLabel: {
    margin: theme.spacing(0),
  },
}));

const MenuItems = ({
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
      className={clsx(classes.listButton, {
        [classes.activated]: hover,
      })}
      component="div"
      sx={!isRoot ? { pl: 0 } : { pl: 1.5 }}
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
                <ArrowIcon
                  className={classes.icon}
                  isOpen={isOpen}
                  size="small"
                />
              )}
          </ListItemIcon>
          <ListItemText className={classes.rootLabel} primary={data.label} />
        </>
      ) : (
        <>
          <ListItemIcon>
            {Array.isArray(data?.groups) && data.groups.length > 0 && (
              <ArrowIcon isOpen={isOpen} size="small" />
            )}
          </ListItemIcon>
          <ListItemText className={classes.label} secondary={data.label} />
        </>
      )}
    </ListItemButton>
  );
};

export default MenuItems;
