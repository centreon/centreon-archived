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
import { CreateCSSProperties } from '@mui/styles';

import { useMemoComponent } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { isDarkMode } from '../../../Header';
import { searchUrlFromEntry } from '../helpers/getUrlFromEntry';
import { Page } from '../../models';
import {
  hoveredNavigationItemsAtom,
  selectedNavigationItemsAtom,
} from '../sideBarAtoms';

import ArrowIcon from './ArrowIcon';

const rootHeightItem = 37;

interface Props {
  data: Page;
  hover: boolean;
  icon?: ReactNode;
  isDoubleClickedFromRoot?: boolean;
  isDrawerOpen?: boolean;
  isItemClicked?: () => void;
  isOpen: boolean;
  isRoot?: boolean;
  onClick?: MouseEventHandler<HTMLAnchorElement>;
  onLeaveMenuItem?: () => void;
  onMouseEnter: (e: MouseEvent<HTMLElement>) => void;
}

const useStyles = makeStyles((theme) => ({
  activated: ({ isRoot }): CreateCSSProperties => ({
    '& .MuiListItemText-root': {
      '& .MuiTypography-root': {
        color:
          isDarkMode(theme) && isRoot
            ? theme.palette.primary.main
            : theme.palette.common.white,
      },
    },
    '& .MuiSvgIcon-root': {
      color:
        isDarkMode(theme) && isRoot
          ? theme.palette.primary.main
          : theme.palette.common.white,
    },
    '&:hover': {
      backgroundColor:
        isDarkMode(theme) && isRoot
          ? theme.palette.common.black
          : theme.palette.primary.dark,
      color: theme.palette.common.white,
    },

    backgroundColor:
      isDarkMode(theme) && isRoot
        ? theme.palette.common.black
        : theme.palette.primary.dark,
  }),
  containerIcon: {
    alignItems: 'center',
    color: theme.palette.common.white,
    minWidth: theme.spacing(5.75),
  },
  icon: {
    color: theme.palette.common.white,
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
    color: theme.palette.common.white,
    margin: theme.spacing(0),
  },
}));

const MenuItems = ({
  onMouseEnter,
  onClick,
  onLeaveMenuItem,
  isItemClicked,
  isOpen,
  icon,
  hover,
  data,
  isDrawerOpen,
  isRoot,
  isDoubleClickedFromRoot,
}: Props): JSX.Element => {
  const classes = useStyles({ isRoot });
  const user = useAtomValue(userAtom);
  const hoveredNavigationItems = useAtomValue(hoveredNavigationItemsAtom);
  const selectedNavigationItems = useAtomValue(selectedNavigationItemsAtom);

  const canNavigate =
    !Array.isArray(data?.groups) || equals(data?.groups.length, 0);

  const memoizedUrl = useMemo(() => searchUrlFromEntry(data) as string, [data]);

  const ItemLink = forwardRef<HTMLAnchorElement, Omit<RouterLinkProps, 'to'>>(
    (props, ref) => <RouterLink ref={ref} to={memoizedUrl} {...props} />,
  );

  const handleClickItem = (e: MouseEvent<HTMLAnchorElement>): void => {
    if (!isRoot && canNavigate) {
      isItemClicked?.();

      return;
    }

    e.preventDefault();
  };

  return useMemoComponent({
    Component: (
      <ListItemButton
        disableTouchRipple
        className={clsx(classes.listButton, {
          [classes.activated]: hover,
        })}
        component={ItemLink}
        sx={!isRoot ? { pl: 0 } : { pl: 1.2 }}
        onClick={handleClickItem}
        onDoubleClick={isRoot ? onClick : undefined}
        onMouseEnter={!isDoubleClickedFromRoot ? onMouseEnter : undefined}
        onMouseLeave={onLeaveMenuItem}
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
      isDoubleClickedFromRoot,
      user,
      hoveredNavigationItems,
      selectedNavigationItems,
    ],
  });
};

export default MenuItems;
