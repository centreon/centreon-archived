import { equals, isNil } from 'ramda';

import { Page } from '../../models';

export const getUrlFromEntry = ({
  page,
  options,
  is_react,
  url,
}: Page): string | null | undefined => {
  const currentPage = isNil(page) ? '' : page;
  const currentOptions = isNil(options) ? '' : options;

  const urlOptions = `${currentPage}${currentOptions}`;
  const currentUrl = is_react ? url : `/main.php?p=${urlOptions}`;

  return currentUrl;
};

const isArrayItem = (item: unknown): boolean => {
  if (isNil(item) || !Array.isArray(item) || equals(item?.length, 0)) {
    return false;
  }

  return true;
};

export const searchUrlFromEntry = (item: Page): string | null | undefined => {
  const childPage = item?.children;
  const groupPage = item?.groups;

  if (isArrayItem(childPage) && Array.isArray(childPage)) {
    const grandsonGroup = childPage[0]?.groups;
    if (isArrayItem(grandsonGroup) && Array.isArray(grandsonGroup)) {
      return searchUrlFromEntry(grandsonGroup[0]);
    }

    return searchUrlFromEntry(childPage[0]);
  }
  if (isArrayItem(groupPage) && Array.isArray(groupPage)) {
    const grandsonPage = groupPage[0]?.children;
    if (isArrayItem(grandsonPage) && Array.isArray(grandsonPage)) {
      return searchUrlFromEntry(grandsonPage[0]);
    }

    return searchUrlFromEntry(groupPage[0]);
  }

  return getUrlFromEntry(item);
};
