type SearchableField =
  | 'host.name'
  | 'host.alias'
  | 'host.address'
  | 'service.description';

const searchOptions: Array<SearchableField> = [
  'host.name',
  'host.alias',
  'host.address',
  'service.description',
];

interface SearchObject {
  field: SearchableField;
  value: string;
}

interface SearchParam {
  $or: Array<{ [field in SearchableField]?: { $lk: string } }>;
}

const getFoundSearchObjects = (searchValue: string): Array<SearchObject> => {
  const searchOptionMatches = searchOptions.map((searchOption) => {
    const pattern = `${searchOption.replace('.', '\\.')}:([^\\s]+)`;

    const [, searchOptionMatch] = searchValue.match(pattern) || [];

    return { field: searchOption, value: searchOptionMatch };
  });

  return searchOptionMatches.filter(({ value }) => value);
};

const getDefaultSearchObjects = (value): Array<SearchObject> => {
  return searchOptions.map((searchOption) => ({ field: searchOption, value }));
};

const getSearchParam = (searchValue: string): SearchParam | undefined => {
  if (!searchValue) {
    return undefined;
  }
  const foundSearchObjects = getFoundSearchObjects(searchValue);

  const searchObjectsToSend =
    foundSearchObjects.length > 0
      ? foundSearchObjects
      : getDefaultSearchObjects(searchValue);

  return {
    $or: searchObjectsToSend.map(({ field, value }) => ({
      [field]: { $lk: `%${value}%` },
    })),
  };
};

export { getSearchParam };
