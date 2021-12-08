import * as React from 'react';

import { useAtom } from 'jotai';
import {
  any,
  append,
  equals,
  filter,
  find,
  flatten,
  includes,
  isNil,
  mapObjIndexed,
  not,
  pipe,
} from 'ramda';

import { getData, useRequest } from '@centreon/ui';

import Navigation, { Page } from './models';
import navigationAtom from './navigationAtoms';

const navigationEndpoint =
  './api/internal.php?object=centreon_topology&action=navigationList';

interface UseNavigationState {
  allowedPages?: Array<string | Array<string>>;
  getNavigation: () => void;
  menu?: Array<Page>;
  reactRoutes?: Record<string, string>;
}

const useNavigation = (): UseNavigationState => {
  const { sendRequest } = useRequest<Navigation>({
    request: getData,
  });
  const [navigation, setNavigation] = useAtom(navigationAtom);

  const getNavigation = (): void => {
    sendRequest(navigationEndpoint).then(setNavigation);
  };

  const getAllowedPages = React.useCallback((acc, page): Array<string> => {
    const children = ['groups', 'children']
      .map((property) => {
        if (!page[property]) {
          return null;
        }

        return page[property].reduce(getAllowedPages, []);
      })
      .filter(pipe(isNil, not));

    const newAccumulator = [...acc, ...flatten(children)];

    if (equals(page.is_react, true)) {
      return append(page.url, newAccumulator);
    }
    if (page.page) {
      return append(page.page, newAccumulator);
    }

    return newAccumulator;
  }, []);

  const filterShowableElements = (acc, page): Array<Page> => {
    if (equals(page.show, false)) {
      return acc;
    }

    const pages = ['groups', 'children'].map((property) => {
      if (!page[property]) {
        return null;
      }

      return {
        ...page,
        [property]: page[property].reduce(filterShowableElements, []),
      };
    });

    if (any((subPage) => not(isNil(subPage)), pages)) {
      return [
        ...acc,
        find((subPage) => not(isNil(subPage)), pages) as Array<Page>,
      ];
    }

    return [...acc, page];
  };

  const filterNotEmptyGroup = React.useCallback((group): boolean => {
    if (not(group.children)) {
      return false;
    }

    return any<Page>((page) => equals(page.show, true), group.children);
  }, []);

  const removeEmptyGroups = React.useCallback((acc, page): Array<Page> => {
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

  const findReactRoutes = React.useCallback(
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

  const allowedPages = isNil(navigation)
    ? undefined
    : navigation.result.reduce(
        getAllowedPages,
        [] as Array<string | Array<string>>,
      );

  const menu = isNil(navigation)
    ? undefined
    : navigation.result
        .reduce(filterShowableElements, [])
        .reduce(removeEmptyGroups, []);

  const reactRoutes = isNil(navigation)
    ? undefined
    : navigation.result.reduce(findReactRoutes, {});

  return {
    allowedPages,
    getNavigation,
    menu,
    reactRoutes,
  };
};

export default useNavigation;
