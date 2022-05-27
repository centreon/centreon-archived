import { MouseEvent, useEffect, useRef, useState } from 'react';

import { equals, flatten, isEmpty, isNil, length } from 'ramda';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAtom } from 'jotai';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';
import { userAtom } from '@centreon/ui-context';

import { Page } from '../../models';
import {
  selectedNavigationItemsAtom,
  hoveredNavigationItemsAtom,
  setHoveredNavigationItemsDerivedAtom,
} from '../sideBarAtoms';
import { closedDrawerWidth, openedDrawerWidth } from '../index';
import { searchUrlFromEntry } from '../helpers/getUrlFromEntry';

import CollapsibleItems from './CollapsibleItems';
import MenuItems from './MenuItems';
import icons from './icons';

interface Props {
  isDrawerOpen: boolean;
  navigationData?: Array<Page>;
}

const useStyles = makeStyles((theme) => ({
  icon: {
    fontSize: 26,
  },
  list: {
    '&.MuiList-root': {
      padding: theme.spacing(0, 0, 0, 0),
    },
  },
}));

const NavigationMenu = ({
  isDrawerOpen,
  navigationData,
}: Props): JSX.Element => {
  const classes = useStyles();
  const navigate = useNavigate();
  const { pathname, search } = useLocation();

  const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);
  const [currentTop, setCurrentTop] = useState<number>();
  const [collapseScrollMaxHeight, setCollapseScrollMaxHeight] = useState<
    number | undefined
  >(undefined);
  const [collapseScrollMaxWidth, setCollapseScrollMaxWidth] = useState<
    number | undefined
  >(undefined);
  const timeoutRef = useRef<null | NodeJS.Timeout>(null);
  const [selectedNavigationItems, setSelectedNavigationItems] = useAtom(
    selectedNavigationItemsAtom,
  );
  const [hoveredNavigationItems, setHoveredNavigationItems] = useAtom(
    hoveredNavigationItemsAtom,
  );
  const user = useAtomValue(userAtom);

  const setHoveredNavigationItemsDerived = useUpdateAtom(
    setHoveredNavigationItemsDerivedAtom,
  );

  const levelName = 'level_0';
  const currentWidth = isDrawerOpen ? openedDrawerWidth / 8 : closedDrawerWidth;

  const hoverItem = ({ e, index, currentPage }): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const { top } = rect;
    setCurrentTop(top);
    setHoveredIndex(index);
    setHoveredNavigationItemsDerived({ currentPage, levelName });
    discardTimeout();
  };

  const discardTimeout = (): void => {
    if (isNil(timeoutRef.current)) {
      return;
    }
    clearTimeout(timeoutRef.current);
  };

  const handleLeave = (): void => {
    discardTimeout();
    timeoutRef.current = setTimeout((): void => {
      setHoveredIndex(null);
      setHoveredNavigationItems(null);
    }, 500);
  };

  const handleClickItem = (currentPage: Page): void => {
    if (isNil(searchUrlFromEntry(currentPage))) {
      return;
    }
    navigate(searchUrlFromEntry(currentPage) as string);
  };

  const isItemHovered = ({ navigationItem, level, currentPage }): boolean => {
    if (!navigationItem || !navigationItem[level]) {
      return false;
    }

    return (
      equals(navigationItem[level].label, currentPage.label) &&
      equals(navigationItem[level]?.url, currentPage?.url)
    );
  };

  const isArrayItem = (item: unknown): boolean => {
    if (Array.isArray(item)) {
      return item.length > 0;
    }

    return false;
  };

  const addSelectedNavigationItemsByDefault = (items): void => {
    const selectedNavigationItemsToAdd = items.reduce(
      (previousItem, currentItem, currentIndex) => {
        return {
          ...previousItem,
          [`level_${currentIndex}`]: currentItem,
        };
      },
      {},
    );

    setSelectedNavigationItems(selectedNavigationItemsToAdd);
  };

  const searchItemsWithReactUrl = (
    parentItem: Page,
    ...args: Array<Page>
  ): Array<Page> | null => {
    if (!equals(pathname, parentItem?.url)) {
      return null;
    }

    return [parentItem, ...args].reverse();
  };

  const searchItemsWithPhpUrl = (
    parentItem: Page,
    ...args: Array<Page>
  ): Array<Page> | null => {
    const page = search?.match(/\d+/);
    if (!page || !equals(page[0], parentItem?.page)) {
      return null;
    }

    return [parentItem, ...args].reverse();
  };

  const getItemHoveredByDefault = (
    currentPage,
    ...args
  ): Array<Page> | null => {
    if (!currentPage.is_react && searchItemsWithPhpUrl(currentPage, ...args)) {
      return searchItemsWithPhpUrl(currentPage, ...args);
    }

    if (currentPage.is_react && searchItemsWithReactUrl(currentPage, ...args)) {
      return searchItemsWithReactUrl(currentPage, ...args);
    }

    return null;
  };

  const searchItemsHoveredByDefault = (
    currentPage,
    ...args
  ): Array<Page> | null => {
    const hoveredCurrentPage = getItemHoveredByDefault(currentPage, ...args);
    if (hoveredCurrentPage) {
      return hoveredCurrentPage;
    }

    const childPage = currentPage?.children;
    if (isNil(childPage) || !isArrayItem(childPage)) {
      return getItemHoveredByDefault(currentPage, ...args);
    }

    return childPage?.map((item) => {
      const hoveredItem = getItemHoveredByDefault(item, ...args);
      if (hoveredItem && equals(length(hoveredItem as Array<Page>), 1)) {
        return [currentPage, ...hoveredItem];
      }

      const grandsonPage = item?.groups;
      if (isNil(grandsonPage) || !isArrayItem(grandsonPage)) {
        if (args.length > 0) {
          return searchItemsHoveredByDefault(item, ...args);
        }

        return searchItemsHoveredByDefault(item, currentPage);
      }

      return grandsonPage.map((element) => {
        if (args.length > 0) {
          return searchItemsHoveredByDefault(element, item, ...args);
        }

        return searchItemsHoveredByDefault(element, item, currentPage);
      });
    });
  };

  useEffect(() => {
    navigationData?.forEach((item) => {
      const searchedItems = searchItemsHoveredByDefault(item);
      const filteredResult = flatten(searchedItems || []).filter(Boolean);

      if (isEmpty(filteredResult)) {
        return;
      }

      addSelectedNavigationItemsByDefault(filteredResult);
    });
  }, []);

  useEffect(() => {
    navigationData?.forEach((item) => {
      const searchedItems = searchItemsHoveredByDefault(item);
      const filteredResult = flatten(searchedItems || []).filter(Boolean);

      if (isEmpty(filteredResult)) {
        return;
      }

      addSelectedNavigationItemsByDefault(filteredResult);
    });
  }, [pathname, search]);

  const props = {
    collapseScrollMaxHeight,
    collapseScrollMaxWidth,
    currentTop,
    currentWidth,
    hoveredIndex,
    isDrawerOpen,
    level: 1,
    pathname,
    search,
    setCollapseScrollMaxHeight,
    setCollapseScrollMaxWidth,
  };

  return useMemoComponent({
    Component: (
      <List
        className={classes.list}
        onMouseEnter={discardTimeout}
        onMouseLeave={handleLeave}
      >
        {navigationData?.map((item, index) => {
          const MenuIcon = !isNil(item?.icon) && icons[item.icon];
          const hover =
            isItemHovered({
              currentPage: item,
              level: levelName,
              navigationItem: selectedNavigationItems,
            }) || equals(hoveredIndex, index);

          return (
            <ListItem disablePadding key={item.label}>
              <MenuItems
                isRoot
                data={item}
                hover={hover}
                icon={<MenuIcon className={classes.icon} />}
                isDrawerOpen={isDrawerOpen}
                isOpen={index === hoveredIndex}
                onClick={(): void => handleClickItem(item)}
                onMouseEnter={(e: MouseEvent<HTMLElement>): void =>
                  hoverItem({ currentPage: item, e, index })
                }
              />

              {Array.isArray(item?.children) &&
                equals(hoveredIndex, index) &&
                item.children.length > 0 && (
                  <CollapsibleItems
                    {...props}
                    data={item.children}
                    isCollapsed={index === hoveredIndex}
                    onLeave={handleLeave}
                  />
                )}
            </ListItem>
          );
        })}
      </List>
    ),
    memoProps: [
      isDrawerOpen,
      hoveredIndex,
      collapseScrollMaxHeight,
      collapseScrollMaxWidth,
      selectedNavigationItems,
      user,
      hoveredNavigationItems,
    ],
  });
};

export default NavigationMenu;
