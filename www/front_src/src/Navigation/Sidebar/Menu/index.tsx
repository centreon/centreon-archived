import React, { useState } from 'react';

import { equals, isNil } from 'ramda';
import { useNavigate } from 'react-router-dom';
import { useAtom } from 'jotai';

import List from '@mui/material/List';
import ListItem from '@mui/material/ListItem';
import makeStyles from '@mui/styles/makeStyles';

import { Page } from '../../models';
import {
  navigationItemSelectedAtom,
  propsNavigationItemSelected,
} from '../sideBarAtoms';
import { openedDrawerWidth } from '../index';

import MenuItems from './MenuItems';
import icons from './icons';
import CollapsItem from './CollapsItem';

interface Props {
  isDrawerOpen: boolean;
  navigationData?: Array<Page>;
}

const useStyles = makeStyles((theme) => ({
  icon: {
    color: theme.palette.text.primary,
    fontSize: 28,
  },
  listRoot: {
    padding: theme.spacing(0.25, 0, 0, 0),
  },
}));

const NavigationMenu = ({
  isDrawerOpen,
  navigationData,
}: Props): JSX.Element => {
  const classes = useStyles();
  const navigate = useNavigate();

  const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);
  const [currentTop, setCurrentTop] = useState<number>();
  const [maxHeightCollapsScroll, setMaxHeightCollapsScroll] = useState<
    number | undefined
  >(undefined);
  const [navigationItemSelected, setNavigationItemSelected] = useAtom(
    navigationItemSelectedAtom,
  );
  const levelName = 'level_0_Navigated';
  const currentWidth = isDrawerOpen ? openedDrawerWidth / 8 : 8;

  const props = {
    currentTop,
    currentWidth,
    hoveredIndex,
    isDrawerOpen,
    level: 1,
    maxHeightCollapsScroll,
    setMaxHeightCollapsScroll,
  };

  const handleHover = (
    e: React.MouseEvent<HTMLElement>,
    index: number | null,
    item: Page,
  ): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const { top } = rect;
    setCurrentTop(top);
    setHoveredIndex(index);
    setNavigationItemSelected({
      ...navigationItemSelected,
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

  const handlClickItem = (item: Page): void => {
    if (!isNil(getUrlFromEntry(item))) {
      navigate(getUrlFromEntry(item) as string);
    }

    if (navigationItemSelected) {
      Object.keys(navigationItemSelected).forEach((i: string) => {
        if (i.includes('_Navigated')) {
          delete navigationItemSelected[i];
        } else {
          navigationItemSelected[`${i}_Navigated`] = navigationItemSelected[i];
          delete navigationItemSelected[i];
        }
      });
    }

    setNavigationItemSelected(navigationItemSelected);
  };

  const isHover = (
    object: Record<string, propsNavigationItemSelected> | null,
    level: string,
    index: number,
  ): boolean => {
    if (object && object[level]) {
      return object[level].index === index;
    }

    return false;
  };

  return (
    <List onMouseLeave={handleLeave}>
      {navigationData?.map((item, index) => {
        const MenuIcon = !isNil(item?.icon) && icons[item.icon];
        const hover =
          isHover(navigationItemSelected, levelName, index) ||
          equals(hoveredIndex, index);

        return (
          <ListItem
            disablePadding
            className={classes.listRoot}
            key={item.label}
          >
            <MenuItems
              isRoot
              data={item}
              hover={hover}
              icon={<MenuIcon className={classes.icon} />}
              isDrawerOpen={isDrawerOpen}
              isOpen={index === hoveredIndex}
              onClick={(): void => handlClickItem(item)}
              onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                handleHover(e, index, item)
              }
            />
            {Array.isArray(item?.children) && item.children.length > 0 && (
              <CollapsItem
                {...props}
                data={item.children}
                isCollapsed={index === hoveredIndex}
                onClick={handlClickItem}
              />
            )}
          </ListItem>
        );
      })}
    </List>
  );
};

export default NavigationMenu;
