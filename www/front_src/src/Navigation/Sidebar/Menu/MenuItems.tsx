import {
  forwardRef,
  MouseEvent,
  MouseEventHandler,
  ReactNode,
  useMemo,
} from 'react';

import clsx from 'clsx';
import { useAtomValue } from 'jotai/utils';
import {
  Link as RouterLink,
  LinkProps as RouterLinkProps,
} from 'react-router-dom';
import { equals } from 'ramda';

import ListItemButton from '@mui/material/ListItemButton';
import ListItemIcon from '@mui/material/ListItemIcon';
import ListItemText from '@mui/material/ListItemText';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { Page } from '../../models';
import {
  hoveredNavigationItemsAtom,
  selectedNavigationItemsAtom,
} from '../sideBarAtoms';

import ArrowIcon from './ArrowIcon';

const rootHeightItem = 37;

interface Props {
  data: Page;
  getUrlFromEntry: (item: Page) => string | null | undefined;
  hover: boolean;
  icon?: ReactNode;
  isDrawerOpen?: boolean;
  isOpen: boolean;
  isRoot?: boolean;
  onClick?: MouseEventHandler<HTMLAnchorElement>;
  onMouseEnter: (e: MouseEvent<HTMLElement>) => void;
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
    color: theme.palette.text.primary,
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
    height: theme.spacing(rootHeightItem / 8),
    marginBottom: 0.8,
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
  getUrlFromEntry,
}: Props): JSX.Element => {
  const classes = useStyles({ isRoot });
  const user = useAtomValue(userAtom);
  const hoveredNavigationItems = useAtomValue(hoveredNavigationItemsAtom);
  const selectedNavigationItems = useAtomValue(selectedNavigationItemsAtom);

  const cannotNavigate =
    !Array.isArray(data?.groups) || equals(data?.groups.length, 0);

  const LinkBehavior = forwardRef<
    HTMLAnchorElement,
    Omit<RouterLinkProps, 'to'>
  >((props, ref) => (
    <RouterLink
      ref={ref}
      to={useMemo(() => getUrlFromEntry(data) as string, [data])}
      {...props}
      role={undefined}
    />
  ));

  return useMemoComponent({
    Component: (
      <ListItemButton
        className={clsx(classes.listButton, {
          [classes.activated]: hover,
        })}
        component={!isRoot && cannotNavigate ? LinkBehavior : 'div'}
        sx={!isRoot ? { pl: 0 } : { pl: 1.2 }}
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
    ),
    memoProps: [
      hover,
      isOpen,
      isRoot,
      isDrawerOpen,
      user,
      hoveredNavigationItems,
      selectedNavigationItems,
    ],
  });
};

export default MenuItems;
