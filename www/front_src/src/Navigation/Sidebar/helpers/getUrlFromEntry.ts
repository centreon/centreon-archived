import { isNil } from 'ramda';

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
