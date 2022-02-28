import React, { useState } from 'react';

import { equals, isNil } from 'ramda';
import { useNavigate } from 'react-router-dom';
import { useAtom } from 'jotai';

import List from '@mui/material/List';
import makeStyles from '@mui/styles/makeStyles';

import { Page } from '../../models';
import { itemSelectedAtom, propsItemSelected } from '../sideBarAtoms';

import ListButton from './ListButton';
import icons from './icons';
import MinCollaps from './MinCollaps';

interface Props {
  isDrawerOpen: boolean;
  navigationData?: Array<Page>;
}

const useStyles = makeStyles((theme) => ({
  icon: {
    color: theme.palette.text.primary,
  },
  root: {
    paddingLeft: theme.spacing(0.4),
    paddingRight: theme.spacing(0.4),
  },
}));

const NavigationMenu = ({
  isDrawerOpen,
  navigationData,
}: Props): JSX.Element => {
  const classes = useStyles();
  const navigate = useNavigate();

  const [selectedIndex, setSelectedIndex] = useState<number | null>(null);
  const [currentTop, setCurrentTop] = useState<number>();
  const [currentWidth, setCurrentWidth] = useState(0);

  const [itemSelectedNav, setItemSelectedNav] = useAtom(itemSelectedAtom);
  const levelName = 'level_0_Navigated';

  const props = {
    currentTop,
    currentWidth,
    isDrawerOpen,
    level: 1,
    selectedIndex,
  };

  const handleHover = (
    e: React.MouseEvent<HTMLElement>,
    index: number | null,
    item: Page,
  ): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const top = Math.floor(rect.bottom) - Math.floor(rect.height);
    const width = Math.floor(rect.right) - Math.floor(rect.left);
    setCurrentTop(top);
    setCurrentWidth(width / 8);
    setSelectedIndex(index);
    setItemSelectedNav({
      ...itemSelectedNav,
      level_0: { index, label: item.label, url: item?.url },
    });
  };

  const handleLeave = (): void => {
    setSelectedIndex(null);
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

    if (itemSelectedNav) {
      Object.keys(itemSelectedNav).forEach((i: string) => {
        if (i.includes('_Navigated')) {
          delete itemSelectedNav[i];
        } else {
          itemSelectedNav[`${i}_Navigated`] = itemSelectedNav[i];
          delete itemSelectedNav[i];
        }
      });
    }

    setItemSelectedNav(itemSelectedNav);
  };

  const isHover = (
    object: Record<string, propsItemSelected> | null,
    level: string,
    index: number,
  ): boolean => {
    if (object && object[level]) {
      return object[level].index === index;
    }

    return false;
  };

  return (
    <List className={classes.root} onMouseLeave={handleLeave}>
      {navigationData?.map((item, index) => {
        const MenuIcon = !isNil(item?.icon) && icons[item.icon];
        const hover =
          isHover(itemSelectedNav, levelName, index) ||
          equals(selectedIndex, index);

        return (
          <List key={item.label}>
            <ListButton
              isRoot
              data={item}
              hover={hover}
              icon={<MenuIcon className={classes.icon} sx={{ fontSize: 30 }} />}
              isDrawerOpen={isDrawerOpen}
              isOpen={index === selectedIndex}
              onClick={(): void => handlClickItem(item)}
              onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                handleHover(e, index, item)
              }
            />
            {Array.isArray(item?.children) && item.children.length > 0 && (
              <MinCollaps
                {...props}
                data={item.children}
                isCollapsed={index === selectedIndex}
                onClick={handlClickItem}
              />
            )}
          </List>
        );
      })}
    </List>
  );
};

export default NavigationMenu;
