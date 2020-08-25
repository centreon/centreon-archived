import formatMetricValue from '.';

type TestCase = [number, string, 1000 | 1024, string];

describe(formatMetricValue, () => {
  const cases: Array<TestCase> = [
    [218857269, '', 1000, '218.86m'],
    [218857269, '', 1024, '219M'],
    [0.12232323445, '', 1000, '0.12'],
    [1024, 'B', 1000, '1K'],
    [1024, 'B', 1024, '1K'],
  ];

  it.each(cases)(
    'formats the given value to a human readable form according to the given unit and base',
    (value, unit, base, formattedResult) => {
      expect(formatMetricValue({ value, unit, base })).toEqual(formattedResult);
    },
  );
});
