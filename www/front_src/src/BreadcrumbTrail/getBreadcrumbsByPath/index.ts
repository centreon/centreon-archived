/* eslint-disable @typescript-eslint/no-use-before-define */

import { Page } from '../../Navigation/models';
import { BreadcrumbsByPath } from '../models';

/**
 * get URL from legacy or react pages
 * @param {Object} item
 * @return {String} build URL
 */
const getUrl = (item): string =>
  item.is_react
    ? item.url
    : `/main.php?p=${item.page}${item.options !== null ? item.options : ''}`;

/**
 * loop on each group/child to get first url
 * @param {Object} item
 * @return {String|undefined} first url found
 */
const findFirstUrl = (item): string | undefined => {
  if (item.url) {
    return getUrl(item);
  }

  if (item.groups) {
    const groupWithUrl = item.groups.find(findFirstUrl);

    return groupWithUrl && groupWithUrl.children
      ? getFirstUrlInChildren(groupWithUrl)
      : undefined;
  }

  return item.children ? getFirstUrlInChildren(item) : undefined;
};

/**
 * find first URL in children prop
 * @param {Object} item
 * @return {String|undefined} first url found
 */
const getFirstUrlInChildren = (item): string | undefined => {
  if (!item.children) {
    return undefined;
  }

  const childrenWithUrl = item.children.find(findFirstUrl);

  return childrenWithUrl ? findFirstUrl(childrenWithUrl) : undefined;
};

interface Breadcrumb {
  label: string;
  link: string;
}

const getBreadcrumbStep = (item): Breadcrumb | null => {
  const availableUrl = item.url ? getUrl(item) : findFirstUrl(item);

  return availableUrl
    ? {
        label: item.label,
        link: availableUrl,
      }
    : null;
};

const getBreadcrumbsByPath = (navigation: Array<Page>): BreadcrumbsByPath => {
  const breadcrumbs = {};

  // build level 1 breadcrumbs
  navigation.forEach((itemLvl1) => {
    const stepLvl1 = getBreadcrumbStep(itemLvl1);
    if (stepLvl1 === null) {
      return;
    }
    breadcrumbs[stepLvl1.link] = [
      {
        label: stepLvl1.label,
        link: stepLvl1.link,
      },
    ];

    // build level 2 breadcrumbs
    if (itemLvl1.children) {
      itemLvl1.children.forEach((itemLvl2) => {
        const stepLvl2 = getBreadcrumbStep(itemLvl2);
        if (stepLvl2 === null) {
          return;
        }
        breadcrumbs[stepLvl2.link] = [
          {
            label: stepLvl1.label,
            link: stepLvl1.link,
          },
          {
            label: stepLvl2.label,
            link: stepLvl2.link,
          },
        ];

        // build level 3 breadcrumbs
        if (itemLvl2.groups) {
          itemLvl2.groups.forEach((groupLvl3) => {
            if (groupLvl3.children) {
              groupLvl3.children.forEach((itemLvl3) => {
                const stepLvl3 = getBreadcrumbStep(itemLvl3);
                if (stepLvl3 === null) {
                  return;
                }
                breadcrumbs[stepLvl3.link] = [
                  {
                    label: stepLvl1.label,
                    link: stepLvl1.link,
                  },
                  {
                    label: stepLvl2.label,
                    link: stepLvl2.link,
                  },
                  {
                    label: stepLvl3.label,
                    link: stepLvl3.link,
                  },
                ];
              });
            }
          });
        }
      });
    }
  });

  return breadcrumbs;
};

export default getBreadcrumbsByPath;
