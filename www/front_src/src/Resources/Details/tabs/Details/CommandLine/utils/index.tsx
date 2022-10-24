import { filter, equals, isNil, find, pipe, head } from 'ramda';
import commandParser from 'string-argv';

const isShortArgument = (argument: string): boolean => {
  return argument.startsWith('-') && argument.length === 2 && argument !== '--';
};

interface CommandWithArguments {
  arguments: Array<Array<string>>;
  command: string;
}

const getCommandWithArguments = (commandLine: string): CommandWithArguments => {
  const commandWithArguments = commandParser(commandLine);

  const [command, ...args] = commandWithArguments;

  const shortArguments = filter(isShortArgument, args);

  const shortArgumentsWithValues = shortArguments.map((argument) => {
    const index = args.findIndex(equals(argument));

    const nextArgument = args[index + 1];

    if (isNil(nextArgument) || isShortArgument(nextArgument)) {
      return [argument];
    }

    return [argument, nextArgument];
  });

  const argumentWithValues = args.map((argument) => {
    const foundShortArgument = find(
      pipe(head, equals(argument)),
      shortArgumentsWithValues
    );

    if (!isNil(foundShortArgument)) {
      return foundShortArgument;
    }

    return [argument];
  });

  const commandArguments = argumentWithValues.filter(([argument], index) => {
    const previousArgument = argumentWithValues[index - 1];
    const previousArgumentValue = previousArgument?.[1];

    return !equals(previousArgumentValue, argument);
  });

  return {
    arguments: commandArguments,
    command
  };
};

export { getCommandWithArguments };
