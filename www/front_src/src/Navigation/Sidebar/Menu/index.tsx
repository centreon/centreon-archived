import * as React from 'react';

import { equals, flatten, isEmpty, isNil } from 'ramda';
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

  const [hoveredIndex, setHoveredIndex] = React.useState<number | null>(null);
  const [currentTop, setCurrentTop] = React.useState<number>();
  const [collapseScrollMaxHeight, setCollapseScrollMaxHeight] = React.useState<
    number | undefined
  >(undefined);
  const [collapseScrollMaxWidth, setCollapseScrollMaxWidth] = React.useState<
    number | undefined
  >(undefined);
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

  const hoverItem = ({ e, index, currentPage }): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const { top } = rect;
    setCurrentTop(top);
    setHoveredIndex(index);
    setHoveredNavigationItemsDerived({ currentPage, levelName });
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
    if (isNil(getUrlFromEntry(currentPage))) {
      return;
    }
    navigate(getUrlFromEntry(currentPage) as string);
    setSelectedNavigationItems(hoveredNavigationItems);
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

  const searchItemsHoveredByDefault = (
    currentPage,
    ...args
  ): Array<Page> | null => {
    const childPage = currentPage?.children;
    if (isNil(childPage) || !isArrayItem(childPage)) {
      if (
        !currentPage.is_react &&
        searchItemsWithPhpUrl(currentPage, ...args)
      ) {
        return searchItemsWithPhpUrl(currentPage, ...args);
      }

      if (
        currentPage.is_react &&
        searchItemsWithReactUrl(currentPage, ...args)
      ) {
        return searchItemsWithReactUrl(currentPage, ...args);
      }
    }

    return childPage?.map((item) => {
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

  const handleWindowClose = (): void => {
    setSelectedNavigationItems(null);
    setHoveredNavigationItems(null);
  };

  React.useEffect(() => {
    navigationData?.forEach((item) => {
      const searchedItems = searchItemsHoveredByDefault(item);
      const filteredResult = flatten(searchedItems || []).filter(Boolean);

      if (isEmpty(filteredResult)) {
        return;
      }

      addSelectedNavigationItemsByDefault(filteredResult);
    });
    window.addEventListener('beforeunload', handleWindowClose);

    return () => window.removeEventListener('beforeunload', handleWindowClose);
  }, []);

  return useMemoComponent({
    Component: (
      <List className={classes.list} onMouseLeave={handleLeave}>
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
                onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
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
      user,
      hoveredNavigationItems,
    ],
  });
};

export default NavigationMenu;
