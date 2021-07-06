import { Search } from './models';

import { build, parse } from './index';

const search =
  'h.name:blabla resource_types:host,service host_groups:1,2 blabla';
const parsedSearch = [
  [
    ['resource_types', ['host', 'service']],
    ['host_groups', ['1', '2']],
  ],
  'h.name:blabla blabla',
] as Search;

describe(parse, () => {
  it('parses the given search string into a Search model', () => {
    const result = parse(search);

    expect(result).toEqual(parsedSearch);
  });
});

// describe(build, () => {
//   it('builds a search string from the given Search model', () => {
//     const parsed = [
//       [
//         ['resource_types', ['host', 'service']],
//         ['host_groups', ['1', '2']],
//       ],
//       'h.name:blabla blabla',
//     ] as Search;

//     const result = build(parsed);

//     expect(result).toEqual(
//       'resource_types:host,service host_groups:1,2 h.name:blabla blabla',
//     );
// });
// });
