import React, { useState } from 'react';

import { equals, isNil, clone, gt } from 'ramda';
import { useNavigate, useLocation } from 'react-router-dom';
import { useAtom } from 'jotai';

import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';

import { Page } from '../../models';
import {
  itemsHoveredByDefaultAtom,
  selectedNavigationItemsAtom,
  propsSelectedNavigationItems,
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
  const [itemsHoveredByDefault, setItemsHoveredByDefault] = useAtom(
    itemsHoveredByDefaultAtom,
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
    setSelectedNavigationItems({
      ...selectedNavigationItems,
      level_0: { index, label: item.label, url: item?.url },
    });
  };

  const handleLeave = (): void => {
    setHoveredIndex(null);
  };

  const getUrlFromEntry = (entryProps: Page): string | null | undefined => {
    const page = !isNil(entryProps?.page) ? entryProps.page : '';
    const options = !isNil(entryProps?.options) ? entryProps.options : '';

    const urlOptions = `${page}${options}`;
    const url = entryProps.is_react
      ? entryProps.url
      : `/main.php?p=${urlOptions}`;

    return url;
  };

  const addSelectedNavigationItems = (
    navigationItems: Record<string, propsSelectedNavigationItems>,
    level: number,
  ): Record<string, propsSelectedNavigationItems> => {
    const updatedNavigationItems = clone(navigationItems);

    if (updatedNavigationItems) {
      Object.keys(updatedNavigationItems).forEach((i: string) => {
        const levelToRemove = i?.match(/\d+/);

        if (levelToRemove && gt(Number(levelToRemove[0]), level)) {
          delete updatedNavigationItems[i];
        }

        if (i.includes('_Navigated')) {
          if (`level_${level}_Navigated` === i) {
            updatedNavigationItems[i] =
              updatedNavigationItems[`level_${level}`];
          }
        } else {
          updatedNavigationItems[`${i}_Navigated`] = updatedNavigationItems[i];
          delete updatedNavigationItems[i];
        }
      });
    }

    return updatedNavigationItems;
  };

  const handleClickItem = (currentPage: Page, level = 0): void => {
    setItemsHoveredByDefault(null);
    if (!isNil(getUrlFromEntry(currentPage))) {
      navigate(getUrlFromEntry(currentPage) as string);
    }

    if (selectedNavigationItems) {
      const isAlreadyClicked =
        equals(
          selectedNavigationItems[`level_${level}_Navigated`]?.url,
          currentPage?.url,
        ) &&
        equals(
          selectedNavigationItems[`level_${level}_Navigated`]?.label,
          currentPage.label,
        );

      if (isAlreadyClicked) {
        return;
      }
      setSelectedNavigationItems(
        addSelectedNavigationItems(selectedNavigationItems, level),
      );
    }
  };

  const isItemHovered = (
    object: Record<string, propsSelectedNavigationItems> | null,
    level: string,
    index: number,
  ): boolean => {
    if (object && object[level]) {
      return object[level].index === index;
    }

    return false;
  };

  const sendRootItemHoveredByDefault = (item: Page): Page => {
    return item;
  };

  const isItemHoveredByDefault = (item: Page): boolean => {
    if (itemsHoveredByDefault) {
      return (
        equals(
          item.label,
          itemsHoveredByDefault?.rootItemHoveredByDefault?.label,
        ) &&
        equals(item?.url, itemsHoveredByDefault.rootItemHoveredByDefault?.url)
      );
    }

    return false;
  };

  const handleWindowClose = (): void => {
    setSelectedNavigationItems(null);
  };

  React.useEffect(() => {
    window.addEventListener('beforeunload', handleWindowClose);

    return () => window.removeEventListener('beforeunload', handleWindowClose);
  }, []);

  return useMemoComponent({
    Component: (
      <List className={classes.list} onMouseLeave={handleLeave}>
        {navigationData?.map((item, index) => {
          const MenuIcon = !isNil(item?.icon) && icons[item.icon];
          const hover =
            isItemHovered(selectedNavigationItems, levelName, index) ||
            equals(hoveredIndex, index) ||
            isItemHoveredByDefault(item);

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

              {Array.isArray(item?.children) && item.children.length > 0 && (
                <CollapsableItems
                  {...props}
                  data={item.children}
                  getRootItemHoveredByDefault={(): Page | null =>
                    sendRootItemHoveredByDefault(item)
                  }
                  isCollapsed={index === hoveredIndex}
                  onClick={handleClickItem}
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
      itemsHoveredByDefault,
    ],
  });
};

export default NavigationMenu;
