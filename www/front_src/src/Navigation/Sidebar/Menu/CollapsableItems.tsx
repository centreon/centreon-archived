import React, { useState } from 'react';

import { equals, clone } from 'ramda';
import clsx from 'clsx';
import { useAtom } from 'jotai';

import Collapse from '@mui/material/Collapse';
import List from '@mui/material/List';
import makeStyles from '@mui/styles/makeStyles';
import ListSubheader from '@mui/material/ListSubheader';

import { useMemoComponent } from '@centreon/ui';

import { Page } from '../../models';
import {
  navigationItemSelectedAtom,
  propsNavigationItemSelected,
} from '../sideBarAtoms';

import MenuItems from './MenuItems';

interface Props {
  collapseScrollMaxHeight?: number;
  collapseScrollMaxWidth?: number;
  currentTop?: number;
  currentWidth: number;
  data?: Array<Page>;
  isCollapsed: boolean;
  isSubHeader?: boolean;
  level: number;
  onClick: (item: Page, level: number) => void;
  setCollapseScrollMaxHeight: React.Dispatch<
    React.SetStateAction<number | undefined>
  >;
  setCollapseScrollMaxWidth: React.Dispatch<
    React.SetStateAction<number | undefined>
  >;
}

interface StyleProps {
  collapseScrollMaxHeight?: number;
  collapseScrollMaxWidth?: number;
  currentTop?: number;
  currentWidth: number;
}

