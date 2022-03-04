import React, { useState } from 'react';

import { equals } from 'ramda';
import clsx from 'clsx';
import { useAtom } from 'jotai';

import Collapse from '@mui/material/Collapse';
import List from '@mui/material/List';
import makeStyles from '@mui/styles/makeStyles';
import ListSubheader from '@mui/material/ListSubheader';

import { Page } from '../../models';
import {
  navigationItemSelectedAtom,
  propsNavigationItemSelected,
} from '../sideBarAtoms';

import MenuItems from './MenuItems';

interface Props {
  currentTop?: number;
  currentWidth: number;
  data?: Array<Page>;
  isCollapsed: boolean;
  isSubHeader?: boolean;
  level: number;
  maxHeightCollapsScroll?: number;
  onClick: (item: Page) => void;
  setMaxHeightCollapsScroll: React.Dispatch<
    React.SetStateAction<number | undefined>
  >;
}

interface StyleProps {
  currentTop?: number;
  currentWidth: number;
  maxHeightCollapsScroll?: number;
}

const collapsWidth = 170;

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
  label: {
    fontWeight: 'bold',
  },
  root: {
    '& .MuiListItemIcon-root': {
      minWidth: theme.spacing(2.25),
      padding: theme.spacing(0, 0.5, 0, 1),
    },
    '& .MuiTypography-root': {
      color: theme.palette.text.primary,
      fontSize: theme.typography.caption,
    },
    border: `solid ${theme.palette.divider} 0.1px`,
  },
  subHeader: {
    color: theme.palette.text.secondary,
    fontSize: theme.typography.body2.fontSize,
    fontWeight: 'bold',
    lineHeight: theme.spacing(3),
    textAlign: 'center',
  },
  toggled: {
    '&::-webkit-scrollbar': {
      width: theme.spacing(1.5),
    },
    '&::-webkit-scrollbar-thumb': {
      backgroundColor: theme.palette.action.disabled,
    },
    '&::-webkit-scrollbar-track': {
      border: `solid ${theme.palette.action.hover} 0.5px`,
    },
    backgroundColor: theme.palette.background.default,
    left: ({ currentWidth }: StyleProps): string => theme.spacing(currentWidth),
    maxHeight: ({ maxHeightCollapsScroll }: StyleProps): string =>
      maxHeightCollapsScroll
        ? theme.spacing(maxHeightCollapsScroll)
        : theme.spacing(40),
    minWidth: collapsWidth,
    overflow: 'auto',
    position: 'fixed',
    top: ({ currentTop }: StyleProps): number | undefined => currentTop,
    zIndex: theme.zIndex.mobileStepper,
  },
}));

