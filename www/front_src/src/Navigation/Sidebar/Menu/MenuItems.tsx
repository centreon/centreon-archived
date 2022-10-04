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
  activated: (): CreateCSSProperties => ({
    '& .MuiListItemText-root': {
      '& .MuiTypography-root': {
        color: 'inherit',
      },
    },
    '& .MuiSvgIcon-root': {
      color: isDarkMode(theme)
        ? theme.palette.common.white
        : theme.palette.primary.main,
    },
    '&:hover': {
      backgroundColor: isDarkMode(theme)
        ? theme.palette.primary.dark
        : theme.palette.primary.light,
    },
    backgroundColor: isDarkMode(theme)
      ? theme.palette.primary.dark
      : theme.palette.primary.light,
    color: isDarkMode(theme)
      ? theme.palette.common.white
      : theme.palette.primary.main,
  }),
  arrowIcon: {
    color: 'inherit',
  },
  iconButton: {
    alignItems: 'center',
    color: theme.palette.common.white,
    height: theme.spacing(rootHeightItem / 8),
  },
  iconWrapper: {
    alignItems: 'center',
    color: 'inherit',
    minWidth: theme.spacing(5.75),
  },
  label: {
    '& .MuiTypography-root': {
      color: 'inherit',
      lineHeight: 1,
    },
    color: theme.palette.text.primary,
    margin: theme.spacing(0),
  },
  rootLabel: {
    color: 'inherit',
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
        className={clsx(classes.iconButton, {
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
            <ListItemIcon className={classes.iconWrapper}>
              {icon}
              {isDrawerOpen &&
                Array.isArray(data?.children) &&
                data.children.length > 0 && (
                  <ArrowIcon
                    className={classes.arrowIcon}
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
