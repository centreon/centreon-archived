import { createSelector } from 'reselect';

// loop on each group/child to get first url
function findFirstUrl(item) {
  if (item.groups) {
    const groupWithUrl = item.groups.find(findFirstUrl);

    return groupWithUrl ? findFirstUrlInChildren(groupWithUrl) : undefined;
  }

  return item.children ?  findFirstUrlInChildren(item) : undefined;
}

function findFirstUrlInChildren(item) {
  const childWithUrl = item.children ? item.children.find((child) => child.url) : undefined;

  return childWithUrl ? getUrl(childWithUrl) : undefined;
}

function getUrl(item) {
  return item.is_react ? item.url : `/main.php?p=${item.page}${item.options !== null ? item.options : ''}`;
}

// get breadcrumb step information from an entry
function getBreadcrumbStep(item) {
    const availableUrl = item.url ? getUrl(item) : findFirstUrl(item);
    return availableUrl
      ? {
        label: item.label,
        link: availableUrl,
      }
      : null;
}

export const reactRoutesSelector = createSelector(
  navigation,
  (menuItems) => {
    let breadcrumbs = {};

    // build level 1 breadcrumbs
    menuItems.map((itemLvl1) => {
      const stepLvl1 = getBreadcrumbStep(itemLvl1);
      if (stepLvl1 === null) {
        return;
      }
      breadcrumbs[stepLvl1.link] = [
        {
          label: stepLvl1.label,
          link: stepLvl1.link,
        }
      ];

      // build level 2 breadcrumbs
      if (itemLvl1.children) {
        itemLvl1.children.map((itemLvl2) => {
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
            itemLvl2.groups.map((groupLvl3) => {
              if (groupLvl3.children) {
                groupLvl3.children.map((itemLvl3) => {
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
  },
);