import { equals, isNil } from 'ramda';

import { Page } from '../../models';

export const getUrlFromEntry = ({
  page,
  options,
  is_react,
  url
}: Page): string | null | undefined => {
  const currentPage = isNil(page) ? '' : page;
  const currentOptions = isNil(options) ? '' : options;

  const urlOptions = `${currentPage}${currentOptions}`;
  const currentUrl = is_react ? url : `/main.php?p=${urlOptions}`;

  return currentUrl;
};

const isArrayItem = (item: unknown): boolean => {
  return !isNil(item) && Array.isArray(item) && !equals(item?.length, 0);
};

export const searchUrlFromEntry = (item: Page): string | null | undefined => {
  const childPage = item?.children;
  const groupPage = item?.groups;

  if (isArrayItem(childPage)) {
    const grandsonGroup = childPage?.[0]?.groups;
    if (isArrayItem(grandsonGroup)) {
      return searchUrlFromEntry(grandsonGroup?.[0] as Page);
    }

    return searchUrlFromEntry(childPage?.[0] as Page);
  }
  if (isArrayItem(groupPage)) {
    const grandsonPage = groupPage?.[0]?.children;
    if (isArrayItem(grandsonPage)) {
      return searchUrlFromEntry(grandsonPage?.[0] as Page);
    }

    return searchUrlFromEntry(groupPage?.[0] as Page);
  }

  return getUrlFromEntry(item);
};
