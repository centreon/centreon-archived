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
          return `/main.php?p=${child.page}${child.options}`;
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

export const breadcrumbsSelector = createSelector(
  ({ state: { menuItems } }) => {
    let breadcrumbs = {};

    menuItems.map((itemLvl1) => {

      //url = getLevelUrl(itemLvl1);
      let urlLvl1 = null;
      if (itemLvl1.is_react) {
        urlLvl1 = itemLvl1.url;
        breadcrumbs[itemLvl1.url] = [
          [itemLvl1.label, urlLvl1]
        ];
      } else {
        urlLvl1 = getFirstAvailableUrl(itemLvl1);
      }

      if (itemLvl1.children) {
        itemLvl1.children.map((itemLvl2) => {
          let urlLvl2 = null;
          if (itemLvl2.is_react) {
            urlLvl2 = itemLvl2.url;
            breadcrumbs[itemLvl2.url] = [
              [itemLvl1.label, urlLvl1],
              [itemLvl2.label, urlLvl2],
            ];
          } else {
            urlLvl2 = getFirstAvailableUrl(itemLvl2);
          }

          if (itemLvl2.groups) {
            itemLvl2.groups.map((groupLvl3) => {
              if (groupLvl3.children) {
                groupLvl3.children.map((itemLvl3) => {
                  if (itemLvl3.is_react) {
                    breadcrumbs[itemLvl3.url] = [
                      [itemLvl1.label, urlLvl1],
                      [itemLvl2.label, urlLvl2],
                      [itemLvl3.label, itemLvl3.url]
                    ];
                  }
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