import React, { useState } from 'react';

import { equals } from 'ramda';
import classnames from 'classnames';
import { useAtom } from 'jotai';

import Collapse from '@mui/material/Collapse';
import List from '@mui/material/List';
import makeStyles from '@mui/styles/makeStyles';
import ListSubheader from '@mui/material/ListSubheader';

import { Page } from '../../models';
import { itemSelectedAtom, propsItemSelected } from '../sideBarAtoms';

import ListButton from './ListButton';

interface CollapsProps {
  currentTop?: number;
  currentWidth: number;
  data?: Array<Page>;
  handlClickItem: (item: Page) => void;
  isCollaps: boolean;
  isSubHeader?: boolean;
  level: number;
}

interface StyleProps {
  currentTop?: number;
  currentWidth: number;
}

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
    '& .MuiListItemButton-root': {
      height: theme.spacing(4),
      margin: theme.spacing(0.5, 0.5, 0.5, 0.5),
    },
    '& .MuiListItemIcon-root': {
      minWidth: theme.spacing(2.25),
    },

    '& .MuiTypography-root': {
      color: theme.palette.text.primary,
      fontSize: theme.typography.caption,
    },

    borderColor: theme.palette.primary.main,
    borderLeft: 'solid',

    borderWidth: theme.spacing(0.5),

    boxShadow: `${theme.spacing(1, 1, 1, 0)} ${theme.palette.divider}`,
    padding: theme.spacing(0.5),
  },
  scroll: {
    '&::-webkit-scrollbar': {
      width: theme.spacing(1.2),
    },

    '&::-webkit-scrollbar-thumb': {
      backgroundColor: theme.palette.action.disabled,
      borderRadius: 5,
    },
    '&::-webkit-scrollbar-track': {
      borderRadius: 5,
      boxShadow: `inset ${theme.spacing(0.5, 0.5, 0.5, 0.5)} ${
        theme.palette.action.disabledBackground
      }`,
    },
    maxHeight: theme.spacing(44),
    overflow: 'auto',
  },
  subHeader: {
    color: theme.palette.text.secondary,
    fontWeight: 'bold',
    lineHeight: theme.spacing(3),
    textAlign: 'center',
  },
  toggled: {
    backgroundColor: theme.palette.background.default,
    left: ({ currentWidth }: StyleProps): string => theme.spacing(currentWidth),
    position: 'fixed',
    top: ({ currentTop }: StyleProps): number | undefined => currentTop,
    width: theme.spacing(25.5),
    zIndex: theme.zIndex.mobileStepper,
  },
}));

const MinCollaps = ({
  data,
  isCollaps,
  isSubHeader,
  currentTop,
  currentWidth,
  handlClickItem,
  level,
}: CollapsProps): JSX.Element => {
  const classes = useStyles({ currentTop, currentWidth });
  const [selectedIndex, setSelectedIndex] = useState<number | null>(null);
  const [topItem, setTopItem] = useState<number>();
  const [widthItem, setWidthItem] = useState(0);
  const [itemSelectedNav, setitemSelectedNav] = useAtom(itemSelectedAtom);
  const levelName = `level_${level}_Navigated`;

  const handleHover = (
    e: React.MouseEvent<HTMLElement>,
    index: number,
    item: Page,
  ): void => {
    const rect = e.currentTarget.getBoundingClientRect();
    const top = Math.floor(rect.bottom) - Math.floor(rect.height);
    const width = Math.floor(rect.right) - Math.floor(rect.left);
    setWidthItem(currentWidth + width / 8);
    setTopItem(top);
    setSelectedIndex(index);
    const levelLabel = `level_${level}`;

    setitemSelectedNav({
      ...itemSelectedNav,
      [levelLabel]: { index, label: item.label, url: item?.url },
    });
  };

  const handleLeave = (): void => {
    setSelectedIndex(null);
    if (itemSelectedNav) {
      Object.keys(itemSelectedNav).forEach(() => {
        delete itemSelectedNav[`level_${level}`];
      });
    }
  };

  const isHover = (
    object: Record<string, propsItemSelected> | null,
    levelTitle: string,
    index: number,
    item: Page,
  ): boolean => {
    if (object && object[levelTitle]) {
      return (
        object[levelTitle].index === index &&
        object[levelTitle].label === item?.label &&
        object[levelTitle].url === item?.url
      );
    }

    return false;
  };

  const getNestedIndex = (index, ind: number, content: Array<Page>): number => {
    if (index > 1) {
      return (
        Number(content[0].children?.length) +
        Number(content[index - 1].children?.length) +
        ind
      );
    }
    if (index === 1) {
      return ind + Number(content[0].children?.length);
    }

    return ind;
  };

  const checkArray = (item: unknown): boolean => {
    if (Array.isArray(item)) {
      return item.length > 0;
    }

    return false;
  };

  return (
    <Collapse
      unmountOnExit
      className={classnames(classes.root, classes.toggled, {
        [classes.scroll]: isSubHeader,
      })}
      in={isCollaps}
      timeout="auto"
      onMouseLeave={handleLeave}
    >
      {data?.map((item, index) => {
        const hover =
          isHover(itemSelectedNav, levelName, index, item) ||
          equals(selectedIndex, index);

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
              checkArray(item?.children) &&
              item?.children?.map((content, ind) => {
                const nestedIndex = getNestedIndex(index, ind, data);
                const nestedHover =
                  isHover(itemSelectedNav, levelName, nestedIndex, content) ||
                  equals(selectedIndex, nestedIndex);

                return (
                  <ListButton
                    data={content}
                    handlClickItem={
                      !checkArray(item?.groups)
                        ? (): void => handlClickItem(content)
                        : undefined
                    }
                    hover={nestedHover}
                    isOpen={nestedIndex === selectedIndex}
                    key=""
                    onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                      handleHover(e, nestedIndex, content)
                    }
                  />
                );
              })
            ) : (
              <ListButton
                data={item}
                handlClickItem={
                  !checkArray(item?.groups)
                    ? (): void => handlClickItem(item)
                    : undefined
                }
                hover={hover}
                isOpen={index === selectedIndex}
                onMouseEnter={(e: React.MouseEvent<HTMLElement>): void =>
                  handleHover(e, index, item)
                }
              />
            )}

            {Array.isArray(item?.groups) && item.groups.length > 1 ? (
              <MinCollaps
                isSubHeader
                currentTop={topItem}
                currentWidth={widthItem + 2}
                data={item.groups}
                handlClickItem={handlClickItem}
                isCollaps={index === selectedIndex}
                level={level + 1}
              />
            ) : (
              checkArray(item?.groups) &&
              item?.groups?.map(
                (itemGroup) =>
                  checkArray(itemGroup?.children) && (
                    <div key={itemGroup.label}>
                      <MinCollaps
                        currentTop={topItem}
                        currentWidth={widthItem + 2}
                        data={itemGroup.children}
                        handlClickItem={handlClickItem}
                        isCollaps={index === selectedIndex}
                        level={level + 1}
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

export default MinCollaps;
