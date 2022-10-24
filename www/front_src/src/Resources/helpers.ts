interface ReplaceBasename {
  endpoint: string;
  newWord: string;
}

export const replaceBasename = ({
  newWord,
  endpoint
}: ReplaceBasename): string => {
  const basename =
    (document
      .getElementsByTagName('base')[0]
      ?.getAttribute('href') as string) || '';

  return endpoint.replace(basename, newWord);
};
