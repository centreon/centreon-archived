interface ReplaceWord {
  endpoint: string;
  newWord: string;
}

export const replaceWord = ({ newWord, endpoint }: ReplaceWord): string => {
  const basename =
    (document
      .getElementsByTagName('base')[0]
      ?.getAttribute('href') as string) || '';

  return endpoint.replace(basename, newWord);
};
