import * as React from 'react';

import { equals, filter, find, head, isNil, map, match } from 'ramda';
import commandParser from 'string-argv';

import { makeStyles, Typography } from '@material-ui/core';

const useStyles = makeStyles((theme) => ({
  command: {
    fontWeight: 'bold',
  },
}));

interface Props {
  commandLine: string;
}

const isShortArgument = (argument: string): boolean => {
  return argument.startsWith('-') && argument.length === 2 && argument !== '--';
};

const CommandCard = ({ commandLine }: Props): JSX.Element => {
  const classes = useStyles();

  const commandWithArguments = commandParser(commandLine);

  const [command, ...argus] = commandWithArguments;

  const single = filter(isShortArgument, argus);

  const singleWithArgus = single.map((sisi) => {
    const foundIndex = argus.findIndex(equals(sisi));

    const value = argus[foundIndex + 1];

    if (isNil(value) || isShortArgument(value)) {
      return [sisi];
    }

    return [sisi, value];
  });

  const argumentWithValues = argus.map((argu) => {
    const firstSingleWithArgus = map(head, singleWithArgus) as Array<string>;

    const foundSingle = find(
      (popo) => equals(argu, head(popo)),
      singleWithArgus,
    );

    if (!isNil(foundSingle)) {
      return foundSingle;
    }

    return [argu];
  });

  const argumentWithValuesMinusDoublons = argumentWithValues.filter(
    ([argument, value], index) => {
      const previous = argumentWithValues[index - 1];

      if (!isNil(previous) && equals(previous[1], argument)) {
        return false;
      }

      return true;
    },
  );

  return (
    <div>
      <Typography className={classes.command}>{command}</Typography>
      {argumentWithValuesMinusDoublons.map(([argument, value]) => {
        return (
          <div key={argument}>
            <Typography>{argument}</Typography>
            {!isNil(value) && <Typography>{value}</Typography>}
          </div>
        );
      })}
    </div>
  );
};

export default CommandCard;
