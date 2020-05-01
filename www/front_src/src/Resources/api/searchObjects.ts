import isEmpty from 'lodash/isEmpty';

interface SearchObject {
  field: string;
  value: string;
}

type SearchPatterns = Array<{ [field: string]: { $rg: string } }>;

export interface OrSearchParam {
  $or: SearchPatterns;
}

interface AndSearchParam {
  $and: SearchPatterns;
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

const getSearchParam = ({
  searchValue,
  searchOptions,
}): OrSearchParam | AndSearchParam | undefined => {
  if (!searchValue) {
    return undefined;
  }

  const foundSearchObjects = getFoundSearchObjects({
    searchValue,
    searchOptions,
  });

  if (!isEmpty(foundSearchObjects)) {
    return {
      $and: foundSearchObjects.map(({ field, value }) => ({
        [field]: { $rg: `${value}` },
      })),
    };
  }

  return {
    $or: searchOptions.map((searchOption) => ({
      [searchOption]: { $rg: `${searchValue}` },
    })),
  };
};

export { getSearchParam };
