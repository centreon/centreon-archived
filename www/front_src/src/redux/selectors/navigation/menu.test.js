import { menuSelector } from './menu';

describe('menuSelector', () => {
  it('returns formatted menu', () => {
    const state = {
      navigation: {
        items: [
          {
            page: '6',
            label: 'Configuration',
            is_react: false,
            show: true,
            url: './configuration.php',
            options: null,
            children: [
              {
                groups: [
                  {
                    label: 'Main Menu',
                    children: [
                      {
                        page: '60201',
                        label: 'Services by host',
                        is_react: false,
                        show: true,
                        url: 'serviceByHost.php',
                        options: null,
                      },
                      {
                        page: '60202',
                        label: 'Hidden entry',
                        is_react: false,
                        show: false,
                        url: 'hiddenEntry.php',
                        options: null,
                      },
                    ]
                  }
                ],
                page: '602',
                label: 'Services',
                is_react: false,
                show: true,
                url: null,
                options: null,
              },
            ],
          },
        ],
      },
    };

    let expectedState = [...state.navigation.items];
    expectedState[0].children[0].groups[0].children.pop(); // remove hidden entry

    const menu = menuSelector(state);

    expect(menu).toEqual(expectedState);
  });
});