const collapseWidth = 20.6;
export const collapseBorderWidth = 0.1;

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
      padding: theme.spacing(0, 0.25, 0, 0.75),
    },
    '& .MuiTypography-root': {
      color: theme.palette.text.primary,
      fontSize: theme.typography.caption,
    },
    border: `solid ${theme.palette.divider} ${collapseBorderWidth}px`,
    boxSizing: 'border-box',
  },
  subHeader: {
    color: theme.palette.text.secondary,
    fontSize: theme.typography.caption.fontSize,
    fontWeight: 'bold',
    lineHeight: theme.spacing(2.9),
    textAlign: 'center',
  },
  toggled: {
    '&::-webkit-scrollbar': {
      width: theme.spacing(1),
    },
    '&::-webkit-scrollbar-corner': {
      backgroundColor: theme.palette.background.default,
    },
    '&::-webkit-scrollbar-thumb': {
      backgroundColor: theme.palette.action.disabled,
    },
    '&::-webkit-scrollbar-track': {
      border: `solid ${theme.palette.action.hover} 0.5px`,
    },
    backgroundColor: theme.palette.background.default,
    left: ({ currentWidth }: StyleProps): string => theme.spacing(currentWidth),
    maxHeight: ({ collapseScrollMaxHeight }: StyleProps): string =>
      collapseScrollMaxHeight
        ? theme.spacing(collapseScrollMaxHeight)
        : theme.spacing(50),
    maxWidth: ({ collapseScrollMaxWidth }: StyleProps): string =>
      collapseScrollMaxWidth
        ? theme.spacing(collapseScrollMaxWidth)
        : theme.spacing(collapseWidth),
    overflow: 'auto',
    position: 'fixed',
    top: ({ currentTop }: StyleProps): number | undefined => currentTop,
    width: theme.spacing(collapseWidth),
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
  collapseScrollMaxHeight,
  collapseScrollMaxWidth,
  setCollapseScrollMaxWidth,
  setCollapseScrollMaxHeight,
}: Props): JSX.Element => {
  const classes = useStyles({
    collapseScrollMaxHeight,
    collapseScrollMaxWidth,
    currentTop,
    currentWidth,
  });
  const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);
  const [itemTop, setItemTop] = useState<number>();
  const [nestedScrollCollapsMaxHeight, setNestedScrollCollapsMaxHeight] =
    useState<undefined | number>(undefined);
  const [nestedScrollCollapsMaxWidth, setNestedScrollCollapsMaxWidth] =
    useState<undefined | number>(undefined);
  const collapsRef = React.useRef<HTMLElement | null>(null);
  const [navigationItemSelected, setNavigationItemSelected] = useAtom(
    navigationItemSelectedAtom,
  );
  const levelName = `level_${level}_Navigated`;
  const itemWidth = currentWidth + collapseWidth;
  const minimumMarginBottom = 4;

  const hoverItem = (
    e: React.MouseEvent<HTMLElement>,
    index: number,
    item: Page,
  ): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const { top } = rect;
    setItemTop(top - collapseBorderWidth);
    setHoveredIndex(index);
    const levelLabel = `level_${level}`;

    setNavigationItemSelected({
      ...navigationItemSelected,
      [levelLabel]: { index, label: item.label, url: item?.url },
    });
  };

  const deleteNavigationItemsSelected = (
    navigationItems: Record<string, propsNavigationItemSelected>,
  ): void => {
    const navigation = clone(navigationItems);
    Object.keys(navigation).forEach(() => {
      delete navigation[`level_${level}`];
    });

    setNavigationItemSelected(navigation);
  };

  const handleLeave = (): void => {
    setHoveredIndex(null);
    if (navigationItemSelected) {
      deleteNavigationItemsSelected(navigationItemSelected);
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

  const updateCollapseSize = (el: HTMLElement): void => {
    const rect = el.getBoundingClientRect();
    setCollapseScrollMaxHeight(
      (window.innerHeight - rect.top) / 8 - minimumMarginBottom,
    );
    setCollapseScrollMaxWidth((window.innerWidth - rect.left) / 8);
  };

  React.useEffect(() => {
    if (collapsRef && collapsRef.current) {
      updateCollapseSize(collapsRef.current);
    }
  }, []);

  return useMemoComponent({
    Component: (
      <Collapse
        unmountOnExit
        className={clsx(classes.root, classes.toggled)}
        in={isCollapsed}
        ref={collapsRef}
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
                          ? (): void => onClick(content, level)
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
                      ? (): void => onClick(item, level)
                      : undefined
                  }
                  onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                    hoverItem(e, index, item)
                  }
                />
              )}

              {Array.isArray(item?.groups) &&
              item.groups.length > 1 &&
              equals(index, hoveredIndex) ? (
                <CollapsableItems
                  isSubHeader
                  collapseScrollMaxHeight={nestedScrollCollapsMaxHeight}
                  collapseScrollMaxWidth={nestedScrollCollapsMaxWidth}
                  currentTop={itemTop}
                  currentWidth={itemWidth}
                  data={item.groups}
                  isCollapsed={index === hoveredIndex}
                  level={level + 1}
                  setCollapseScrollMaxHeight={setNestedScrollCollapsMaxHeight}
                  setCollapseScrollMaxWidth={setNestedScrollCollapsMaxWidth}
                  onClick={onClick}
                />
              ) : (
                isArrayItem(item?.groups) &&
                equals(index, hoveredIndex) &&
                item?.groups?.map(
                  (itemGroup) =>
                    isArrayItem(itemGroup?.children) && (
                      <div key={itemGroup.label}>
                        <CollapsableItems
                          collapseScrollMaxHeight={nestedScrollCollapsMaxHeight}
                          collapseScrollMaxWidth={nestedScrollCollapsMaxWidth}
                          currentTop={itemTop}
                          currentWidth={itemWidth}
                          data={itemGroup.children}
                          isCollapsed={index === hoveredIndex}
                          level={level + 1}
                          setCollapseScrollMaxHeight={
                            setNestedScrollCollapsMaxHeight
                          }
                          setCollapseScrollMaxWidth={
                            setNestedScrollCollapsMaxWidth
                          }
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
    ),
    memoProps: [
      isCollapsed,
      collapseScrollMaxHeight,
      setCollapseScrollMaxWidth,
      nestedScrollCollapsMaxHeight,
      nestedScrollCollapsMaxWidth,
      hoveredIndex,
      navigationItemSelected,
    ],
  });
};

export default CollapsableItems;
