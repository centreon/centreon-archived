import { parse } from './index';

describe(parse, () => {
  it('parses', () => {
    const search = 'resource_types:host,service';

    console.log(parse(search));
  });
});