const CollapsableItems = ({
  data,
  isCollapsed,
  isSubHeader,
  currentTop,
  currentWidth,
  onClick,
  level,
  maxHeightCollapsScroll,
  setMaxHeightCollapsScroll,
}: Props): JSX.Element => {
  const classes = useStyles({
    currentTop,
    currentWidth,
    maxHeightCollapsScroll,
  });
  const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);
  const [topItem, setTopItem] = useState<number>();
  const [nestedMaxHeightCollaps, setNestedMaxHeightCollaps] = useState<
    undefined | number
  >(undefined);
  const [navigationItemSelected, setNavigationItemSelected] = useAtom(
    navigationItemSelectedAtom,
  );
  const levelName = `level_${level}_Navigated`;
  const widthItem = currentWidth + collapsWidth / 8 + 0.15;

  const hoverItem = (
    e: React.MouseEvent<HTMLElement>,
    index: number,
    item: Page,
  ): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const { top } = rect;
    setTopItem(top);
    setHoveredIndex(index);
    const levelLabel = `level_${level}`;

    setNavigationItemSelected({
      ...navigationItemSelected,
      [levelLabel]: { index, label: item.label, url: item?.url },
    });
  };

  const handleLeave = (): void => {
    setHoveredIndex(null);
    if (navigationItemSelected) {
      Object.keys(navigationItemSelected).forEach(() => {
        delete navigationItemSelected[`level_${level}`];
      });
    }
  };

  const isItemHovered = (
    object: Record<string, propsNavigationItemSelected> | null,
    levelTitle: string,
    index: number,
    item: Page,
  ): boolean => {
    if (object && object[levelTitle]) {
      return (
        object[levelTitle].index === index &&
        object[levelTitle].label === item.label &&
        object[levelTitle].url === item?.url
      );
    }

    return false;
  };

  const getNestedIndex = (
    itemIndex,
    childIndex: number,
    content: Array<Page>,
  ): number => {
    if (itemIndex > 1) {
      return (
        Number(content[0].children?.length) +
        Number(content[itemIndex - 1].children?.length) +
        childIndex
      );
    }
    if (itemIndex === 1) {
      return childIndex + Number(content[0].children?.length);
    }

    return childIndex;
  };

  const isArrayItem = (item: unknown): boolean => {
    if (Array.isArray(item)) {
      return item.length > 0;
    }

    return false;
  };

  const updateMaxHeightCollaps = (el: HTMLElement): void => {
    const rect = el.getBoundingClientRect();
    setMaxHeightCollapsScroll((window.innerHeight - rect.top) / 8);
  };

  return (
    <Collapse
      unmountOnExit
      addEndListener={(node): void => {
        updateMaxHeightCollaps(node);
      }}
      className={clsx(classes.root, classes.toggled)}
      in={isCollapsed}
      timeout={0}
      onMouseLeave={handleLeave}
    >
      {data?.map((item, index) => {
        const hover =
          isItemHovered(navigationItemSelected, levelName, index, item) ||
          equals(hoveredIndex, index);

        return (
          <List
            disablePadding
            key={item.label}
            subheader={
              isSubHeader && (
                <ListSubheader
                  disableGutters
                  disableSticky
                  className={classes.subHeader}
                >
                  {item.label}
                </ListSubheader>
              )
            }
          >
            {isSubHeader ? (
              isArrayItem(item?.children) &&
              item?.children?.map((content, ind) => {
                const nestedIndex = getNestedIndex(index, ind, data);
                const nestedHover =
                  isItemHovered(
                    navigationItemSelected,
                    levelName,
                    nestedIndex,
                    content,
                  ) || equals(hoveredIndex, nestedIndex);

                return (
                  <MenuItems
                    data={content}
                    hover={nestedHover}
                    isOpen={nestedIndex === hoveredIndex}
                    key={content.label}
                    onClick={
                      !isArrayItem(item?.groups)
                        ? (): void => onClick(content)
                        : undefined
                    }
                    onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                      hoverItem(e, nestedIndex, content)
                    }
                  />
                );
              })
            ) : (
              <MenuItems
                data={item}
                hover={hover}
                isOpen={index === hoveredIndex}
                onClick={
                  !isArrayItem(item?.groups)
                    ? (): void => onClick(item)
                    : undefined
                }
                onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                  hoverItem(e, index, item)
                }
              />
            )}

            {Array.isArray(item?.groups) && item.groups.length > 1 ? (
              <CollapsableItems
                isSubHeader
                currentTop={topItem}
                currentWidth={widthItem}
                data={item.groups}
                isCollapsed={index === hoveredIndex}
                level={level + 1}
                maxHeightCollapsScroll={nestedMaxHeightCollaps}
                setMaxHeightCollapsScroll={setNestedMaxHeightCollaps}
                onClick={onClick}
              />
            ) : (
              isArrayItem(item?.groups) &&
              item?.groups?.map(
                (itemGroup) =>
                  isArrayItem(itemGroup?.children) && (
                    <div key={itemGroup.label}>
                      <CollapsableItems
                        currentTop={topItem}
                        currentWidth={widthItem}
                        data={itemGroup.children}
                        isCollapsed={index === hoveredIndex}
                        level={level + 1}
                        maxHeightCollapsScroll={nestedMaxHeightCollaps}
                        setMaxHeightCollapsScroll={setNestedMaxHeightCollaps}
                        onClick={onClick}
                      />
                    </div>
                  ),
              )
            )}
          </List>
        );
      })}
    </Collapse>
  );
};

export default CollapsableItems;
