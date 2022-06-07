import { useCallback, useMemo } from 'react';

import { useAtom } from 'jotai';
import {
  always,
  any,
  append,
  cond,
  equals,
  filter,
  find,
  flatten,
  includes,
  isNil,
  map,
  mapObjIndexed,
  not,
  pipe,
  prop,
  propEq,
  T,
} from 'ramda';

import { getData, useRequest } from '@centreon/ui';

import Navigation, { Page } from './models';
import navigationAtom from './navigationAtoms';

export const navigationEndpoint =
  './api/internal.php?object=centreon_topology&action=navigationList';

interface UseNavigationState {
  allowedPages?: Array<string | Array<string>>;
  getNavigation: () => void;
  menu?: Array<Page>;
  reactRoutes?: Record<string, string>;
}

const isDefined = pipe(isNil, not);
const propExists = <T>(property: string): ((obj: T) => boolean) =>
  pipe(prop(property) as (obj: T) => unknown, isDefined);

const getAllowedPages = ({
  page,
  newAccumulator,
}): ((...a: Array<Page>) => Array<string>) =>
  cond<Page, Array<string>>([
    [
      propEq('is_react', true) as (obj: Page) => boolean,
      always(append<string>(page.url as string, newAccumulator)),
    ],
    [
      propExists<Page>('page'),
      always(append<string>(page.page as string, newAccumulator)),
    ],
    [T, always(newAccumulator)],
  ]);

const useNavigation = (): UseNavigationState => {
  const { sendRequest } = useRequest<Navigation>({
    request: getData,
  });
  const [navigation, setNavigation] = useAtom(navigationAtom);

  const getNavigation = (): void => {
    sendRequest({
      endpoint: navigationEndpoint,
    }).then(setNavigation);
  };

  const reduceAllowedPages = useCallback(
    (acc: Array<string>, page: Page): Array<string> => {
      const children = pipe(
        map<string, string | null>((property) => {
          if (!page[property]) {
            return null;
          }

          return page[property].reduce(reduceAllowedPages, []);
        }),
        filter(isDefined),
      )(['groups', 'children']) as Array<string>;

      const newAccumulator = [...acc, ...flatten(children)];

      return getAllowedPages({ newAccumulator, page })(page);
    },
    [],
  );

  const filterShowableElements = (acc, page): Array<Page> => {
    if (equals(page.show, false)) {
      return acc;
    }

    const pages = map(
      (property) => {
        if (!page[property]) {
          return null;
        }

        return {
          ...page,
          [property]: page[property].reduce(filterShowableElements, []),
        };
      },
      ['groups', 'children'],
    );

    const getShowablePages = cond([
      [any(isDefined), find(isDefined)],
      [T, always(page)],
    ]);

    return [...acc, getShowablePages(pages)];
  };

  const filterNotEmptyGroup = useCallback((group): boolean => {
    if (not(group.children)) {
      return false;
    }

    return any<Page>((page) => equals(page.show, true), group.children);
  }, []);

  const removeEmptyGroups = useCallback((acc, page): Array<Page> => {
    if (page.children) {
      return [
        ...acc,
        {
          ...page,
          children: page.children.reduce(removeEmptyGroups, []),
        },
      ];
    }

    if (page.groups) {
      return [
        ...acc,
        {
          ...page,
          groups: page.groups.filter(filterNotEmptyGroup),
        },
      ];
    }

    return [...acc, page];
  }, []);

  const findReactRoutes = useCallback(
    (acc, page: Page): Record<string, string> => {
      const children = mapObjIndexed((value, key) => {
        if (!includes(key, ['groups', 'children'])) {
          return null;
        }

        return (value as Array<Page>).reduce(findReactRoutes, {});
      }, page) as {
        ['children']?: Record<string, string>;
        ['groups']?: Record<string, string>;
      };

      const filteredChildren = filter(pipe(isNil, not), children);

      const newAccumulator = {
        ...acc,
        ...(filteredChildren?.children || {}),
        ...(filteredChildren?.groups || {}),
      };

      if (equals(page.is_react, false) || isNil(page.url)) {
        return newAccumulator;
      }

      return {
        ...newAccumulator,
        [page.url as string]: page.page as string,
      };
    },
    [],
  );

  const allowedPages = useMemo(
    (): Array<string> | undefined =>
      isNil(navigation)
        ? undefined
        : navigation.result.reduce(reduceAllowedPages, [] as Array<string>),
    [navigation],
  );

  const menu = useMemo(
    (): Array<Page> | undefined =>
      isNil(navigation)
        ? undefined
        : navigation.result
            .reduce(filterShowableElements, [])
            .reduce(removeEmptyGroups, []),
    [navigation],
  );

  const reactRoutes = useMemo(
    (): Record<string, string> | undefined =>
      isNil(navigation)
        ? undefined
        : navigation.result.reduce(findReactRoutes, {}),
    [navigation],
  );

  return {
    allowedPages,
    getNavigation,
    menu,
    reactRoutes,
  };
};

export default useNavigation;
