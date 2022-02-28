import React from 'react';

import Box from '@mui/material/Box';
import Divider from '@mui/material/Divider';
import MuiDrawer from '@mui/material/Drawer';
import { CSSObject, styled, Theme } from '@mui/material/styles';
import { makeStyles } from '@mui/styles';

import { Page } from '../models';

import Logo from './Logo';
import NavigationMenu from './Menu';

const drawerWidth = 230;

const useStyles = makeStyles(() => ({
  logo: {
    '&:hover': {
      cursor: 'pointer',
    },
  },
}));

const openedMixin = (theme: Theme): CSSObject => ({
  overflowX: 'hidden',
  transition: theme.transitions.create('width', {
    duration: theme.transitions.duration.enteringScreen,
    easing: theme.transitions.easing.sharp,
  }),
  width: drawerWidth,
});

const closedMixin = (theme: Theme): CSSObject => ({
  overflowX: 'hidden',
  transition: theme.transitions.create('width', {
    duration: theme.transitions.duration.leavingScreen,
    easing: theme.transitions.easing.sharp,
  }),
  width: `calc(${theme.spacing(7)} + 1px)`,
  [theme.breakpoints.up('sm')]: {
    width: `calc(${theme.spacing(9)} + 1px)`,
  },
});

const DrawerHeader = styled('div')(() => ({
  alignItems: 'center',
  alignSelf: 'center',
  display: 'flex',
}));

const Drawer = styled(MuiDrawer, {
  shouldForwardProp: (prop) => prop !== 'open',
})(({ theme, open }) => ({
  '& .MuiPaper-root': {
    backgroundColor: theme.palette.background.default,
  },
  boxSizing: 'border-box',
  flexShrink: 0,
  whiteSpace: 'nowrap',
  width: drawerWidth,
  ...(open && {
    ...openedMixin(theme),
    '& .MuiDrawer-paper': openedMixin(theme),
  }),
  ...(!open && {
    ...closedMixin(theme),
    '& .MuiDrawer-paper': closedMixin(theme),
  }),
}));

export interface Props {
  navigationData?: Array<Page>;
}

export default ({ navigationData }: Props): JSX.Element => {
  const classes = useStyles();
  const [open, setOpen] = React.useState(false);

  const toggleNavigation = (): void => {
    setOpen(!open);
  };

  return (
    <Box sx={{ display: 'flex' }}>
      <Drawer open={open} variant="permanent">
        <DrawerHeader>
          <Logo
            customClass={classes.logo}
            isDrawerOpen={open}
            onClick={toggleNavigation}
          />
        </DrawerHeader>
        <Divider />
        <NavigationMenu isDrawerOpen={open} navigationData={navigationData} />
      </Drawer>
    </Box>
  );
};
