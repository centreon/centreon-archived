import React, { useState } from 'react';

import { equals, isNil } from 'ramda';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAtom } from 'jotai';

import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';

import { Page } from '../../models';
import {
  selectedNavigationItemsAtom,
  hoveredNavigationItemsAtom,
} from '../sideBarAtoms';
import { closedDrawerWidth, openedDrawerWidth } from '../index';

import CollapsableItems, { collapseBorderWidth } from './CollapsableItems';
import MenuItems from './MenuItems';
import icons from './icons';

interface Props {
  isDrawerOpen: boolean;
  navigationData?: Array<Page>;
}

const useStyles = makeStyles((theme) => ({
  icon: {
    color: theme.palette.text.primary,
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
  const [selectedNavigationItems, setSelectedNavigationItems] = useAtom(
    selectedNavigationItemsAtom,
  );
  const [hoveredNavigationItems, setHoveredNavigationItems] = useAtom(
    hoveredNavigationItemsAtom,
  );

  const selectedNavigationItemsByDefault = React.useRef<Array<Page> | null>(
    null,
  );
  const levelName = 'level_0_Navigated';
  const currentWidth = isDrawerOpen ? openedDrawerWidth / 8 : closedDrawerWidth;

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

  const hoverItem = (
    e: React.MouseEvent<HTMLElement>,
    index: number | null,
    item: Page,
  ): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const { top } = rect;
    setCurrentTop(top - collapseBorderWidth);
    setHoveredIndex(index);
    setHoveredNavigationItems({
      ...hoveredNavigationItems,
      level_0: item,
    });
  };

  const handleLeave = (): void => {
    setHoveredIndex(null);
    setHoveredNavigationItems(null);
  };

  const getUrlFromEntry = (entryProps: Page): string | null | undefined => {
    const page = isNil(entryProps?.page) ? '' : entryProps.page;
    const options = isNil(entryProps?.options) ? '' : entryProps.options;

    const urlOptions = `${page}${options}`;
    const url = entryProps.is_react
      ? entryProps.url
      : `/main.php?p=${urlOptions}`;

    return url;
  };

  const handleClickItem = (currentPage: Page): void => {
    if (!hoveredNavigationItems) {
      return;
    }
    setSelectedNavigationItems(hoveredNavigationItems);
    navigate(getUrlFromEntry(currentPage) as string);
  };

  const isItemHovered = (
    navigationItem: Record<string, Page> | null,
    level: string,
    item: Page,
  ): boolean => {
    if (!navigationItem || !navigationItem[level]) {
      return false;
    }

    return (
      equals(navigationItem[level].label, item.label) &&
      equals(navigationItem[level]?.url, item?.url)
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
          [`level_${currentIndex}_Navigated`]: currentItem,
        };
      },
      {},
    );

    setSelectedNavigationItems(selectedNavigationItemsToAdd);
  };

  const searchItemsWithReactUrl = (
    parentItem: Page,
    ...args: Array<Page>
  ): void => {
    if (!equals(pathname, parentItem?.url)) {
      return;
    }
    selectedNavigationItemsByDefault.current = [parentItem, ...args].reverse();
  };

  const searchItemsWithPhpUrl = (
    parentItem: Page,
    ...args: Array<Page>
  ): void => {
    const page = search?.match(/\d+/);
    if (!page || !equals(page[0], parentItem?.page)) {
      return;
    }
    selectedNavigationItemsByDefault.current = [parentItem, ...args].reverse();
  };

  const searchItemsHoveredByDefault = (currentPage, ...args): void => {
    const childPage = currentPage?.children;
    if (isNil(childPage) || !isArrayItem(childPage)) {
      if (!currentPage.is_react) {
        searchItemsWithPhpUrl(currentPage, ...args);

        return;
      }
      searchItemsWithReactUrl(currentPage, ...args);

      return;
    }
    childPage.forEach((item) => {
      const grandsonPage = item?.groups;
      if (isNil(grandsonPage) || !isArrayItem(grandsonPage)) {
        if (args.length > 0) {
          searchItemsHoveredByDefault(item, ...args);

          return;
        }
        searchItemsHoveredByDefault(item, currentPage);

        return;
      }
      grandsonPage.forEach((element) => {
        if (args.length > 0) {
          searchItemsHoveredByDefault(element, item, ...args);

          return;
        }
        searchItemsHoveredByDefault(element, item, currentPage);
      });
    });
  };

  const handleWindowClose = (): void => {
    setSelectedNavigationItems(null);
    setHoveredNavigationItems(null);
  };

  React.useEffect(() => {
    window.addEventListener('beforeunload', handleWindowClose);

    return () => window.removeEventListener('beforeunload', handleWindowClose);
  }, []);

  React.useEffect(() => {
    if (
      !selectedNavigationItemsByDefault ||
      !selectedNavigationItemsByDefault.current
    ) {
      return;
    }
    addSelectedNavigationItemsByDefault(
      selectedNavigationItemsByDefault.current,
    );
  }, [search]);

  return useMemoComponent({
    Component: (
      <List className={classes.list} onMouseLeave={handleLeave}>
        {navigationData?.map((item, index) => {
          searchItemsHoveredByDefault(item);

          const MenuIcon = !isNil(item?.icon) && icons[item.icon];
          const hover =
            isItemHovered(selectedNavigationItems, levelName, item) ||
            equals(hoveredIndex, index);

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
                onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                  hoverItem(e, index, item)
                }
              />

              {Array.isArray(item?.children) &&
                equals(hoveredIndex, index) &&
                item.children.length > 0 && (
                  <CollapsableItems
                    {...props}
                    data={item.children}
                    isCollapsed={index === hoveredIndex}
                    onClick={handleClickItem}
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
    ],
  });
};

export default NavigationMenu;
