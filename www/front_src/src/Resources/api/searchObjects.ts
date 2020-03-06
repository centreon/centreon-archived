interface SearchObject {
  field: string;
  value: string;
}

export interface SearchParam {
  $and: Array<{ [field: string]: { $rg: string } }>;
}

const getFoundSearchObjects = ({
  searchValue,
  searchOptions,
}): Array<SearchObject> => {
  const searchOptionMatches = searchOptions.map((searchOption) => {
    const pattern = `${searchOption.replace('.', '\\.')}:([^\\s]+)`;

    const [, searchOptionMatch] = searchValue.match(pattern) || [];

    return { field: searchOption, value: searchOptionMatch };
  });

  return searchOptionMatches.filter(({ value }) => value);
};

const getDefaultSearchObjects = ({
  searchValue,
  searchOptions,
}): Array<SearchObject> => {
  return searchOptions.map((searchOption) => ({
    field: searchOption,
    value: searchValue,
  }));
};

const getSearchParam = ({
  searchValue,
  searchOptions,
}): SearchParam | undefined => {
  if (!searchValue) {
    return undefined;
  }
  const foundSearchObjects = getFoundSearchObjects({
    searchValue,
    searchOptions,
  });

  const searchObjectsToSend =
    foundSearchObjects.length > 0
      ? foundSearchObjects
      : getDefaultSearchObjects({ searchValue, searchOptions });

  return {
    $and: searchObjectsToSend.map(({ field, value }) => ({
      [field]: { $rg: `${value}` },
    })),
  };
};

export { getSearchParam };
