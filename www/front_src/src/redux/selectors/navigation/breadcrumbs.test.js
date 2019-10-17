import { breadcrumbsSelector } from './breadcrumbs';

describe('breadcrumbsSelector', () => {
  it('returns formatted breadcrumbs', () => {
    const state = {
      navigation: {
        items: [
          {
            page: '1',
            label: 'Home',
            is_react: false,
            url: './include/home/home.php',
            options: null,
            children: [
              {
                groups: [],
                page: '103',
                label: 'Custom Views',
                is_react: true,
                url: '/home/customViews',
                options: null,
              },
            ],
          },
        ],
      },
    };
    const breadcrumbs = breadcrumbsSelector(state);

    expect(breadcrumbs).toEqual({
      '/main.php?p=1': [
        { label: 'Home', link: '/main.php?p=1' },
      ],
      '/home/customViews': [
        { label: 'Home', link: '/main.php?p=1' },
        { label: 'Custom Views', link: '/home/customViews' },
      ],
    });
  });
});