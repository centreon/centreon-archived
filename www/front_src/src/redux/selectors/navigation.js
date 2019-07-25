import { createSelector } from 'reselect';

// loop on each child and get first available url
function getFirstAvailableUrl(item) {
  let firstAvailableUrl = null;

  if (item.groups) {
    for (const group of item.groups) {
      firstAvailableUrl = getFirstAvailableUrl(group);
      if (firstAvailableUrl) {
        return firstAvailableUrl;
      }
    }
  }

  if (item.children) {
    for (const child of item.children) {
      if (child.url) {
        if (child.is_react) {
          return child.url;
        } else {
          // construct legacy route
          return `/main.php?p=${child.page}${child.options !== null ? child.options : ''}`;
        }
      } else {
        firstAvailableUrl = getFirstAvailableUrl(child);
        if (firstAvailableUrl) {
          return firstAvailableUrl;
        }
      }
    }
  }

  return null;
}

// get breadcrumb step information from an entry
function getBreadcrumbStep(item) {
  let step = null;
  if (item.is_react) {
    step = {
      label: item.label,
      link: item.url
    };
  } else {
    const availableUrl = getFirstAvailableUrl(item);
    if (availableUrl) {
      step = {
        label: item.label,
        link: availableUrl
      };
    }
  }

  return step;
}

const getMenuItems = (state) => state.navigation.menuItems;

export const breadcrumbsSelector = createSelector(
  getMenuItems,
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