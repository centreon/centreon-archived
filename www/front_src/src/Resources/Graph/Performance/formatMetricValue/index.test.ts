import formatMetricValue from '.';

type TestCase = [number | null, string, 1000 | 1024, string | null];

describe(formatMetricValue, () => {
  const cases: Array<TestCase> = [
    [218857269, '', 1000, '218.86m'],
    [218857269, '', 1024, '208.72M'],
    [0.12232323445, '', 1000, '0.12'],
    [1024, 'B', 1000, '1K'],
    [1024, 'B', 1024, '1K'],
    [null, 'B', 1024, null],
  ];

  it.each(cases)(
    'formats the given value to a human readable form according to the given unit and base',
    (value, unit, base, formattedResult) => {
      expect(formatMetricValue({ base, unit, value })).toEqual(formattedResult);
    },
  );
});
