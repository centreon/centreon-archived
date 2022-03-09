import React from 'react';

import Box from '@mui/material/Box';
import Divider from '@mui/material/Divider';
import MuiDrawer from '@mui/material/Drawer';
import { CSSObject, styled, Theme } from '@mui/material/styles';

import { Page } from '../models';

import Logo from './Logo';
import MiniLogo from './Logo/LogoMini';
import NavigationMenu from './Menu';

export const openedDrawerWidth = 165;
export const closedDrawerWidth = 6.6;

const openedMixin = (theme: Theme): CSSObject => ({
  overflowX: 'hidden',
  transition: theme.transitions.create('width', {
    duration: theme.transitions.duration.enteringScreen,
    easing: theme.transitions.easing.sharp,
  }),
  width: openedDrawerWidth,
});

const closedMixin = (theme: Theme): CSSObject => ({
  overflowX: 'hidden',
  transition: theme.transitions.create('width', {
    duration: theme.transitions.duration.leavingScreen,
    easing: theme.transitions.easing.sharp,
  }),
  width: theme.spacing(closedDrawerWidth),
});

const DrawerHeader = styled('div')(({ theme }) => ({
  '&:hover': {
    cursor: 'pointer',
  },
  alignItems: 'flex-end',
  alignSelf: 'center',
  display: 'flex',
  height: theme.spacing(6.9),
  paddingRight: theme.spacing(0.65),
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
  width: theme.spacing(openedDrawerWidth / 8),
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
  const [open, setOpen] = React.useState(false);

  const toggleNavigation = (): void => {
    setOpen(!open);
  };

  return (
    <Box sx={{ display: 'flex' }}>
      <Drawer open={open} variant="permanent">
        <DrawerHeader>
          {open ? (
            <Logo onClick={toggleNavigation} />
          ) : (
            <MiniLogo onClick={toggleNavigation} />
          )}
        </DrawerHeader>
        <Divider />
        <NavigationMenu isDrawerOpen={open} navigationData={navigationData} />
      </Drawer>
    </Box>
  );
};
