import {
  Dispatch,
  MouseEvent,
  SetStateAction,
  useEffect,
  useRef,
  useState,
} from 'react';

import { equals } from 'ramda';
import clsx from 'clsx';
import { useUpdateAtom, useAtomValue } from 'jotai/utils';

import Collapse from '@mui/material/Collapse';
import List from '@mui/material/List';
import makeStyles from '@mui/styles/makeStyles';
import ListSubheader from '@mui/material/ListSubheader';

import { useMemoComponent } from '@centreon/ui';

import { Page } from '../../models';
import {
  selectedNavigationItemsAtom,
  hoveredNavigationItemsAtom,
  setHoveredNavigationItemsDerivedAtom,
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
  onClick: (item: Page) => void;
  onLeave?: () => void;
  setCollapseScrollMaxHeight: Dispatch<SetStateAction<number | undefined>>;
  setCollapseScrollMaxWidth: Dispatch<SetStateAction<number | undefined>>;
}

interface StyleProps {
  collapseScrollMaxHeight?: number;
  collapseScrollMaxWidth?: number;
  currentTop?: number;
  currentWidth: number;
}

const collapseWidth = 24;

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
      padding: theme.spacing(0, 0.25, 0, 0.1),
    },
    '& .MuiTypography-root': {
      color: theme.palette.text.primary,
      fontSize: theme.typography.caption,
    },
    boxShadow: theme.shadows[3],
    outline: `solid  ${theme.palette.divider} ${0.1}px`,
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
    position: 'fixed',
    top: ({ currentTop }: StyleProps): number | undefined => currentTop,
    whiteSpace: 'normal',
    width: theme.spacing(collapseWidth),
    zIndex: theme.zIndex.mobileStepper,
  },
}));

const CollapsibleItems = ({
  data,
  isCollapsed,
  isSubHeader,
  currentTop,
  currentWidth,
  onClick,
  onLeave,
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
  const collapsRef = useRef<HTMLElement | null>(null);
  const hoveredNavigationItems = useAtomValue(hoveredNavigationItemsAtom);
  const selectedNavigationItems = useAtomValue(selectedNavigationItemsAtom);
  const setHoveredNavigationItems = useUpdateAtom(
    setHoveredNavigationItemsDerivedAtom,
  );

  const levelName = `level_${level}`;
  const itemWidth = currentWidth + collapseWidth;
  const minimumMarginBottom = 4;

  const hoverItem = ({ e, index, currentPage }): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const { top } = rect;
    setItemTop(top);
    setHoveredIndex(index);
    setHoveredNavigationItems({ currentPage, levelName });
  };

  const isItemHovered = ({
    navigationItem,
    levelTitle,
    currentPage,
  }): boolean => {
    if (navigationItem && navigationItem[levelTitle]) {
      return (
        navigationItem[levelTitle].label === currentPage.label &&
        navigationItem[levelTitle]?.url === currentPage?.url
      );
    }

    return false;
  };

  const getNestedIndex = ({ itemIndex, childIndex, content }): number => {
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

  useEffect(() => {
    if (isCollapsed && collapsRef && collapsRef.current) {
      updateCollapseSize(collapsRef.current);
    }
  }, [isCollapsed]);

  return useMemoComponent({
    Component: (
      <Collapse
        unmountOnExit
        className={clsx(classes.root, classes.toggled)}
        enter={false}
        exit={false}
        in={isCollapsed}
        ref={collapsRef}
        timeout={0}
        onMouseLeave={onLeave}
      >
        {data?.map((item, index) => {
          const hover =
            isItemHovered({
              currentPage: item,
              levelTitle: levelName,
              navigationItem: selectedNavigationItems,
            }) || equals(hoveredIndex, index);

          const mouseEnterItem = (e: MouseEvent<HTMLElement>): void =>
            hoverItem({ currentPage: item, e, index });

          const isCollapseWithSubheader =
            Array.isArray(item?.groups) && item.groups.length > 1;

          const isSimpleCollapse =
            isArrayItem(item?.groups) && equals(index, hoveredIndex);

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
                  const nestedIndex = getNestedIndex({
                    childIndex: ind,
                    content: data,
                    itemIndex: index,
                  });
                  const nestedHover =
                    isItemHovered({
                      currentPage: content,
                      levelTitle: levelName,
                      navigationItem: selectedNavigationItems,
                    }) || equals(hoveredIndex, nestedIndex);

                  const mouseEnterContent = (
                    e: MouseEvent<HTMLElement>,
                  ): void =>
                    hoverItem({ currentPage: content, e, index: nestedIndex });

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
                      onMouseEnter={mouseEnterContent}
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
                  onMouseEnter={mouseEnterItem}
                />
              )}

              {isCollapseWithSubheader && equals(index, hoveredIndex) ? (
                <CollapsibleItems
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
                isSimpleCollapse &&
                item?.groups?.map(
                  (itemGroup) =>
                    isArrayItem(itemGroup?.children) && (
                      <div key={itemGroup.label}>
                        <CollapsibleItems
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
      collapseScrollMaxWidth,
      nestedScrollCollapsMaxHeight,
      nestedScrollCollapsMaxWidth,
      hoveredIndex,
      selectedNavigationItems,
      hoveredNavigationItems,
    ],
  });
};

export default CollapsibleItems;
