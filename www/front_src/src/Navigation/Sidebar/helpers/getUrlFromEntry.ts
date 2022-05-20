import { isNil } from 'ramda';

import { Page } from '../../models';

export const getUrlFromEntry = (
  entryProps: Page,
): string | null | undefined => {
  const page = isNil(entryProps?.page) ? '' : entryProps.page;
  const options = isNil(entryProps?.options) ? '' : entryProps.options;

  const urlOptions = `${page}${options}`;
  const url = entryProps.is_react
    ? entryProps.url
    : `/main.php?p=${urlOptions}`;

  return url;
};
